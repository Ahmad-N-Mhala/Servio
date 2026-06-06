<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\RestaurantSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        $restaurant = auth()->user()->currentRestaurant();

        // If no restaurant is available, redirect to restaurant selection
        if (! $restaurant) {
            return Inertia::render('Plans/Index', [
                'plans' => Plan::where('is_active', true)->orderBy('order')->orderBy('price_monthly', 'asc')->get(),
                'currentSubscription' => null,
            ]);
        }

        $plans = Plan::where('is_active', true)
            ->orderBy('order')
            ->orderBy('price_monthly', 'asc')
            ->get();

        $currentSubscription = RestaurantSubscription::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        return Inertia::render('Plans/Index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    public function subscribe(Request $request, Plan $plan)
    {
        $restaurant = auth()->user()->currentRestaurant();

        // Cancel existing subscription
        RestaurantSubscription::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // Create new subscription
        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        // Sync features to restaurant settings
        $settings = is_array($restaurant->settings) ? $restaurant->settings : [];
        $settings['enabled_features'] = $plan->enabled_features ?? [];
        $restaurant->update(['settings' => $settings]);

        return redirect()->route('plans.index')
            ->with('message', "Successfully subscribed to {$plan->name} plan!");
    }
}
