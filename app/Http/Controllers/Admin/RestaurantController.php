<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Restaurant::with('owner')->where('status', '!=', 'deleted');

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
        ]);

        $restaurant = \App\Models\Restaurant::create($validated);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant created successfully.');
    }

    public function edit(\App\Models\Restaurant $restaurant)
    {
        return inertia('Admin/Restaurants/Edit', [
            'restaurant' => $restaurant
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
        ]);

        $restaurant->update($validated);

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
