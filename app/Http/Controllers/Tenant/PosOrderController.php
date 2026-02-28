<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use App\Services\InventoryService;
use App\Events\OrderUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class PosOrderController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService,
        protected InventoryService $inventoryService
    ) {
    }

    public function create(): Response
    {
        // Check permission
        // Gate::authorize('manage_delivery_orders'); // We'll use middleware in routes, but good to have here too if we define the gate
        // Since we are using Spatie permissions via middleware, we might not need Gate::authorize unless we registered a gate.
        // The middleware 'permission:create_delivery_order' handles it.

        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Reuse data fetching logic from OrderController::create but optimized/simplified where possible

        // 1. Menu Categories & Items
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
                $sortedItems = $category->items->filter(fn($item) => (bool) $item->is_available)->sortBy(function ($item) {
                    $name = $item->name;
                    return is_array($name) ? ($name['en'] ?? $name['ar'] ?? '') : $name;
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
                            'extras' => $item->extras,
                            'bundles' => $item->bundles,
                            'inventory_status' => $item->inventory_status,
                            'recipe' => $item->recipe ?? $item->ingredients->map(function ($i) {
                                return ['ingredient_id' => $i->id, 'quantity' => $i->pivot->quantity];
                            })->all(),
                        ];
                    }),
                ];
            });

        // 2. Customers (Optional for delivery, but good to have)
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

        // 3. Rewards
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
                    'menu_item_id' => $reward->menu_item_id,
                    'menu_item_ids' => $reward->menuItems->pluck('id')->toArray(),
                    'min_order_value' => $reward->min_order_value,
                ];
            });

        // 3. Delivery Providers
        $deliveryProviders = \App\Models\DeliveryProvider::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['name', 'slug', 'logo_url']);

        // 5. Tables (for Dine In support in POS)
        $tables = \App\Models\Table::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get()
            ->map(function ($table) {
                $hasActiveOrder = \App\Models\Order::where('table_id', $table->id)
                    ->whereIn('status', ['pending', 'preparing', 'ready', 'serving'])
                    ->exists();

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'is_available' => !$hasActiveOrder,
                ];
            });

        // 6. Stock & Inventory Data (CRITICAL for validation parity)
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

        $allBatches = \App\Models\IngredientBatch::where('restaurant_id', $restaurant->id)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($batches) => $batches->sum('quantity_remaining'));

        $menuItemStockInfo = [];
        $menuItemsWithIngredients = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)->with('ingredients')->get();
        foreach ($menuItemsWithIngredients as $menuItem) {
            $maxServings = PHP_INT_MAX;
            if ($menuItem->ingredients->isEmpty()) {
                $maxServings = 999;
            } else {
                foreach ($menuItem->ingredients as $ingredient) {
                    $needed = $ingredient->pivot->quantity;
                    if ($needed > 0) {
                        $available = $allBatches[(string) $ingredient->id] ?? 0;
                        $canMake = floor($available / $needed);
                        $maxServings = min($maxServings, $canMake);
                    }
                }
            }
            if ($maxServings === PHP_INT_MAX)
                $maxServings = 999;

            $menuItemStockInfo[$menuItem->id] = [
                'max_quantity' => (int) $maxServings,
                'available' => $maxServings > 0,
                'is_tracked' => $menuItem->ingredients->isNotEmpty(),
            ];
        }

        return Inertia::render('Orders/DeliveryCreate', [
            'menuCategories' => $menuCategories,
            'customers' => $customers,
            'rewards' => $rewards,
            'currency' => $restaurant->currency ?? config('app.currency', 'AED'),
            'deliveryProviders' => $deliveryProviders,
            'tables' => $tables,
            'stockAvailability' => $menuItemStockInfo,
            'ingredientStocks' => $ingredientStocks,
            'google_map_location' => $restaurant->google_map_location,
        ]);
    }

    public function store(Request $request)
    {
        // Permission check handled by middleware

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_phone' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'customer_birth_date' => ['nullable', 'date'],
            'type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'delivery_provider' => ['nullable', 'required_if:type,delivery', 'string'],
            'delivery_order_id' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.extras' => ['nullable', 'array'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reward_id' => ['nullable', 'exists:rewards,id'],
            'otp' => ['nullable', 'string', 'size:6'],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Update table status if dine-in
        if (($validated['type'] ?? 'dine_in') === 'dine_in' && !empty($validated['table_id'])) {
            $table = \App\Models\Table::find($validated['table_id']);
            if ($table && $table->restaurant_id == $restaurant->id) {
                $table->update(['status' => 'occupied']);
            }
        }

        // Find or create customer
        $customer = null;
        if (!empty($validated['customer_id'])) {
            $customer = \App\Models\Customer::find($validated['customer_id']);
        } elseif (!empty($validated['customer_phone'])) {
            $customer = $this->loyaltyService->findOrCreateCustomer(
                $restaurant,
                $validated['customer_phone'],
                $validated['customer_name'] ?? null,
                null,
                $validated['customer_birth_date'] ?? null
            );
        }

        // ====== STOCK VALIDATION ======
        $stockErrors = [];
        $menuItemIds = collect($validated['items'])->pluck('menu_item_id')->unique()->toArray();
        $menuItems = \App\Models\MenuItem::with('ingredients')->whereIn('id', $menuItemIds)->get()->keyBy('id');

        $ingredientIds = [];
        foreach ($menuItems as $item) {
            if ($item->ingredients->isNotEmpty()) {
                $ingredientIds = array_merge($ingredientIds, $item->ingredients->pluck('id')->toArray());
            }
        }
        $ingredientIds = array_unique($ingredientIds);

        $allBatches = \App\Models\IngredientBatch::whereIn('ingredient_id', $ingredientIds)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($batches) => $batches->sum('quantity_remaining'));

        foreach ($validated['items'] as $item) {
            $menuItem = $menuItems[$item['menu_item_id']] ?? null;
            if ($menuItem && $menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
                    if (!$ingredient->pivot || !isset($ingredient->pivot->quantity))
                        continue;
                    $neededQty = $ingredient->pivot->quantity * $item['quantity'];
                    $availableStock = $allBatches[(string) $ingredient->id] ?? 0;

                    if ($availableStock < $neededQty) {
                        $menuItemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;
                        $stockErrors[] = "{$menuItemName} - Insufficient stock for '{$ingredient->name}'";
                    }
                }
            }
        }
        if (!empty($stockErrors)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['items' => $stockErrors]);
        }
        // ====== END STOCK VALIDATION ======

        // Generate Order Number with Fallback/Retry Logic
        $maxRetries = 5;
        $order = null;

        for ($i = 0; $i < $maxRetries; $i++) {
            $transactionNumber = $restaurant->next_order_number ?? 1;
            $orderNumber = 'ORD-' . $transactionNumber;

            try {
                $orderType = $validated['type'] ?? 'dine_in';
                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'order_number' => $orderNumber,
                    'transaction_number' => $transactionNumber,
                    'status' => 'pending',
                    'type' => $orderType,
                    'subtotal' => $validated['subtotal'],
                    'tax' => $validated['tax'] ?? 0,
                    'discount_amount' => $validated['discount_amount'] ?? 0,
                    'total' => $validated['total'],
                    'currency' => $restaurant->currency ?? 'AED',
                    'customer_name' => $validated['customer_name'] ?? ($customer ? $customer->name : 'Guest'),
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'waiter_id' => auth()->id(),
                    'table_id' => $validated['table_id'] ?? null,
                    'delivery_provider' => $orderType === 'delivery' ? ($validated['delivery_provider'] ?? null) : null,
                    'delivery_order_id' => $orderType === 'delivery' ? ($validated['delivery_order_id'] ?? null) : null,
                    'payment_method' => $orderType === 'delivery' ? 'online' : null,
                    'payment_status' => $orderType === 'delivery' ? 'paid' : 'pending',
                ]);

                $restaurant->increment('next_order_number');
                break;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'E11000 duplicate key error')) {
                    $restaurant->increment('next_order_number');
                    $restaurant->refresh();
                    continue;
                }
                throw $e;
            }
        }

        if (!$order)
            throw new \Exception("Failed to generate order number.");

        // Create items
        foreach ($validated['items'] as $item) {
            $menuItem = $menuItems[$item['menu_item_id']] ?? null;
            if (!$menuItem)
                continue;

            $extrasCost = 0;
            if (!empty($item['extras'])) {
                foreach ($item['extras'] as $extra)
                    $extrasCost += (float) ($extra['price'] ?? 0);
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
            if (empty($validated['otp'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['otp' => ['OTP is required.']]);
            }
            if (!$this->loyaltyService->verifyOtp($customer, $validated['otp'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
            }
            $redemption = $this->loyaltyService->redeemReward($customer, (string) $validated['reward_id']);
            $redemption->markAsUsed((string) $order->id);
        }

        // Broadcast
        broadcast(new OrderUpdated($order->load(['items.menuItem', 'customer', 'table']), 'created'))->toOthers();

        $order->refresh();
        if ($order->points_earned > 0) {
            $message = __('orders.order_created_with_points', ['order' => $order->order_number, 'points' => $order->points_earned]);
        } else {
            $message = __('orders.order_created_successfully', ['order' => $order->order_number]);
        }

        return back()->with('message', $message);
    }
}
