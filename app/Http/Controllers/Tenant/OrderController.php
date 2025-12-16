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
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->with(['customer']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_phone', 'ilike', "%{$search}%")
                    ->orWhere('delivery_provider', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(total AS TEXT) ilike ?', ["%{$search}%"]);
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
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_phone', 'ilike', "%{$search}%")
                    ->orWhere('delivery_provider', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(total AS TEXT) ilike ?', ["%{$search}%"]);
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

        $columns = ['Order Number', 'Customer Name', 'Phone', 'Status', 'Total', 'Currency', 'Delivery Provider', 'Created At'];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row = [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_phone,
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

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

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
                    $query->where('is_available', true)->orderByRaw("name->>'en' ASC");
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'items' => $category->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'price' => (float) $item->price,
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

        // Calculate stock availability for each menu item
        $menuItemStockInfo = [];
        foreach (\App\Models\MenuItem::where('restaurant_id', $restaurant->id)->with('ingredients')->get() as $menuItem) {
            $maxServings = PHP_INT_MAX; // Start with infinite

            if ($menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
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
            ];
        }

        return Inertia::render('Orders/Create', [
            'menuCategories' => $menuCategories,
            'customers' => $customers,
            'rewards' => $rewards,
            'tables' => $tables,
            'currency' => $restaurant->currency ?? 'AED',
            'stockAvailability' => $menuItemStockInfo,
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

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        return DB::transaction(function () use ($validated, $restaurant) {
            // Update table status if dine-in
            if ($validated['type'] === 'dine_in' && !empty($validated['table_id'])) {
                $table = \App\Models\Table::find($validated['table_id']);
                if ($table) {
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
            ]);

            // Create order items and Update Inventory
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                // Deduct Inventory using FIFO (First-In, First-Out) Batch Logic
                $menuItem = \App\Models\MenuItem::with('ingredients')->find($item['menu_item_id']);
                if ($menuItem && $menuItem->ingredients->isNotEmpty()) {
                    foreach ($menuItem->ingredients as $ingredient) {
                        $neededQty = $ingredient->pivot->quantity * $item['quantity'];
                        $remainingQty = $neededQty;

                        // Get available batches (FIFO - oldest first)
                        $batches = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)
                            ->where('quantity_remaining', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $totalCost = 0;
                        $batchesUsed = [];

                        foreach ($batches as $batch) {
                            if ($remainingQty <= 0)
                                break;

                            $deductFromBatch = min($remainingQty, $batch->quantity_remaining);
                            $batch->decrement('quantity_remaining', $deductFromBatch);

                            $totalCost += $deductFromBatch * $batch->cost_per_unit;
                            $batchesUsed[] = "{$batch->batch_number} ({$deductFromBatch})";

                            $remainingQty -= $deductFromBatch;
                        }

                        // Safety check: ensure we deducted the full amount
                        if ($remainingQty > 0.0001) { // Small epsilon for floating point
                            throw new \Exception("Critical Error: Unable to fulfill order. Insufficient stock for {$ingredient->name}. This should have been caught by validation.");
                        }

                        // Update ingredient's global stock
                        $ingredient->decrement('current_stock', $neededQty);
                        $ingredient->refresh();

                        // Log Usage with batch details
                        \App\Models\InventoryLog::create([
                            'restaurant_id' => $restaurant->id,
                            'ingredient_id' => $ingredient->id,
                            'user_id' => auth()->id(),
                            'action' => 'used_in_menu',
                            'quantity_change' => -$neededQty,
                            'new_stock_level' => $ingredient->current_stock,
                            'notes' => 'Order #' . $order->order_number . ' | Batches: ' . implode(', ', $batchesUsed),
                        ]);
                    }
                }
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
        });
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

        return redirect()->back()->with('message', __('orders.status_updated'));
    }

    public function generateBill(Order $order)
    {
        // Load relationships needed for the bill
        $order->load(['customer', 'table', 'items.menuItem']);

        // Generate PDF
        $pdf = Pdf::loadView('bills.order', [
            'order' => $order,
            'tenant' => tenant(),
        ]);

        // Return PDF for display in browser
        return $pdf->stream("bill-{$order->id}.pdf");
    }
}
