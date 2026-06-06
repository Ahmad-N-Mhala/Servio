<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reward;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    public function index(Request $request): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('view_loyalty');

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // Customers Query
        $customersQuery = Customer::where('restaurant_id', $restaurant->id)
            ->with('loyaltyPoints');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $customersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->input('sort_field', 'total_spent');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['name', 'phone', 'total_spent', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $customersQuery->orderBy($sortField, $sortDirection);
        } elseif ($sortField === 'points_balance') {
            // Sort by related loyalty points balance is not supported via aggregation in simple Eloquent Mongo
            // Fallback to total_spent
            $customersQuery->orderBy('total_spent', 'desc');
        } else {
            $customersQuery->orderBy('total_spent', 'desc');
        }

        $customers = $customersQuery->paginate(10)
            ->withQueryString();

        $rewards = Reward::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with(['menuItems'])
            ->orderBy('sort_order')
            ->get();

        $menuCategories = \App\Models\MenuCategory::where('restaurant_id', $restaurant->id)
            ->with([
                'items' => function ($q) {
                    $q->select(['id', 'name', 'price', 'menu_category_id']);
                },
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $cat->setRelation('items', $cat->items->sortBy(function ($item) {
                    return is_array($item->name) ? ($item->name['en'] ?? $item->name['ar'] ?? '') : $item->name;
                })->values());

                return $cat;
            });

        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', $restaurant->id)->first();

        $stats = [
            'total_members' => (int) Customer::where('restaurant_id', $restaurant->id)->count(),
            'active_rewards' => (int) $rewards->count(),
            'total_redemptions' => (int) \App\Models\RewardRedemption::whereHas('customer', function ($q) use ($restaurant) {
                $q->where('restaurant_id', $restaurant->id);
            })->count(),
        ];

        return Inertia::render('Loyalty/Index', [
            'stats' => $stats,
            'customers' => $customers,
            'rewards' => $rewards,
            'menuCategories' => $menuCategories,
            'settings' => $restaurant->settings ?? [],
            'earningMethod' => $earningMethod,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    // ... showCustomer, storeReward, updateReward, deleteReward, adjustPoints methods unchanged ...

    public function showCustomer(Customer $customer): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('view_loyalty');

        if ((string) $customer->restaurant_id !== (string) session('active_restaurant_id')) {
            abort(403, 'This customer belongs to another restaurant.');
        }
        $customer->load([
            'loyaltyPoints',
            'pointTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(50);
            },
            'rewardRedemptions.reward',
            'orders' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
        ]);

        $rewards = \App\Models\Reward::where('restaurant_id', $customer->restaurant_id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('Loyalty/Customer', [
            'customer' => $customer,
            'rewards' => $rewards,
        ]);
    }

    public function storeReward(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards');
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:1'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'reward_type' => ['required', 'in:discount_percentage,discount_fixed,free_item,cashback'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'menu_item_ids' => ['nullable', 'array'],
            'menu_item_ids.*' => ['exists:menu_items,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        $data = $validated;
        unset($data['menu_item_ids']);
        $data['min_order_value'] = $data['min_order_value'] ?? 0;

        $reward = Reward::create(array_merge($data, [
            'restaurant_id' => $restaurant->id,
            'redemptions_count' => 0,
            'sort_order' => 0,
        ]));

        if (isset($validated['menu_item_ids'])) {
            $reward->menuItems()->sync($validated['menu_item_ids']);
        }

        return redirect()->back()->with('message', __('loyalty.reward_created'));
    }

    public function updateReward(Request $request, Reward $reward)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards');
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:1'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'reward_type' => ['required', 'in:discount_percentage,discount_fixed,free_item,cashback'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'menu_item_ids' => ['nullable', 'array'],
            'menu_item_ids.*' => ['exists:menu_items,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant || (string) $reward->restaurant_id !== (string) $restaurant->id) {
            abort(403, 'Unauthorized');
        }

        $data = $validated;
        unset($data['menu_item_ids']);
        $data['min_order_value'] = $data['min_order_value'] ?? 0;

        $reward->update($data);

        if (isset($validated['menu_item_ids'])) {
            $reward->menuItems()->sync($validated['menu_item_ids']);
        }

        return redirect()->back()->with('message', __('loyalty.reward_updated'));
    }

    public function deleteReward(Reward $reward)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards');
        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant || (string) $reward->restaurant_id !== (string) $restaurant->id) {
            abort(403, 'Unauthorized');
        }
        $reward->delete();

        return redirect()->back()->with('message', __('loyalty.reward_deleted'));
    }

    public function adjustPoints(Request $request, Customer $customer)
    {
        \Illuminate\Support\Facades\Gate::authorize('adjust_points');
        $validated = $request->validate([
            'points' => ['required', 'integer'],
            'description' => ['required', 'string'],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant || (string) $customer->restaurant_id !== (string) $restaurant->id) {
            abort(403, 'Unauthorized');
        }

        $loyaltyPoints = $customer->loyaltyPoints()->firstOrCreate([
            'customer_id' => $customer->id,
        ], [
            'balance' => 0,
            'total_earned' => 0,
            'total_redeemed' => 0,
        ]);

        if ($validated['points'] > 0) {
            $loyaltyPoints->addPoints($validated['points'], $validated['description']);
        } else {
            $loyaltyPoints->redeemPoints(abs($validated['points']), $validated['description']);
        }

        return redirect()->back()->with('message', __('loyalty.points_adjusted'));
    }

    public function settings(Request $request): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards');

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', $restaurant->id)->first();

        return Inertia::render('Loyalty/Settings', [
            'settings' => $restaurant->settings ?? [],
            'earningMethod' => $earningMethod,
        ]);
    }

    public function updateSettings(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards'); // Re-use permission for now

        $validated = $request->validate([
            'loyalty_program_name' => ['nullable', 'string', 'max:50'],
            'loyalty_card_title' => ['nullable', 'string', 'max:50'],
            'loyalty_card_description' => ['nullable', 'string', 'max:100'],
            'loyalty_theme_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'loyalty_text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'loyalty_terms' => ['nullable', 'string', 'max:500'],
            'loyalty_qr_link' => ['nullable', 'url', 'max:255'],
            // Basic file validation, in real app would handle storage proper
            'loyalty_logo' => ['nullable', 'image', 'max:2048'],
            'loyalty_banner' => ['nullable', 'image', 'max:5120'],

            // Earning Method Fields
            'earning_method_type' => ['nullable', 'string', 'in:order_total,visit'],
            'earning_points' => ['nullable', 'numeric', 'min:1'],
            'earning_currency_amount' => ['nullable', 'numeric', 'min:0.01'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        $currentSettings = $restaurant->settings ?? [];

        // Handle File Uploads (naive implementation for local serve)
        if ($request->hasFile('loyalty_logo')) {
            $path = $request->file('loyalty_logo')->store('loyalty/logos', 'public');
            $currentSettings['loyalty_logo'] = '/storage/'.$path;
        }

        if ($request->hasFile('loyalty_banner')) {
            $path = $request->file('loyalty_banner')->store('loyalty/banners', 'public');
            $currentSettings['loyalty_banner'] = '/storage/'.$path;
        }

        // Update other fields
        $fields = ['loyalty_program_name', 'loyalty_card_title', 'loyalty_card_description', 'loyalty_theme_color', 'loyalty_text_color', 'loyalty_terms', 'loyalty_qr_link'];
        foreach ($fields as $field) {
            if ($request->has($field)) { // Only update if present in request
                $currentSettings[$field] = $validated[$field] ?? null;
            }
        }

        $restaurant->settings = $currentSettings;
        $restaurant->save();

        // Update Earning Method
        if ($request->has('earning_method_type')) {
            \App\Models\EarningMethod::updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'name' => $request->earning_method_type === 'order_total' ? 'Points per Spend' : 'Points per Visit',
                    'type' => $request->earning_method_type,
                    'points' => $request->earning_points ?? 1,
                    'is_active' => true,
                    // If order_total, use currency_amount, else null
                    'currency_amount' => $request->earning_method_type === 'order_total' ? ($request->earning_currency_amount ?? 1) : null,
                    'min_spent' => $request->min_spent ?? 0,
                ]
            );
        }

        return redirect()->back()->with('success', 'Loyalty card updated successfully.');
    }

    public function updateRewardDesign(Request $request, Reward $reward)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage_rewards');

        $validated = $request->validate([
            'loyalty_program_name' => ['nullable', 'string', 'max:50'], // Mapped to Title
            'loyalty_card_title' => ['nullable', 'string', 'max:50'], // Mapped to Subtitle
            'loyalty_card_description' => ['nullable', 'string', 'max:100'],
            'loyalty_theme_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'loyalty_text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'loyalty_terms' => ['nullable', 'string', 'max:500'],
            'loyalty_qr_link' => ['nullable', 'url', 'max:255'],
            'loyalty_logo' => ['nullable', 'image', 'max:2048'],
            'loyalty_banner' => ['nullable', 'image', 'max:5120'],
        ]);

        $currentDesign = $reward->design ?? [];

        // Handle File Uploads
        if ($request->hasFile('loyalty_logo')) {
            $path = $request->file('loyalty_logo')->store('loyalty/rewards/logos', 'public');
            $currentDesign['loyalty_logo'] = '/storage/'.$path;
        }

        if ($request->hasFile('loyalty_banner')) {
            $path = $request->file('loyalty_banner')->store('loyalty/rewards/banners', 'public');
            $currentDesign['loyalty_banner'] = '/storage/'.$path;
        }

        // Update generic fields
        $fields = ['loyalty_program_name', 'loyalty_card_title', 'loyalty_card_description', 'loyalty_theme_color', 'loyalty_text_color', 'loyalty_terms', 'loyalty_qr_link'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $currentDesign[$field] = $validated[$field] ?? null;
            }
        }

        $reward->design = $currentDesign;
        $reward->save();

        return redirect()->back()->with('success', 'Reward design updated successfully.');
    }

    public function requestRedemptionOtp(Request $request, Customer $customer)
    {
        $logContext = [
            'customer_id' => (string) $customer->id,
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ];

        \Illuminate\Support\Facades\Log::info('LoyaltyController: Incoming OTP request', $logContext);

        try {
            \Illuminate\Support\Facades\Gate::authorize('view_loyalty');

            // --- IMPROVED AUTH CHECK ---
            $user = $request->user();
            $restaurantId = (string) ($user->currentRestaurant()?->id ?? session('active_restaurant_id') ?? '');

            // Rule: Super Admin can bypass. Non-super admins MUST match the customer's restaurant.
            if (! $user->is_super_admin && (string) $customer->restaurant_id !== $restaurantId) {
                \Illuminate\Support\Facades\Log::warning('LoyaltyController: Authorization Failed', array_merge($logContext, [
                    'customer_restaurant' => (string) $customer->restaurant_id,
                    'user_context_restaurant' => $restaurantId,
                ]));

                return response()->json(['message' => __('loyalty.unauthorized_customer')], 403);
            }

            if (! $restaurantId && ! $user->is_super_admin) {
                return response()->json(['message' => __('loyalty.restaurant_context_missing')], 400);
            }

            \Illuminate\Support\Facades\Log::info('LoyaltyController: Authorization Passed. Handing off to LoyaltyService.');

            $sent = $this->loyaltyService->sendRedemptionOtp($customer);

            if ($sent) {
                \Illuminate\Support\Facades\Log::info('LoyaltyController: Success. OTP process completed.');

                return response()->json(['message' => __('loyalty.otp_send_success')]);
            }

            \Illuminate\Support\Facades\Log::error('LoyaltyController: Service indicated failure (SmsService issue).');

            return response()->json(['message' => __('loyalty.otp_send_failed')], 503);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LoyaltyController Failure: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('LoyaltyController CRITICAL FAILURE: '.$e->getMessage(), array_merge($logContext, [
                'trace' => $e->getTraceAsString(),
            ]));

            return response()->json(['message' => __('common.server_error')], 500);
        }
    }

    public function verifyAndRedeem(Request $request, Customer $customer)
    {
        \Illuminate\Support\Facades\Gate::authorize('view_loyalty');

        if (! $request->user()->is_super_admin && (string) $customer->restaurant_id !== (string) session('active_restaurant_id')) {
            return response()->json(['message' => __('loyalty.unauthorized_customer')], 403);
        }

        $validated = $request->validate([
            'reward_id' => 'required|exists:rewards,id',
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->loyaltyService->verifyOtp($customer, $validated['otp'])) {
            return response()->json(['message' => __('loyalty.invalid_otp')], 422);
        }

        try {
            $redemption = $this->loyaltyService->redeemReward($customer, (string) $validated['reward_id']);

            return response()->json([
                'message' => __('loyalty.reward_redeemed'),
                'redemption' => $redemption,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function verifyOtpOnly(Request $request, Customer $customer)
    {
        \Illuminate\Support\Facades\Gate::authorize('view_loyalty');

        if (! $request->user()->is_super_admin && (string) $customer->restaurant_id !== (string) session('active_restaurant_id')) {
            return response()->json(['message' => __('loyalty.unauthorized_customer')], 403);
        }

        $validated = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if ($this->loyaltyService->verifyOtp($customer, $validated['otp'], false)) {
            return response()->json(['message' => __('loyalty.otp_verified')]);
        }

        return response()->json(['message' => __('loyalty.invalid_otp')], 422);
    }

    public function smsLogs(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view_sms_logs');

        $restaurant = auth()->user()->currentRestaurant();

        $logs = \App\Models\CommunicationLog::where('restaurant_id', (string) $restaurant->id)
            ->where('type', 'sms')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Loyalty/SmsLogs', [
            'logs' => $logs,
        ]);
    }
}
