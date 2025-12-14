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
        $restaurants = Restaurant::whereExists(function ($query) use ($user) {
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

                // Fetch active plan (assuming we store it on restaurant or still separate table)
                // Ideally we should have a relationship: $restaurant->subscription
                $planName = 'Basic'; // Placeholder until Subscription model is updated to belongsTo Restaurant
    
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

        return Inertia::render('MultiRestaurant/Index', [
            'restaurants' => $restaurants,
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
