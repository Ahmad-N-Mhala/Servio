<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = RestaurantSubscription::with(['restaurant', 'plan']);

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->whereHas('restaurant', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // Sort by starts_at desc by default
        $query->orderBy('starts_at', 'desc');

        // Get all available plans for the dropdown (in case filters/modals need it)
        $plans = Plan::where('is_active', true)
            ->orderBy('order')
            ->orderBy('price_monthly')
            ->get();

        return inertia('Admin/Subscriptions/Index', [
            'subscriptions' => $query->paginate(20)->withQueryString(),
            'plans' => $plans,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();
        $plans = Plan::where('is_active', true)
            ->orderBy('order')
            ->orderBy('price_monthly')
            ->get();

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
            'ends_at' => 'nullable|date',
            'status' => 'required|in:active,cancelled,expired,trial',
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        RestaurantSubscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully');
    }

    public function edit(RestaurantSubscription $subscription)
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();
        $plans = Plan::where('is_active', true)
            ->orderBy('order')
            ->orderBy('price_monthly')
            ->get();

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
            'ends_at' => 'nullable|date', // Removed after:starts_at constraint to allow flexibility
            'status' => 'required|in:active,cancelled,expired,trial',
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        // To preserve history (logs), we create a new record instead of overwriting the old one.
        // The Restaurant model's subscription() relation uses latest(), so the new one becomes active.
        RestaurantSubscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated (new history record created).');
    }

    public function destroy(RestaurantSubscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription deleted successfully');
    }
}
