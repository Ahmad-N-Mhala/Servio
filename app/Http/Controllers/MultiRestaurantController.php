<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MultiRestaurantController extends Controller
{

    /**
     * Display a listing of the user's restaurants.
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch restaurants where the user's email is associated
        // We use the central 'restaurants' table now
        $restaurants = Restaurant::with(['subscription.plan'])
            ->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('restaurant_user')
                    ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                    ->where('restaurant_user.email', $user->email);
            })
            ->get()
            ->map(function ($restaurant) use ($user) {
                // Fetch pivot data manually
                $pivot = \DB::table('restaurant_user')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('email', $user->email)
                    ->first();

                // Fetch active plan from the subscription relationship
                $planName = $restaurant->subscription && $restaurant->subscription->plan
                    ? $restaurant->subscription->plan->name
                    : 'Free'; // Default to Free if no subscription
    
                // Domain logic is deprecated in single-DB, so we can return null or a logical ID
                $domain = request()->getHost(); // Just current host since we are single domain now
    
                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'slug' => $restaurant->slug,
                    'logo' => null, // Add logo column later
                    'role' => $pivot ? $pivot->role : 'staff',
                    'is_active' => $pivot ? (bool) $pivot->is_active : false,
                    'plan' => $planName,
                    'domain' => $domain,
                ];
            });

        // Get the user's current plan (from first restaurant they own)
        $currentPlan = null;
        $maxRestaurants = 1; // Default limit

        $firstRestaurant = Restaurant::with(['subscription.plan'])
            ->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('restaurant_user')
                    ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                    ->where('restaurant_user.email', $user->email)
                    ->where('restaurant_user.role', 'owner');
            })
            ->first();

        if ($firstRestaurant && $firstRestaurant->subscription && $firstRestaurant->subscription->plan) {
            $currentPlan = $firstRestaurant->subscription->plan;
            $maxRestaurants = $currentPlan->max_restaurants ?? 1;
        }

        // Check if user can add more restaurants
        $canAddRestaurant = $restaurants->count() < $maxRestaurants;

        return Inertia::render('MultiRestaurant/Index', [
            'restaurants' => $restaurants,
            'canAddRestaurant' => $canAddRestaurant,
            'currentPlan' => $currentPlan ? [
                'name' => $currentPlan->name,
                'max_restaurants' => $maxRestaurants,
            ] : null,
        ]);
    }

    /**
     * Switch context to a specific restaurant.
     * In a Stancl/Tenancy setup, this usually means redirecting to that tenant's domain.
     */
    public function switch(Request $request, Restaurant $restaurant)
    {
        // Verify user has access to this restaurant by email in the pivot table (central DB)
        $hasAccess = \DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->where('email', $request->user()->email)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to this restaurant.');
        }

        // Set the active restaurant in the session
        session(['active_restaurant_id' => $restaurant->id]);

        return redirect()->route('dashboard');
    }
}
