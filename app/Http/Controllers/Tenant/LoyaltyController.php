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

    public function index(): Response
    {
        $restaurant = \App\Models\Restaurant::first();

        $customers = Customer::where('restaurant_id', $restaurant->id)
            ->with('loyaltyPoints')
            ->orderBy('total_spent', 'desc')
            ->paginate(10);

        $rewards = Reward::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->orderByRaw("name->>'en' ASC")
            ->get(['id', 'name']);

        return Inertia::render('Loyalty/Index', [
            'customers' => $customers,
            'rewards' => $rewards,
            'menuItems' => $menuItems,
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
            'reward_type' => ['required', 'in:discount_percentage,discount_fixed,free_item,cashback'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $restaurant = \App\Models\Restaurant::first();

        Reward::create(array_merge($validated, [
            'restaurant_id' => $restaurant->id,
            'redemptions_count' => 0,
            'sort_order' => 0,
        ]));

        return redirect()->back()->with('message', __('loyalty.reward_created'));
    }

    public function updateReward(Request $request, Reward $reward)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:1'],
            'reward_type' => ['required', 'in:discount_percentage,discount_fixed,free_item,cashback'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $reward->update($validated);

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

