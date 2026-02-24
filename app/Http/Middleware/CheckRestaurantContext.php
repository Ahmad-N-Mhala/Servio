<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

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
            //TODO:
            // FIX: Use manual query
            $pivotIds = \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('email', $request->user()->email)
                ->pluck('restaurant_id')
                ->toArray();

            \Illuminate\Support\Facades\Log::info('CheckRestaurantContext: User ' . $request->user()->email . ' has pivot IDs: ' . json_encode($pivotIds));

            $firstRestaurant = !empty($pivotIds)
                ? \App\Models\Restaurant::whereIn('id', $pivotIds)->first()
                : null;

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
        $cacheKey = 'user_restaurants_' . $request->user()->id;
        $pivotIds = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($request) {
            return \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('email', $request->user()->email)
                ->pluck('restaurant_id')
                ->toArray();
        });

        $hasAccess = in_array($restaurantId, $pivotIds);

        if (!$hasAccess) {
            // User lost access to current restaurant, try to switch to another
            session()->forget('active_restaurant_id');

            // FIX: Use manual query (reusing pivotIds from above)
            $anotherRestaurant = !empty($pivotIds)
                ? \App\Models\Restaurant::whereIn('id', $pivotIds)->first()
                : null;

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

        // Validated Access - Now Check Subscription Status
        $isExpired = \Illuminate\Support\Facades\Cache::remember("restaurant_{$restaurantId}_is_expired", 60, function () use ($restaurantId) {
            $restaurant = \App\Models\Restaurant::find($restaurantId);
            if (!$restaurant)
                return true;

            $subscription = $restaurant->subscription; // HasOne relationship

            // Check if subscription exists and is valid
            // You can adjust these rules (e.g., allow grace period)
            if (!$subscription)
                return true;
            if ($subscription->status !== 'active')
                return true;
            if ($subscription->ends_at && $subscription->ends_at->isPast())
                return true;

            return false;
        });

        if ($isExpired) {
            // Return Inertia response directly (interrupting the request)
            return Inertia::render('Error/SubscriptionExpired')
                ->toResponse($request)
                ->setStatusCode(403);
        }

        return $next($request);
    }
}
