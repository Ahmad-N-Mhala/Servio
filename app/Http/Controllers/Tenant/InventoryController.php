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
        \Illuminate\Support\Facades\Gate::authorize('view_inventory');

        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }

        if (!$restaurant) {
            return Inertia::render('Inventory/Index', ['ingredients' => [], 'filters' => []]);
        }

        $ingredients = Ingredient::where('restaurant_id', $restaurant->id)
            ->when($request->search, function ($query, $search) {
                $query->where('name.en', 'like', "%{$search}%")
                    ->orWhere('name.ar', 'like', "%{$search}%");
            })
            ->orderBy('name.en', 'asc')
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

        $ingredient = Ingredient::create($validated);

        // Create initial ingredient batch for FIFO tracking
        \App\Models\IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'batch_number' => 'Batch 1',
            'quantity_received' => $validated['current_stock'],
            'quantity_remaining' => $validated['current_stock'],
            'cost_per_unit' => $validated['cost'],
            'received_at' => now(),
        ]);

        // Log the creation of new ingredient with initial stock
        \App\Models\InventoryLog::create([
            'restaurant_id' => $restaurant->id,
            'ingredient_id' => $ingredient->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'quantity_change' => $validated['current_stock'],
            'new_stock_level' => $validated['current_stock'],
            'notes' => "New ingredient created with initial stock: {$validated['current_stock']} {$validated['unit']} @ {$validated['cost']}",
        ]);

        return redirect()->back()->with('message', 'Ingredient added successfully.');
    }

    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        // Permission check
        \Illuminate\Support\Facades\Gate::authorize('add_stock');

        $restaurant = $request->user()->currentRestaurant();
        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('id')->first();
        }

        // Authorization Logic
        if ($request->user()->is_super_admin) {
            // Allowed
        } elseif ($restaurant && $ingredient->restaurant_id === $restaurant->id) {
            // Allowed
        } else {
            $hasAccess = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('email', $request->user()->email)
                ->where('restaurant_id', $ingredient->restaurant_id)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'This item does not belong to the active restaurant.');
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes',
            'unit' => 'sometimes',
            'current_stock' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'add_stock' => 'nullable|numeric|min:0',
            'added_cost' => 'nullable|numeric|min:0', // New field for the price of the *new* stock
        ]);

        if (is_string($request->name)) {
            $validated['name'] = ['en' => $request->name, 'ar' => $request->name];
        }

        if (isset($validated['add_stock']) && $validated['add_stock'] > 0) {
            $addedQty = $validated['add_stock'];
            $currentCost = $ingredient->cost;

            // Determine cost of the NEW stock (Batch Cost)
            $incomingCost = $validated['added_cost'] ?? ($request->has('cost') ? $request->cost : $currentCost);

            // Generate Batch Number
            $existingBatchesCount = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)->count();
            $nextBatchNumber = 'Batch ' . ($existingBatchesCount + 1);

            // Create a new Batch
            \App\Models\IngredientBatch::create([
                'ingredient_id' => $ingredient->id,
                'batch_number' => $nextBatchNumber,
                'quantity_initial' => $addedQty,
                'quantity_remaining' => $addedQty,
                'cost_per_unit' => $incomingCost,
                'expiration_date' => null,
            ]);

            // Update Total Stock (Sum of all batches essentially, but we store it for performance)
            // Update Total Stock manually to ensure type safety (MongoDB strict increment on strings fails)
            $ingredient->current_stock = (float) $ingredient->current_stock + (float) $addedQty;

            // Update ingredient cost to reflect FIFO (First In, First Out)
            // The cost should always be the cost of the oldest batch with remaining stock
            $oldestBatchWithStock = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($oldestBatchWithStock) {
                $ingredient->cost = $oldestBatchWithStock->cost_per_unit;
            }

            $ingredient->save();

            // Log the action
            \App\Models\InventoryLog::create([
                'restaurant_id' => $ingredient->restaurant_id,
                'ingredient_id' => $ingredient->id,
                'user_id' => $request->user()->id,
                'action' => 'added',
                'quantity_change' => $addedQty,
                'new_stock_level' => $ingredient->fresh()->current_stock,
                'notes' => "Added {$addedQty} {$ingredient->unit} @ {$incomingCost} [{$nextBatchNumber}]",
            ]);

            // Unset fields 
            unset($validated['add_stock']);
            unset($validated['added_cost']);
            unset($validated['cost']);
            unset($validated['current_stock']);
        }

        // Standard update for other fields (name, reorder_level, etc.)
        // If 'cost' is sent WITHOUT 'add_stock', it allows manual price correction.
        $ingredient->update($validated);

        return redirect()->back()->with('message', 'Ingredient updated successfully.');
    }

    public function history(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);
        // Authorization similar to other methods...
        // Assuming whoever can view/update can view history.

        $logs = \App\Models\InventoryLog::where('ingredient_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
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
