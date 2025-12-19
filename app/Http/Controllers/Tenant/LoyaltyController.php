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
    ) {
    }

    public function index(Request $request): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

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

        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->orderBy('name.en', 'asc') // Mongo object sort syntax
            ->get(['id', 'name']);

        return Inertia::render('Loyalty/Index', [
            'customers' => $customers,
            'rewards' => $rewards,
            'menuItems' => $menuItems,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function showCustomer(Customer $customer): Response
    {
        $customer->load([
            'loyaltyPoints',
            'pointTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(50);
            },
            'rewardRedemptions.reward',
            'orders' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ]);

        return Inertia::render('Loyalty/Customer', [
            'customer' => $customer,
        ]);
    }

    public function storeReward(Request $request)
    {
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

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

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
        $reward->delete();

        return redirect()->back()->with('message', __('loyalty.reward_deleted'));
    }

    public function adjustPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => ['required', 'integer'],
            'description' => ['required', 'string'],
        ]);

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
}
