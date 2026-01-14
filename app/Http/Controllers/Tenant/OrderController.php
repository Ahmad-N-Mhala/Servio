<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use App\Services\InventoryService;
use App\Events\OrderUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService,
        protected InventoryService $inventoryService
    ) {
    }

    public function index(Request $request): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->with([
                'customer',
                'waiter',
                'items.menuItem' => function ($q) {
                    $q->withTrashed();
                }
            ]);

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
        // Date Range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->input('start_date'))->startOfDay());
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->input('end_date'))->endOfDay());
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
            'currency' => $restaurant->currency ?? config('app.currency', 'AED'),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'start_date', 'end_date']),
        ]);
    }

    public function show(Order $order)
    {
        // Load all necessary relationships for printing
        $order->load(['customer', 'table', 'items.menuItem', 'restaurant', 'waiter']);

        // Return as JSON
        return response()->json($order);
    }

    public function export(Request $request)
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->with([
                'waiter',
                'items.menuItem' => function ($q) {
                    $q->withTrashed();
                }
            ]);

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
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->input('start_date'))->startOfDay());
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->input('end_date'))->endOfDay());
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

        $columns = [
            __('reports.order_number'),
            __('reports.customer_name'),
            __('reports.phone'),
            __('reports.waiter'),
            __('reports.status'),
            __('orders.total'),
            __('reports.currency'),
            __('reports.delivery_provider'),
            __('reports.created_at')
        ];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $statusKey = 'orders.' . strtolower($order->status);
                $statusTranslated = __($statusKey);
                // If translation equals key (missing), fallback to ucfirst
                if ($statusTranslated === $statusKey) {
                    $statusTranslated = ucfirst($order->status);
                }

                $row = [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_phone,
                    $order->waiter->name ?? '-',
                    $statusTranslated,
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
            ->with([
                'items' => function ($query) {
                    $query->with(['ingredients', 'extras', 'bundles.childItem']);
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($category) {
                return (bool) $category->is_active;
            })
            ->values()
            ->map(function ($category) {
                // Sort items by name in PHP (handle JSON translations)
                $sortedItems = $category->items->filter(fn($item) => (bool) $item->is_available)->sortBy(function ($item) {
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
                            'type' => $item->type ?? 'item',
                            'extras' => $item->extras, // Pass extras to frontend
                            'bundles' => $item->bundles, // Pass bundles to frontend
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
            'currency' => $restaurant->currency ?? config('app.currency', 'AED'),
            'stockAvailability' => $menuItemStockInfo,
            'ingredientStocks' => $ingredientStocks,
            'google_map_location' => $restaurant->google_map_location,
        ]);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('pos_system');

        $validated = $request->validate([
            'customer_phone' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'customer_birth_date' => ['nullable', 'date'],
            'type' => ['required', 'in:dine_in,takeaway,delivery'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'], // Made optional for all order types
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reward_id' => ['nullable', 'exists:rewards,id'],
            'otp' => ['nullable', 'string', 'size:6'],
            'delivery_provider' => ['nullable', 'string', 'required_if:type,delivery'],
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

        // Generate Transaction Number (Sequential)
        $transactionNumber = $restaurant->next_order_number ?? 1;

        try {
            $restaurant->increment('next_order_number');
        } catch (\Exception $e) {
            // Handle case where next_order_number is stored as string in MongoDB (Cannot apply $inc)
            // We fix the type by saving it as integer and retry. This self-heals any bad data.
            if (str_contains($e->getMessage(), 'non-numeric type') || str_contains($e->getMessage(), 'Apply $inc to a value of non-numeric type')) {
                $restaurant->next_order_number = (int) ($restaurant->next_order_number ?? 1);
                $restaurant->save();
                $restaurant->increment('next_order_number');
            } else {
                throw $e;
            }
        }

        // Generate Order Number
        // We use the sequential transaction number (per-restaurant) as the order number
        $orderNumber = 'ORD-' . $transactionNumber;

        // Create order
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer ? $customer->id : null,
            'order_number' => $orderNumber,
            'transaction_number' => $transactionNumber,
            'status' => 'pending',
            'type' => $validated['type'],
            'table_id' => $validated['table_id'] ?? null,
            'subtotal' => $validated['subtotal'],
            'tax' => $validated['tax'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'total' => $validated['total'],
            'currency' => $restaurant->currency ?? config('app.currency', 'AED'),
            'customer_name' => $validated['customer_name'] ?? ($customer ? $customer->name : 'Guest'),
            'customer_phone' => $validated['customer_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'waiter_id' => auth()->id(),
            'delivery_provider' => $validated['delivery_provider'] ?? null,
            'payment_method' => $validated['type'] === 'delivery' ? 'online' : null,
            'payment_status' => $validated['type'] === 'delivery' ? 'paid' : 'pending',
        ]);

        // Create order items
        foreach ($validated['items'] as $item) {
            $menuItem = \App\Models\MenuItem::find($item['menu_item_id']);
            if (!$menuItem)
                continue; // Should be handled by validation, but safe check

            $extrasCost = 0;
            if (!empty($item['extras']) && is_array($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    // Assuming extra price is per unit of the main item
                    $extrasCost += (float) ($extra['price'] ?? 0);
                }
            }

            $itemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;

            $order->items()->create([
                'menu_item_id' => $item['menu_item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * ($item['unit_price'] + $extrasCost),
                'name' => $itemName,
                'notes' => $item['notes'] ?? null,
                'extras' => $item['extras'] ?? null,
            ]);
        }


        // Handle Reward Redemption
        if (!empty($validated['reward_id']) && $customer) {
            $reward = \App\Models\Reward::find($validated['reward_id']);

            if ($reward && $reward->min_order_value > 0 && $validated['subtotal'] < $reward->min_order_value) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reward_id' => ["Minimum order value of " . (float) $reward->min_order_value . " required for this reward."]
                ]);
            }

            if ($reward) {
                // Verify OTP
                if (empty($validated['otp'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'otp' => ['OTP is required for reward redemption.']
                    ]);
                }

                if (!$this->loyaltyService->verifyOtp($customer, $validated['otp'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'otp' => ['Invalid or expired OTP.']
                    ]);
                }

                $redemption = $this->loyaltyService->redeemReward($customer, (string) $validated['reward_id']);
                $redemption->markAsUsed($order->id);
            }
        }

        // Broadcast order created event for real-time updates
        broadcast(new OrderUpdated($order->load([
            'items.menuItem' => function ($q) {
                $q->withTrashed();
            },
            'customer',
            'table'
        ]), 'created'))->toOthers();

        // Refresh to check for points
        $order->refresh();

        $message = "Order #{$order->order_number} Created Successfully.";
        if ($order->points_earned > 0) {
            $message .= " +{$order->points_earned} Loyalty Points Earned!";
        }

        return redirect()->back()->with('message', $message);
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
            $order->load(['items.menuItem.ingredients', 'items.menuItem.bundles.childItem', 'items.menuItem.extras']);

            foreach ($order->items as $orderItem) {
                if ($orderItem->menuItem) {
                    // Deduct Main Item and potential Bundles
                    $this->processInventoryForMenuItem($orderItem->menuItem, $orderItem->quantity, $order);
                }

                // Deduct Extras
                if (!empty($orderItem->extras)) {
                    foreach ($orderItem->extras as $extra) {
                        // Check structure: assuming frontend sends { name, price, ingredient_id, quantity }
                        // or we might need to look it up if only ID sent. Assuming full object snapshot for robustness in order history.
                        $ingId = $extra['ingredient_id'] ?? null;
                        $qty = $extra['quantity'] ?? 0;

                        if ($ingId && $qty > 0) {
                            $ingredient = \App\Models\Ingredient::find($ingId);
                            if ($ingredient) {
                                $extraName = is_array($extra['name']) ? ($extra['name']['en'] ?? reset($extra['name'])) : $extra['name'];

                                $totalExtraQty = (float) ($qty * $orderItem->quantity);
                                $this->inventoryService->deductStock(
                                    $ingredient,
                                    $totalExtraQty,
                                    "Extra '{$extraName}' ({$totalExtraQty} {$ingredient->unit}) on Order #{$order->order_number}",
                                    auth()->id()
                                );
                            }
                        }
                    }
                }
            }
        }

        // Handle Delivery Order Approval
        if (
            $order->delivery_provider &&
            in_array($validated['status'], ['processing', 'preparing']) &&
            in_array($oldStatus, ['pending', 'pending_approval'])
        ) {
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => 'online',
            ]);
        }
        // ====== END INVENTORY DEDUCTION ======

        // Broadcast order status changed event for real-time updates
        broadcast(new OrderUpdated($order->load(['items.menuItem', 'customer', 'table']), 'status_changed'))->toOthers();

        return redirect()->back()->with('message', __('orders.status_updated'));
    }

    public function receipt(Order $order)
    {
        $order->load(['customer.loyaltyPoints', 'table', 'items.menuItem', 'restaurant', 'waiter']);

        $restaurant = $order->restaurant; // Use the relationship

        // Prepare template settings - Handle Mongo BSON
        $settings = $restaurant->receipt_template;
        if (is_object($settings) && method_exists($settings, 'getArrayCopy')) {
            $settings = $settings->getArrayCopy();
        } elseif (!is_array($settings)) {
            $settings = [];
        }

        return Inertia::render('Orders/Receipt', [
            'order' => $order,
            'template' => $settings,
            'logo' => $restaurant->logo,
            'restaurantName' => $restaurant->name,
            'google_map_location' => $restaurant->google_map_location,
        ]);
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

    /**
     * Helper to process inventory deduction recursively via Service
     */
    private function processInventoryForMenuItem($menuItem, $qty, $order)
    {
        if (!$menuItem)
            return;

        $itemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;

        // A. If Meal, process bundles
        if (($menuItem->type ?? 'item') === 'meal') {
            if (!$menuItem->relationLoaded('bundles')) {
                $menuItem->load(['bundles.childItem']);
            }

            foreach ($menuItem->bundles as $bundle) {
                if ($bundle->childItem) {
                    $this->processInventoryForMenuItem(
                        $bundle->childItem,
                        $qty * ($bundle->quantity ?? 1),
                        $order
                    );
                }
            }
            // Don't return, as Meal itself *could* have ingredients too? Usually not, but if so, let's process them.
            // But usually a wrapper meal just sums items.
        }

        // B. Process Recipe (Standard or Legacy)
        $recipe = $menuItem->recipe ?? [];
        if (!empty($recipe)) {
            foreach ($recipe as $component) {
                $ingId = $component['ingredient_id'] ?? null;
                $needed = (float) ($component['quantity'] ?? 0);

                if ($ingId && $needed > 0) {
                    $ingredient = \App\Models\Ingredient::find($ingId);
                    if ($ingredient) {
                        $this->inventoryService->deductStock(
                            $ingredient,
                            $needed * $qty,
                            "Item '{$itemName}' Order #{$order->order_number}",
                            auth()->id()
                        );
                    }
                }
            }
        } elseif ($menuItem->relationLoaded('ingredients') && $menuItem->ingredients->isNotEmpty()) {
            // Fallback to legacy Pivot
            foreach ($menuItem->ingredients as $ingredient) {
                $pivotQty = $ingredient->pivot->quantity ?? 0;
                if ($pivotQty > 0) {
                    $this->inventoryService->deductStock(
                        $ingredient,
                        $pivotQty * $qty,
                        "Item '{$itemName}' Order #{$order->order_number}",
                        auth()->id()
                    );
                }
            }
        }
    }
}
