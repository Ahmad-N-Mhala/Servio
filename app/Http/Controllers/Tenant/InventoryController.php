<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ingredient;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view_inventory');

        $restaurant = $request->user()->currentRestaurant();

        // Enforce strict context - removed implicit fallback for super admin
        if (!$restaurant && $request->user()->is_super_admin) {
            // Check if there's a session ID first (fallback to manual lookup if helper fails)
            if (session('active_restaurant_id')) {
                $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
            }
        }

        if (!$restaurant) {
            return Inertia::render('Inventory/Index', ['ingredients' => [], 'filters' => []]);
        }

        $ingredients = Ingredient::where('restaurant_id', $restaurant->id)
            ->with([
                'batches' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                },
                'menuItems'
            ])
            ->when($request->search, function ($query, $search) {
                $query->where('name.en', 'like', "%{$search}%")
                    ->orWhere('name.ar', 'like', "%{$search}%");
            })
            ->orderBy('name.en', 'asc')
            ->get();

        $users = $restaurant->users()->get(['id', 'name', 'email']);

        // Identify owner for default selection if needed
        $owner = $restaurant->owner()->first();

        // Ensure owner is in the list
        if ($owner && !$users->contains('id', $owner->id)) {
            $users->push($owner);
        }

        return Inertia::render('Inventory/Index', [
            'ingredients' => $ingredients,
            'filters' => $request->only(['search']),
            'users' => $users,
            'defaultOwnerId' => $owner ? $owner->id : null,
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        // Enforce strict context - removed implicit fallback for super admin
        if (!$restaurant && $request->user()->is_super_admin && session('active_restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
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
            'expiration_date' => 'nullable|date',
            'bill' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000',
            'reminder_days' => 'nullable|integer|min:1',
            'reminder_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['restaurant_id'] = $restaurant->id;

        // Ensure name is array with both keys populated
        $nameVal = $validated['name'] ?? null;
        if ($nameVal) {
            if (is_string($nameVal)) {
                $validated['name'] = ['en' => $nameVal, 'ar' => $nameVal];
            } elseif (is_array($nameVal)) {
                $en = $nameVal['en'] ?? null;
                $ar = $nameVal['ar'] ?? null;
                // Fallback if one is empty
                if (empty($en) && !empty($ar))
                    $en = $ar;
                if (empty($ar) && !empty($en))
                    $ar = $en;

                $validated['name'] = ['en' => $en, 'ar' => $ar];
            }
        }

        // Ensure reorder_level is not null for decimal casting
        $validated['reorder_level'] = $validated['reorder_level'] ?? 0;

        // Check for duplicate names (EN and AR)
        $duplicate = Ingredient::where('restaurant_id', $restaurant->id)
            ->where(function ($query) use ($validated) {
                // Since spatie/laravel-translatable stores as JSON string in MongoDB, we use 'like'
                $query->where('name', 'like', '%"en":"' . $validated['name']['en'] . '"%')
                    ->orWhere('name', 'like', '%"ar":"' . $validated['name']['ar'] . '"%');
            })->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => [__('inventory.duplicate_name')]
            ]);
        }

        $ingredient = Ingredient::create($validated);

        // Create initial ingredient batch for FIFO tracking
        \App\Models\IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'batch_number' => 'Batch 1',
            'quantity_initial' => $validated['current_stock'],
            'quantity_remaining' => $validated['current_stock'],
            'cost_per_unit' => $validated['cost'],
            'received_at' => now(),
            'expiration_date' => $validated['expiration_date'] ?? null,
            'reminder_days_before' => $validated['reminder_days'] ?? null,
            'reminder_user_id' => $validated['reminder_user_id'] ?? null,
        ]);

        // Set notification user on ingredient level too
        if (isset($validated['reminder_user_id'])) {
            $ingredient->notification_user_id = $validated['reminder_user_id'];
            $ingredient->save();
        }

        // Handle Bill Upload
        $billPath = null;
        if ($request->hasFile('bill')) {
            $billPath = $request->file('bill')->store('inventory-bills', 'public');
        }

        // Log the creation of new ingredient with initial stock
        $logNotes = "New ingredient created with initial stock: {$validated['current_stock']} {$validated['unit']} @ {$validated['cost']}";
        if (!empty($validated['notes'])) {
            $logNotes .= "\nUser Notes: " . $validated['notes'];
        }

        \App\Models\InventoryLog::create([
            'restaurant_id' => $restaurant->id,
            'ingredient_id' => $ingredient->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'quantity_change' => $validated['current_stock'],
            'new_stock_level' => $validated['current_stock'],
            'notes' => $logNotes,
            'bill_path' => $billPath,
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
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Authorization Logic
        if ($request->user()->is_super_admin) {
            // Super admins have full access
        } else {
            // For regular users, check if they are associated with the restaurant that owns this ingredient
            $hasAccess = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('email', $request->user()->email)
                ->where('restaurant_id', (string) $ingredient->restaurant_id)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have permission to modify this item.');
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
            'expiration_date' => 'nullable|date',
            'bill' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000',
            'reminder_days' => 'nullable|integer|min:1',
            'reminder_user_id' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['name'])) {
            $nameVal = $validated['name'];
            if (is_string($nameVal)) {
                $validated['name'] = ['en' => $nameVal, 'ar' => $nameVal];
            } elseif (is_array($nameVal)) {
                $en = $nameVal['en'] ?? null;
                $ar = $nameVal['ar'] ?? null;
                // Fallback if one is empty
                if (empty($en) && !empty($ar))
                    $en = $ar;
                if (empty($ar) && !empty($en))
                    $ar = $en;

                $validated['name'] = ['en' => $en, 'ar' => $ar];
            }
        }

        // Ensure reorder_level is not null for decimal casting
        if (array_key_exists('reorder_level', $validated) && is_null($validated['reorder_level'])) {
            $validated['reorder_level'] = 0;
        }

        // Check for duplicate names (EN and AR) if name is provided
        if (isset($validated['name'])) {
            $duplicate = Ingredient::where('restaurant_id', $ingredient->restaurant_id)
                ->where('_id', '!=', $ingredient->id)
                ->where(function ($query) use ($validated) {
                    // Since spatie/laravel-translatable stores as JSON string in MongoDB, we use 'like'
                    $query->where('name', 'like', '%"en":"' . $validated['name']['en'] . '"%')
                        ->orWhere('name', 'like', '%"ar":"' . $validated['name']['ar'] . '"%');
                })->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'name' => [__('inventory.duplicate_name')]
                ]);
            }
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
                'expiration_date' => $validated['expiration_date'] ?? null,
                'reminder_days_before' => $validated['reminder_days'] ?? null,
                'reminder_user_id' => $validated['reminder_user_id'] ?? null,
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

            // Handle Bill Upload
            $billPath = null;
            if ($request->hasFile('bill')) {
                $billPath = $request->file('bill')->store('inventory-bills', 'public');
            }

            // Log the action
            $logNotes = "Added {$addedQty} {$ingredient->unit} @ {$incomingCost} [{$nextBatchNumber}]";
            if (!empty($validated['notes'])) {
                $logNotes .= "\nUser Notes: " . $validated['notes'];
            }

            \App\Models\InventoryLog::create([
                'restaurant_id' => $ingredient->restaurant_id,
                'ingredient_id' => $ingredient->id,
                'user_id' => $request->user()->id,
                'action' => 'added',
                'quantity_change' => $addedQty,
                'new_stock_level' => $ingredient->fresh()->current_stock,
                'notes' => $logNotes,
                'bill_path' => $billPath,
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

    public function export(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();
        if (!$restaurant && $request->user()->is_super_admin) {
            if (session('active_restaurant_id')) {
                $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
            }
        }

        if (!$restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // Determine Timezone
        $country = \App\Models\Country::where('name', $restaurant->country)->first();
        $timezone = $country && $country->timezone ? $country->timezone : config('app.timezone');

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Parse dates in the restaurant's timezone using Carbon
        $startDate = \Carbon\Carbon::parse($request->start_date, $timezone)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date, $timezone)->endOfDay();

        $query = \App\Models\InventoryLog::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['ingredient', 'user'])
            ->orderBy('created_at', 'desc');

        $logs = $query->get();

        // Current time in restaurant timezone for filename
        $now = \Carbon\Carbon::now($timezone);
        $filename = "inventory_report_" . $now->format('Y-m-d_H-i') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $filename,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            __('reports.date'),
            __('reports.ingredient'),
            __('reports.action'),
            __('reports.quantity_change'),
            __('reports.new_stock_level'),
            __('reports.user'),
            __('reports.notes')
        ];

        $callback = function () use ($logs, $columns, $timezone) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                $ingredientName = $log->ingredient ? ($log->ingredient->name['en'] ?? $log->ingredient->name['ar'] ?? (is_string($log->ingredient->name) ? $log->ingredient->name : 'Deleted Item')) : 'Deleted Item';

                // Format log date in restaurant timezone
                $logDate = $log->created_at->setTimezone($timezone)->format('Y-m-d H:i:s');

                $row = [
                    $logDate,
                    $ingredientName,
                    ucfirst(str_replace('_', ' ', $log->action)),
                    $log->quantity_change > 0 ? '+' . $log->quantity_change : $log->quantity_change,
                    $log->new_stock_level,
                    $log->user ? $log->user->name : 'System',
                    $log->notes,
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete_inventory');

        $ingredient = Ingredient::findOrFail($id);

        // Authorization Logic
        if (!request()->user()->is_super_admin) {
            $hasAccess = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('email', request()->user()->email)
                ->where('restaurant_id', (string) $ingredient->restaurant_id)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have permission to delete this item.');
            }
        }

        // Check if ingredient is used in any menu items (Robust Check for MongoDB)
        // 1. Get IDs from pivot collection
        $pivotItemIds = \Illuminate\Support\Facades\DB::table('menu_item_ingredients')
            ->where('ingredient_id', new ObjectId($id))
            ->pluck('menu_item_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        // 2. Find MenuItems linked via pivot or embedded recipe
        $menuItems = \App\Models\MenuItem::whereIn('_id', $pivotItemIds)
            ->orWhere('recipe.ingredient_id', $id)
            ->orWhere('recipe.ingredient_id', new ObjectId($id))
            ->get();

        if ($menuItems->count() > 0) {
            $menuItemNames = $menuItems->map(function ($item) {
                return $item->getTranslation('name', app()->getLocale()) ?: $item->name;
            })->filter()->unique()->implode(', ');

            return redirect()->back()->with('error', "Cannot delete ingredient. It is currently used in the following menu items: {$menuItemNames}. Please remove it from these items first.");
        }

        // Delete associated records from DB
        \App\Models\IngredientBatch::where('ingredient_id', $id)->delete();
        \App\Models\InventoryLog::where('ingredient_id', $id)->delete();

        $ingredient->delete();

        return redirect()->back()->with('message', 'Ingredient deleted successfully.');
    }
}
