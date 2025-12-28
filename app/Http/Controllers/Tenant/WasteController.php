<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Models\Ingredient;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Fetch logs with ingredient details, including deleted ones
        $query = WasteLog::withTrashed()
            ->where('restaurant_id', $restaurant->id)
            ->with(['ingredient']);

        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->date);
        }

        $logs = $query->orderBy('log_date', 'desc')->paginate(20);

        // Fetch Waste Activity Logs (Inventory Logs related to waste)
        $wasteActivityLogs = InventoryLog::where('restaurant_id', $restaurant->id)
            ->whereIn('action', ['waste', 'waste_update', 'waste_deleted', 'waste_restored'])
            ->with(['user', 'ingredient'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Fetch ingredients for the dropdown - Show ALL ingredients (active and inactive)
        $ingredients = \App\Models\Ingredient::where('restaurant_id', $restaurant->id)
            ->with([
                'batches' => function ($query) {
                    $query->where('quantity_remaining', '>', 0)
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->get();

        // Transform ingredients to ensure proper data structure for frontend
        $ingredientsData = $ingredients->map(function ($ingredient) {
            return [
                'id' => (string) $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => $ingredient->current_stock,
                'is_active' => $ingredient->is_active,
                'batches' => $ingredient->batches->map(function ($batch) {
                    return [
                        'id' => (string) $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity_remaining' => $batch->quantity_remaining,
                        'cost_per_unit' => $batch->cost_per_unit,
                    ];
                })->toArray()
            ];
        })->toArray();

        return Inertia::render('Waste/Index', [
            'logs' => $logs,
            'wasteActivityLogs' => $wasteActivityLogs,
            'ingredients' => $ingredientsData,
            'filters' => $request->only(['date'])
        ]);
    }

    // ... (store and update methods remain same) ...

    public function restore($id)
    {
        $wasteLog = WasteLog::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($wasteLog) {
            $wasteLog->restore();

            // Deduct Stock again (re-applying waste)
            if ($wasteLog->ingredient_id && $wasteLog->waste_amount > 0) {
                $ingredient = \App\Models\Ingredient::lockForUpdate()->find($wasteLog->ingredient_id);

                if ($ingredient) {
                    $ingredient->decrement('current_stock', $wasteLog->waste_amount);

                    // Log Inventory Change
                    InventoryLog::create([
                        'restaurant_id' => $wasteLog->restaurant_id,
                        'ingredient_id' => $ingredient->id,
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'waste_restored',
                        'quantity_change' => -$wasteLog->waste_amount,
                        'new_stock_level' => $ingredient->fresh()->current_stock,
                        'notes' => 'Waste log restored. Stock deducted.',
                    ]);
                }
            }
        });

        return redirect()->back()->with('message', 'Waste record restored.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'ingredient_batch_id' => 'required|exists:ingredient_batches,id',
            'waste_amount' => 'required|numeric|min:0.0001',
            'log_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Note: MongoDB transactions require replica sets
        // Executing without transaction wrapper for standalone MongoDB instances
        try {
            $ingredient = \App\Models\Ingredient::findOrFail($validated['ingredient_id']);
            $batch = \App\Models\IngredientBatch::findOrFail($validated['ingredient_batch_id']);

            // Validate batch belongs to ingredient
            if ($batch->ingredient_id !== $ingredient->id) {
                throw new \Exception('Batch does not belong to selected ingredient.');
            }

            // Check stock in batch
            if ($batch->quantity_remaining < $validated['waste_amount']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'waste_amount' => "Insufficient stock in selected batch. Available: {$batch->quantity_remaining}"
                ]);
            }

            // Capture snapshots using Batch Cost
            $cost = $batch->cost_per_unit;
            $stockBefore = $ingredient->current_stock; // Global stock before
            $loss = $validated['waste_amount'] * $cost;
            $stockAfter = $stockBefore - $validated['waste_amount'];

            WasteLog::create([
                'restaurant_id' => $restaurant->id,
                'ingredient_id' => $validated['ingredient_id'],
                'ingredient_batch_id' => $batch->id,
                'user_id' => Auth::id() ?? 1,
                'menu_item_id' => null,
                'log_date' => $validated['log_date'],
                'added_amount' => 0,
                'waste_amount' => $validated['waste_amount'],
                'cost_per_unit' => $cost,
                'total_loss' => $loss,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Deduct from Batch and Ingredient
            $batch->decrement('quantity_remaining', $validated['waste_amount']);
            $ingredient->decrement('current_stock', $validated['waste_amount']);

            // Update ingredient cost to reflect FIFO
            $ingredient->updateCostFromFIFO();

            // Log Inventory Change
            InventoryLog::create([
                'restaurant_id' => $restaurant->id,
                'ingredient_id' => $ingredient->id,
                'user_id' => Auth::id() ?? 1,
                'action' => 'waste',
                'quantity_change' => -$validated['waste_amount'],
                'new_stock_level' => $ingredient->fresh()->current_stock,
                'notes' => "Waste logged from Batch #{$batch->batch_number}: " . ($validated['notes'] ?? ''),
            ]);
        } catch (\Exception $e) {
            \Log::error('Waste log creation failed', [
                'error' => $e->getMessage(),
                'ingredient_id' => $validated['ingredient_id'] ?? null,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Failed to create waste log: ' . $e->getMessage()
            ])->withInput();
        }

        return redirect()->back()->with('message', 'Waste log created and stock updated.');
    }

    public function update(Request $request, WasteLog $wasteLog)
    {
        $validated = $request->validate([
            'waste_amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $wasteLog) {
            $diff = $validated['waste_amount'] - $wasteLog->waste_amount;

            // Update Waste Log
            $loss = $validated['waste_amount'] * $wasteLog->cost_per_unit;
            $wasteLog->update([
                'waste_amount' => $validated['waste_amount'],
                'total_loss' => $loss
            ]);

            // Update Stock if linked to ingredient
            if ($wasteLog->ingredient_id && $diff != 0) {
                $ingredient = \App\Models\Ingredient::lockForUpdate()->find($wasteLog->ingredient_id);
                if ($ingredient) {
                    // Decrement by difference. If diff is positive (more waste), stock goes down.
                    $ingredient->decrement('current_stock', $diff);

                    // Log Inventory Change
                    InventoryLog::create([
                        'restaurant_id' => $wasteLog->restaurant_id,
                        'ingredient_id' => $ingredient->id,
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'waste_update',
                        'quantity_change' => -$diff,
                        'new_stock_level' => $ingredient->fresh()->current_stock,
                        'notes' => 'Waste adjusted by ' . $diff,
                    ]);
                }
            }
        });

        return redirect()->back()->with('message', 'Waste updated and inventory adjusted.');
    }

    public function destroy(WasteLog $wasteLog)
    {
        DB::transaction(function () use ($wasteLog) {
            // Restore Stock if linked
            if ($wasteLog->ingredient_id && $wasteLog->waste_amount > 0) {
                $ingredient = \App\Models\Ingredient::lockForUpdate()->find($wasteLog->ingredient_id);

                if ($ingredient) {
                    $ingredient->increment('current_stock', $wasteLog->waste_amount);

                    // Log Inventory Change
                    InventoryLog::create([
                        'restaurant_id' => $wasteLog->restaurant_id,
                        'ingredient_id' => $ingredient->id,
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'waste_deleted', // Custom action for reversal
                        'quantity_change' => $wasteLog->waste_amount,
                        'new_stock_level' => $ingredient->fresh()->current_stock,
                        'notes' => 'Waste log deleted. Stock restored.',
                    ]);
                }
            }

            // Soft Delete the log
            $wasteLog->delete();
        });

        return redirect()->back()->with('message', 'Waste record deleted and stock restored.');
    }
}
