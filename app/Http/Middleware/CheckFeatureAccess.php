<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $feature)
    {
        $restaurantId = session('active_restaurant_id');

        if (!$restaurantId) {
            return redirect()->route('restaurants.index')
                ->with('error', 'Please select a restaurant first.');
        }

        $restaurant = auth()->user()->currentRestaurant();

        if (!$restaurant || (string) $restaurant->id !== (string) $restaurantId) {
            return redirect()->route('restaurants.index')
                ->with('error', 'Restaurant not found.');
        }

        // Check if the restaurant has access to this feature
        if (!$restaurant->hasFeature($feature)) {
            return redirect()->route('dashboard')
                ->with('error', 'This feature is not available in your current plan. Please upgrade to access it.');
        }

        return $next($request);
    }
}
