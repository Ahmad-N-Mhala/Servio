<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Restaurant::with(['owner', 'subscription.plan'])->where('status', '!=', 'deleted');

        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        return inertia('Admin/Restaurants/Index', [
            'restaurants' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Restaurants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'earning_method_type' => 'nullable|string|in:order_total,visit',
            'earning_points' => 'nullable|numeric|min:1',
        ]);

        $restaurant = \App\Models\Restaurant::create($validated);

        // Create Default Loyalty Setting
        \App\Models\EarningMethod::create([
            'restaurant_id' => $restaurant->id,
            'name' => $request->earning_method_type === 'order_total' ? 'Points per Spend' : 'Points per Visit',
            'type' => $request->earning_method_type ?? 'order_total',
            'points' => $request->earning_points ?? 1,
            'is_active' => true,
            'currency_amount' => ($request->earning_method_type ?? 'order_total') === 'order_total' ? 1 : null,
        ]);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant created successfully.');
    }

    public function edit(\App\Models\Restaurant $restaurant)
    {
        // Load loyalty settings if stored in settings json column or separate table
        // Assuming for now they might be in the 'settings' JSON column based on OnboardingController
        // Or if you want to support them as direct columns, migration is needed.
        // Based on OnboardingController, they seem to be saved to EarningMethod model.

        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', $restaurant->id)->where('is_active', true)->first();

        $restaurantData = $restaurant->toArray();
        if ($earningMethod) {
            $restaurantData['earning_method_type'] = $earningMethod->type; // 'order_total' or 'visit'
            $restaurantData['earning_points'] = $earningMethod->points;
        }

        return inertia('Admin/Restaurants/Edit', [
            'restaurant' => $restaurantData
        ]);
    }

    public function update(Request $request, \App\Models\Restaurant $restaurant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug,' . $restaurant->id,
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'status' => 'required|string|in:active,suspended',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'earning_method_type' => 'nullable|string|in:order_total,visit',
            'earning_points' => 'nullable|numeric|min:1',
        ]);

        $restaurant->update($validated);

        // Update Loyalty Settings
        if ($request->has('earning_method_type') && $request->has('earning_points')) {
            \App\Models\EarningMethod::updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'name' => $request->earning_method_type === 'order_total' ? 'Points per Spend' : 'Points per Visit',
                    'type' => $request->earning_method_type,
                    'points' => $request->earning_points,
                    'is_active' => true,
                    // Default values for other fields if creating new
                    'currency_amount' => $request->earning_method_type === 'order_total' ? 1 : null,
                ]
            );
        }

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant updated successfully.');
    }

    public function destroy(\App\Models\Restaurant $restaurant)
    {
        // 1. Update status to deleted
        $restaurant->update(['status' => 'deleted']);

        // 2. Deactivate all users associated with this restaurant
        // utilizing the restaurant_user pivot table
        \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->update(['is_active' => false]);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant deleted and users deactivated.');
    }
}
