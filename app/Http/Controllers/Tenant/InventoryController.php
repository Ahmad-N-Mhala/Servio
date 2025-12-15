<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ingredient;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }

        if (!$restaurant) {
            return Inertia::render('Inventory/Index', ['ingredients' => [], 'filters' => []]);
        }

        $ingredients = Ingredient::where('restaurant_id', $restaurant->id)
            ->when($request->search, function ($query, $search) {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->orderByRaw("name->>'en' ASC")
            ->get();

        return Inertia::render('Inventory/Index', [
            'ingredients' => $ingredients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }

        if (!$restaurant) {
            return redirect()->back()->with('error', 'No active restaurant found.');
        }

        $validated = $request->validate([
            'name' => 'required',
            'unit' => 'required|string',
            'current_stock' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $validated['restaurant_id'] = $restaurant->id;

        // Ensure name is array for translation
        if (is_string($validated['name'])) {
            $validated['name'] = ['en' => $validated['name'], 'ar' => $validated['name']];
        }

        Ingredient::create($validated);

        return redirect()->back()->with('message', 'Ingredient added successfully.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $restaurant = $request->user()->currentRestaurant();
        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }
        $restaurantId = $restaurant?->id;

        $hasAccess = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $request->user()->email)
            ->where('restaurant_id', $ingredient->restaurant_id)
            ->exists();

        // If user is NOT super admin AND does NOT have explicit access
        if (!$request->user()->is_super_admin && !$hasAccess) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes',
            'unit' => 'sometimes',
            'current_stock' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'add_stock' => 'nullable|numeric|min:0',
        ]);

        if (is_string($request->name)) {
            $validated['name'] = ['en' => $request->name, 'ar' => $request->name];
        }

        if (isset($validated['add_stock'])) {
            $ingredient->increment('current_stock', $validated['add_stock']);
            // We could log this addition here if we had an InventoryLog
            unset($validated['add_stock']);
        }

        $ingredient->update($validated);

        return redirect()->back()->with('message', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $restaurant = request()->user()->currentRestaurant();
        if (!$restaurant && request()->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }
        $restaurantId = $restaurant?->id;

        $hasAccess = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', request()->user()->email)
            ->where('restaurant_id', $ingredient->restaurant_id)
            ->exists();

        if (!request()->user()->is_super_admin && !$hasAccess) {
            abort(403);
        }

        $ingredient->delete();

        return redirect()->back()->with('message', 'Ingredient deleted successfully.');
    }
}
