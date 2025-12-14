<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Plan;
use App\Models\RestaurantSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        // Get all restaurants with their current subscription
        $restaurants = Restaurant::with([
            'subscription' => function ($query) {
                $query->with('plan')->latest();
            }
        ])
            ->select(['id', 'name', 'email', 'phone', 'created_at'])
            ->paginate(20);

        // Get all available plans for the dropdown
        $plans = Plan::where('is_active', true)->get();

        return inertia('Admin/Subscriptions/Index', [
            'restaurants' => $restaurants,
            'plans' => $plans,
        ]);
    }

    public function create()
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();
        $plans = Plan::where('is_active', true)->get();

        return inertia('Admin/Subscriptions/Create', [
            'restaurants' => $restaurants,
            'plans' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'plan_id' => 'required|exists:plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'status' => 'required|in:active,cancelled,expired',
        ]);

        RestaurantSubscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully');
    }

    public function edit(RestaurantSubscription $subscription)
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();
        $plans = Plan::where('is_active', true)->get();

        return inertia('Admin/Subscriptions/Edit', [
            'subscription' => $subscription->load(['restaurant', 'plan']),
            'restaurants' => $restaurants,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, RestaurantSubscription $subscription)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'plan_id' => 'required|exists:plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'status' => 'required|in:active,cancelled,expired',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully');
    }

    public function destroy(RestaurantSubscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription deleted successfully');
    }
}
