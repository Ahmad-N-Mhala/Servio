<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRestaurantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for super admins - they use admin routes
        if ($request->user() && $request->user()->is_super_admin) {
            return $next($request);
        }

        // If route is excluded from context check (e.g., selection page), skip
        if ($request->routeIs('restaurants.*') || $request->routeIs('logout') || $request->routeIs('admin.*')) {
            return $next($request);
        }

        $restaurantId = session('active_restaurant_id');

            // If no restaurant in session, auto-select the first one
            if (!$restaurantId) {
                $firstRestaurant = $request->user()->restaurants()->first();

                if (!$firstRestaurant) {
                    // User has no restaurants - redirect to login with error
                    auth()->logout();
                    return redirect()->route('login')->with('error', 'No restaurant access found for this account.');
                }

            // Set the first restaurant as active and continue
            session(['active_restaurant_id' => $firstRestaurant->id]);
            // No need to validate since we just selected from user's own restaurants
            return $next($request);
        }

        // If restaurant is already in session, validate user still has access
        // Direct query to pivot collection works fine in Mongo for simple where
        // If restaurant is already in session, validate user still has access
        $hasAccess = $request->user()->restaurants()->where('id', $restaurantId)->exists();

        if (!$hasAccess) {
            // User lost access to current restaurant, try to switch to another
            session()->forget('active_restaurant_id');

            $anotherRestaurant = $request->user()->restaurants()->first();

            if ($anotherRestaurant) {
                session(['active_restaurant_id' => $anotherRestaurant->id]);
                return redirect()->back()->with('message', 'Switched to available restaurant.');
            }

            // No restaurants available - log out
            auth()->logout();
            return redirect()->route('login')->with('error', 'No restaurant access found for this account.');
        }

        // Share restaurant ID globally or with views if needed
        // config(['app.active_restaurant_id' => $restaurantId]); 

        return $next($request);
    }
}
