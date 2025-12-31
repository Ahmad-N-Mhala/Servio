<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {
    }

    public function index(Request $request): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->with(['customer', 'waiter']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('delivery_provider', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('total', (float) $search);
                }
            });
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSorts = ['order_number', 'customer_name', 'total', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Live', [
            'orders' => $orders,
            'currency' => $restaurant->currency ?? 'AED',
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'start_date', 'end_date']),
        ]);
    }

    public function export(Request $request)
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->with('waiter');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('delivery_provider', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('total', (float) $search);
                }
            });
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['order_number', 'customer_name', 'total', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=orders_" . date('Y-m-d_H-i') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Order Number', 'Customer Name', 'Phone', 'Waiter', 'Status', 'Total', 'Currency', 'Delivery Provider', 'Created At'];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row = [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_phone,
                    $order->waiter->name ?? '-',
                    ucfirst($order->status),
                    $order->total,
                    $order->currency,
                    ucfirst($order->delivery_provider ?? ''),
                    $order->created_at->format('Y-m-d H:i:s')
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('pos_system');

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Get tables with availability status
        $tables = \App\Models\Table::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get()
            ->map(function ($table) {
                // Check if table has any active/incomplete orders
                $hasActiveOrder = \App\Models\Order::where('table_id', $table->id)
                    ->whereIn('status', ['pending', 'preparing', 'ready', 'serving'])
                    ->exists();

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'is_available' => !$hasActiveOrder, // Available if no active orders
                ];
            });

        // Get menu categories with items for order creation
        $menuCategories = \App\Models\MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query->where('is_available', true)
                        ->with('ingredients'); // Load ingredients for stock check
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                // Sort items by name in PHP (handle JSON translations)
                $sortedItems = $category->items->sortBy(function ($item) {
                    $name = $item->name;
                    if (is_array($name)) {
                        return $name['en'] ?? $name['ar'] ?? '';
                    }
                    return $name;
                })->each(function ($item) {
                    $item->append('inventory_status');
                })->values();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'items' => $sortedItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'price' => (float) $item->price,
                            'description' => $item->description,
                            'image' => $item->image,
                            'images' => $item->images,
                            'inventory_status' => $item->inventory_status,
                            'recipe' => $item->recipe ?? $item->ingredients->map(function ($i) {
                                return ['ingredient_id' => $i->id, 'quantity' => $i->pivot->quantity];
                            })->all(),
                        ];
                    }),
                ];
            });

        // Get customers with loyalty points for selection
        $customers = \App\Models\Customer::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with('loyaltyPoints')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'loyalty_points' => $customer->loyaltyPoints?->balance ?? 0,
                ];
            });

        // Get active rewards for redemption
        $rewards = \App\Models\Reward::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with(['menuItems'])
            ->orderBy('points_required')
            ->get()
            ->map(function ($reward) {
                return [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'description' => $reward->description,
                    'points_required' => $reward->points_required,
                    'reward_type' => $reward->reward_type,
                    'discount_value' => $reward->discount_value,
                    'menu_item_id' => $reward->menu_item_id, // Keep for legacy/fallback
                    'menu_item_ids' => $reward->menuItems->pluck('id')->toArray(),
                    'min_order_value' => $reward->min_order_value,
                ];
            });

        // Get raw ingredient stocks for frontend validation
        $ingredientStocks = \App\Models\Ingredient::where('restaurant_id', $restaurant->id)
            ->get(['id', 'current_stock', 'name'])
            ->mapWithKeys(function ($ing) {
                return [
                    $ing->id => [
                        'current_stock' => (float) $ing->current_stock,
                        'name' => $ing->name
                    ]
                ];
            });

        // Calculate stock availability for each menu item
        $menuItemStockInfo = [];
        foreach (\App\Models\MenuItem::where('restaurant_id', $restaurant->id)->with('ingredients')->get() as $menuItem) {
            $maxServings = PHP_INT_MAX; // Start with infinite

            if ($menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
                    // Skip if pivot data is missing
                    if (!$ingredient->pivot || !isset($ingredient->pivot->quantity)) {
                        continue;
                    }

                    $requiredPerServing = $ingredient->pivot->quantity;

                    // Get available stock from batches
                    $availableStock = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)
                        ->where('quantity_remaining', '>', 0)
                        ->sum('quantity_remaining');

                    // Calculate how many servings we can make with this ingredient
                    if ($requiredPerServing > 0) {
                        $possibleServings = floor($availableStock / $requiredPerServing);
                        $maxServings = min($maxServings, $possibleServings);
                    }
                }
            }

            // If item has no ingredients, allow unlimited
            if ($maxServings === PHP_INT_MAX) {
                $maxServings = 999;
            }

            $menuItemStockInfo[$menuItem->id] = [
                'max_quantity' => (int) $maxServings,
                'available' => $maxServings > 0,
                'is_tracked' => $menuItem->ingredients->isNotEmpty(),
            ];
        }

        return Inertia::render('Orders/Create', [
            'menuCategories' => $menuCategories,
            'customers' => $customers,
            'rewards' => $rewards,
            'tables' => $tables,
            'currency' => $restaurant->currency ?? 'AED',
            'stockAvailability' => $menuItemStockInfo,
            'ingredientStocks' => $ingredientStocks,
        ]);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('pos_system');

        $validated = $request->validate([
            'customer_phone' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'customer_birth_date' => ['nullable', 'date'],
            'type' => ['required', 'in:dine_in,takeaway'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'], // Made optional for all order types
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reward_id' => ['nullable', 'exists:rewards,id'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Note: MongoDB transactions require replica sets, so we execute without transaction wrapper
        // Update table status if dine-in
        if ($validated['type'] === 'dine_in' && !empty($validated['table_id'])) {
            $table = \App\Models\Table::find($validated['table_id']);
            if ($table) {
                // Ensure table belongs to restaurant
                if ($table->restaurant_id != $restaurant->id) {
                    abort(403, 'Table does not belong to this restaurant');
                }
                $table->update(['status' => 'occupied']);
            }
        }

        // Find or create customer ONLY if phone is provided
        $customer = null;
        if (!empty($validated['customer_phone'])) {
            $customer = $this->loyaltyService->findOrCreateCustomer(
                $restaurant,
                $validated['customer_phone'],
                $validated['customer_name'] ?? null,
                null, // email
                $validated['customer_birth_date'] ?? null
            );
        }

        // ====== STOCK VALIDATION BEFORE ORDER CREATION ======
        $stockErrors = [];
        foreach ($validated['items'] as $item) {
            $menuItem = \App\Models\MenuItem::with('ingredients')->find($item['menu_item_id']);

            if ($menuItem && $menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
                    // Skip if pivot data is missing
                    if (!$ingredient->pivot || !isset($ingredient->pivot->quantity)) {
                        continue;
                    }

                    $neededQty = $ingredient->pivot->quantity * $item['quantity'];

                    // Get available stock from batches
                    $availableStock = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)
                        ->where('quantity_remaining', '>', 0)
                        ->sum('quantity_remaining');

                    if ($availableStock < $neededQty) {
                        // Get menu item name (handle translations)
                        $menuItemName = $menuItem->name;
                        if (is_array($menuItemName)) {
                            $menuItemName = $menuItemName['en'] ?? $menuItemName['ar'] ?? 'Unknown Item';
                        }

                        // Get ingredient name (handle translations)
                        $ingredientName = $ingredient->name;
                        if (is_array($ingredientName)) {
                            $ingredientName = $ingredientName['en'] ?? $ingredientName['ar'] ?? 'Unknown Ingredient';
                        }

                        $stockErrors[] = "{$menuItemName} - Insufficient stock for ingredient '{$ingredientName}'. Available: {$availableStock} {$ingredient->unit}, Required: {$neededQty} {$ingredient->unit}";
                    }
                }
            }
        }
        // If there are stock errors, abort the transaction and return error
        if (!empty($stockErrors)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'items' => $stockErrors
            ]);
        }
        // ====== END STOCK VALIDATION ======

        // Create order
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer ? $customer->id : null,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'pending',
            'type' => $validated['type'],
            'table_id' => $validated['table_id'] ?? null,
            'subtotal' => $validated['subtotal'],
            'tax' => $validated['tax'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'total' => $validated['total'],
            'currency' => $restaurant->currency ?? 'AED',
            'customer_name' => $validated['customer_name'] ?? ($customer ? $customer->name : 'Guest'),
            'customer_phone' => $validated['customer_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'waiter_id' => auth()->id(),
        ]);

        // Create order items
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'menu_item_id' => $item['menu_item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Handle Reward Redemption
        if (!empty($validated['reward_id']) && $customer) {
            $reward = \App\Models\Reward::find($validated['reward_id']);

            if ($reward && $reward->min_order_value > 0 && $validated['subtotal'] < $reward->min_order_value) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reward_id' => ["Minimum order value of {$reward->min_order_value} required for this reward."]
                ]);
            }

            if ($reward) {
                $redemption = $this->loyaltyService->redeemReward($customer, (int) $validated['reward_id']);
                $redemption->markAsUsed($order->id);
            }
        }

        return redirect()->back()->with('message', __('orders.order_created'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled,deleted'],
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        // Note: Loyalty points are automatically processed by Order model observer
        // when status changes to 'completed'

        // Reverse loyalty points if order is deleted
        if ($validated['status'] === 'deleted' && $order->points_earned > 0) {
            // Reverse points if they were earned
            $customer = $order->customer;
            if ($customer) {
                $customer->decrement('loyalty_points', $order->points_earned);
                $order->update(['points_earned' => 0]);
            }
        }

        // ====== INVENTORY DEDUCTION LOGIC ======
        // Deduct Inventory when status moves from 'pending' to any active cooking/served state
        if ($oldStatus === 'pending' && in_array($validated['status'], ['processing', 'completed', 'served'])) {
            $order->load(['items.menuItem.ingredients']);

            foreach ($order->items as $item) {
                $menuItem = $item->menuItem;
                if ($menuItem) {
                    $recipe = $menuItem->recipe ?? []; // Use embedded recipe
                    $hasRecipe = !empty($recipe);

                    if ($hasRecipe) {
                        // NEW LOGIC: Use embedded recipe
                        foreach ($recipe as $component) {
                            $ingId = $component['ingredient_id'] ?? null;
                            $qtyNeeded = (float) ($component['quantity'] ?? 0);

                            if ($ingId && $qtyNeeded > 0) {
                                // Fetch ingredient
                                $ingredient = \App\Models\Ingredient::find($ingId);

                                if ($ingredient) {
                                    $totalNeeded = $qtyNeeded * $item->quantity;

                                    // Log debug
                                    \Illuminate\Support\Facades\Log::info("Deducting Stock (Recipe) order #{$order->order_number}", [
                                        'ing' => $ingredient->name,
                                        'qty' => $totalNeeded
                                    ]);

                                    // Deduct from batches
                                    $remainingQty = $totalNeeded;

                                    // Robust Query: handle potentially mixed ID types if needed, but standard is string
                                    $batches = \App\Models\IngredientBatch::where('ingredient_id', (string) $ingredient->id)
                                        ->where('quantity_remaining', '>', 0)
                                        ->orderBy('created_at', 'asc')->get();

                                    $batchesUsed = [];
                                    if ($batches->isEmpty()) {
                                        \Illuminate\Support\Facades\Log::warning("No active batches found for ingredient {$ingredient->name} (ID: {$ingredient->id})");
                                    }

                                    foreach ($batches as $batch) {
                                        if ($remainingQty <= 0)
                                            break;

                                        $deduct = min($remainingQty, (float) $batch->quantity_remaining);

                                        // Manual update to ensure persistence
                                        $batch->quantity_remaining = (float) $batch->quantity_remaining - $deduct;
                                        $batch->save();

                                        $remainingQty -= $deduct;
                                        $batchesUsed[] = "{$batch->batch_number} ({$deduct})";
                                    }

                                    $ingredient->updateCostFromFIFO();
                                    $ingredient = $ingredient->fresh();

                                    // Manual update for global stock consistency
                                    $ingredient->current_stock = (float) $ingredient->current_stock - $totalNeeded;
                                    $ingredient->save();

                                    \App\Models\InventoryLog::create([
                                        'restaurant_id' => $order->restaurant_id,
                                        'ingredient_id' => $ingredient->id,
                                        'user_id' => auth()->id(),
                                        'action' => 'used_in_menu',
                                        'quantity_change' => -$totalNeeded,
                                        'new_stock_level' => $ingredient->current_stock,
                                        'notes' => 'Cooked Order #' . $order->order_number . ' | Batches: ' . implode(', ', $batchesUsed),
                                    ]);
                                }
                            }
                        }
                    } elseif ($menuItem->ingredients->isNotEmpty()) {
                        // OLD LOGIC FALLBACK
                        foreach ($menuItem->ingredients as $ingredient) {
                            $pivotQty = 0;
                            if ($ingredient->pivot && isset($ingredient->pivot->quantity)) {
                                $pivotQty = $ingredient->pivot->quantity;
                            } else {
                                $pivotRecord = \App\Models\MenuItemIngredient::where('menu_item_id', $menuItem->id)
                                    ->where('ingredient_id', $ingredient->id)->first();
                                if ($pivotRecord)
                                    $pivotQty = $pivotRecord->quantity;
                            }

                            if ($pivotQty <= 0)
                                continue;

                            $neededQty = $pivotQty * $item->quantity;
                            $remainingQty = $neededQty;

                            // Deduct from batches
                            $batches = \App\Models\IngredientBatch::where('ingredient_id', (string) $ingredient->id)
                                ->where('quantity_remaining', '>', 0)
                                ->orderBy('created_at', 'asc')->get();

                            $batchesUsed = [];
                            foreach ($batches as $batch) {
                                if ($remainingQty <= 0)
                                    break;

                                $deduct = min($remainingQty, (float) $batch->quantity_remaining);
                                $batch->quantity_remaining = (float) $batch->quantity_remaining - $deduct;
                                $batch->save();

                                $remainingQty -= $deduct;
                                $batchesUsed[] = "{$batch->batch_number} ({$deduct})";
                            }

                            $ingredient->updateCostFromFIFO();
                            $ingredient = $ingredient->fresh();
                            $ingredient->current_stock = (float) $ingredient->current_stock - $neededQty;
                            $ingredient->save();

                            \App\Models\InventoryLog::create([
                                'restaurant_id' => $order->restaurant_id,
                                'ingredient_id' => $ingredient->id,
                                'user_id' => auth()->id(),
                                'action' => 'used_in_menu',
                                'quantity_change' => -$neededQty,
                                'new_stock_level' => $ingredient->current_stock,
                                'notes' => 'Cooked Order #' . $order->order_number . ' | Batches: ' . implode(', ', $batchesUsed),
                            ]);
                        }
                    }
                }
            }
        }
        // ====== END INVENTORY DEDUCTION ======

        return redirect()->back()->with('message', __('orders.status_updated'));
    }

    public function generateBill(Order $order)
    {
        // Load relationships needed for the bill
        $order->load(['customer', 'table', 'items.menuItem', 'restaurant']);

        // Generate PDF
        $pdf = Pdf::loadView('bills.order', [
            'order' => $order,
            'tenant' => tenant(),
        ]);

        // Return PDF for display in browser
        return $pdf->stream("bill-{$order->id}.pdf");
    }
}
