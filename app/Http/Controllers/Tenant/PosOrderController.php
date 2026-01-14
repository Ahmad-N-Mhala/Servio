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

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
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
                            'type' => $item->type ?? 'item',
                            'extras' => $item->extras,
                            'bundles' => $item->bundles,
                            'inventory_status' => $item->inventory_status,
                            'recipe' => $item->recipe,
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
                    'loyalty_points' => $customer->loyaltyPoints?->balance ?? 0,
                ];
            });

        return Inertia::render('Orders/DeliveryCreate', [
            'menuCategories' => $menuCategories,
            'customers' => $customers,
            'currency' => $restaurant->currency ?? config('app.currency', 'AED'),
        ]);
    }

    public function store(Request $request)
    {
        // Permission check handled by middleware

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_phone' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
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
            'total' => ['required', 'numeric', 'min:0'], // This will be the user-overridden price
            'notes' => ['nullable', 'string'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // Create Customer if needed
        $customer = null;
        if (!empty($validated['customer_id'])) {
            $customer = \App\Models\Customer::find($validated['customer_id']);
        } elseif (!empty($validated['customer_phone'])) {
            $customer = $this->loyaltyService->findOrCreateCustomer(
                $restaurant,
                $validated['customer_phone'],
                $validated['customer_name'] ?? null
            );
        }

        // Validate Stock
        $stockErrors = [];
        foreach ($validated['items'] as $item) {
            $menuItem = \App\Models\MenuItem::with('ingredients')->find($item['menu_item_id']);
            if ($menuItem && $menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
                    if (!$ingredient->pivot || !isset($ingredient->pivot->quantity))
                        continue;
                    $neededQty = $ingredient->pivot->quantity * $item['quantity'];
                    $availableStock = \App\Models\IngredientBatch::where('ingredient_id', $ingredient->id)
                        ->where('quantity_remaining', '>', 0)
                        ->sum('quantity_remaining');

                    if ($availableStock < $neededQty) {
                        $menuItemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;
                        $stockErrors[] = "{$menuItemName} - Insufficient stock (Need {$neededQty})";
                    }
                }
            }
        }
        if (!empty($stockErrors)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['items' => $stockErrors]);
        }

        // Generate Transaction #
        try {
            $restaurant->increment('next_order_number');
        } catch (\Exception $e) {
            // Fix string type if happens
            $restaurant->next_order_number = (int) ($restaurant->next_order_number ?? 1);
            $restaurant->save();
            $restaurant->increment('next_order_number');
        }
        $transactionNumber = $restaurant->next_order_number;
        $orderNumber = 'ORD-' . $transactionNumber;

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
            'total' => $validated['total'], // TRUSTED INPUT
            'currency' => $restaurant->currency ?? 'AED',
            'customer_name' => $validated['customer_name'] ?? ($customer ? $customer->name : 'Guest'),
            'customer_phone' => $validated['customer_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'waiter_id' => auth()->id(),
            'delivery_provider' => $orderType === 'delivery' ? ($validated['delivery_provider'] ?? null) : null,
            'delivery_order_id' => $orderType === 'delivery' ? ($validated['delivery_order_id'] ?? null) : null,
            'payment_method' => null,
            'payment_status' => 'unpaid', // Must be 'unpaid' (or null) to appear in POS list
        ]);

        // Items
        foreach ($validated['items'] as $item) {
            $menuItem = \App\Models\MenuItem::find($item['menu_item_id']);
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

        // Broadcast
        broadcast(new OrderUpdated($order->load(['items.menuItem', 'customer']), 'created'))->toOthers();

        return redirect()->back()->with('message', 'Delivery Order Created Successfully');
    }
}
