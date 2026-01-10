<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrOrderController extends Controller
{
    /**
     * Show the public menu for QR code ordering
     */
    public function showMenu(string $token): Response
    {
        $table = Table::where('qr_code_token', $token)->firstOrFail();
        $restaurant = $table->restaurant;

        if (!$restaurant->hasFeature('qr_ordering')) {
            abort(403, 'QR Ordering is not enabled for this restaurant.');
        }

        $categories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->with([
                'items' => function ($query) {
                    $query->with('extras')
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($category) {
                return (bool) $category->is_active;
            })
            ->values()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'items' => $category->items->filter(fn($item) => (bool) $item->is_available)->map(function ($item) {
                        // Fix image URL - prepend /storage/ if image exists
                        $imageUrl = null;
                        if ($item->image) {
                            // If image already has full URL, use it
                            if (str_starts_with($item->image, 'http')) {
                                $imageUrl = $item->image;
                            } else {
                                // Otherwise, prepend /storage/
                                $imageUrl = asset('storage/' . $item->image);
                            }
                        }

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => (float) $item->price,
                            'image' => $imageUrl,
                            'images' => $item->images, // Pass generic images array
                            'currency' => $item->currency ?? 'AED',
                            'extras' => $item->extras,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();

        return Inertia::render('Public/QrMenu', [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'token' => $table->qr_code_token,
            ],
            'restaurant' => [
                'name' => $restaurant->name,
                'currency' => $restaurant->currency ?? 'AED',
                'locale' => $restaurant->locale ?? 'en',
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Place an order from QR code
     */
    public function placeOrder(Request $request, string $token)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $table = Table::where('qr_code_token', $token)->firstOrFail();
        $restaurant = $table->restaurant;

        // Calculate total
        $subtotal = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['id']);

            $itemExtrasPrice = 0;
            $itemsExtrasData = [];

            if (isset($item['extras']) && is_array($item['extras'])) {
                foreach ($item['extras'] as $extraItem) {
                    $extraId = $extraItem['id'];
                    $extraQty = $extraItem['quantity'] ?? 1;

                    // Verify extra belongs to item and get price
                    $extraModel = \App\Models\MenuItemExtra::where('id', $extraId)
                        ->where('menu_item_id', $menuItem->id)
                        ->first();

                    if ($extraModel) {
                        $extraTotal = $extraModel->price * $extraQty; // Typically extras are qty 1 per item unit, but if array supports qty
                        $itemExtrasPrice += $extraTotal;
                        $itemsExtrasData[] = [
                            'id' => $extraModel->id,
                            'name' => $extraModel->name,
                            'price' => $extraModel->price,
                            'quantity' => $extraQty
                        ];
                    }
                }
            }

            $unitPrice = $menuItem->price + $itemExtrasPrice; // Base price + extras price (per unit)
            // Wait, logic check: usually extra price is per unit of item. 
            // If I order 2 Burgers, and 1 extra Cheese. Does Cheese apply to both?
            // In QrMenu.vue: "extras: item.extras ? item.extras.map((e:any) => ({ id: e.id, quantity: e.quantity || 1 })) : []"
            // The cart item has a quantity (e.g. 2 Burgers).
            // The extra has a quantity (e.g. 1 Cheese). 
            // Usually in this UI, 1 "Burger + Cheese" Item means (Burger Price + Cheese Price) * Quantity.
            // Let's check QrMenu.vue addToCart.
            // "addToCart(customizingItem.value, extrasToAdd);"
            // It creates a single cart item. If I increase quantity of that cart item, I increase quantity of burgers AND cheese.
            // So: Total Price = (Base Price + Sum(Extra Price * Extra Qty)) * Item Quantity.

            // Re-calculating correctly:
            $singleItemTotalExtras = 0;
            if (isset($item['extras']) && is_array($item['extras'])) {
                foreach ($item['extras'] as $extraItem) {
                    $extraId = $extraItem['id'];
                    // In the current UI, e.quantity seems to be 1 usually, but let's support passed quantity
                    $extraQty = $extraItem['quantity'] ?? 1;

                    $extraModel = \App\Models\MenuItemExtra::where('id', $extraId)
                        ->where('menu_item_id', $menuItem->id)
                        ->first();

                    if ($extraModel) {
                        $singleItemTotalExtras += ($extraModel->price * $extraQty);
                        // Store normalized structure
                        $itemsExtrasData[] = [
                            'id' => $extraModel->id,
                            'name' => $extraModel->getTranslation('name', 'en') ?: $extraModel->name,
                            'price' => $extraModel->price
                        ];
                    }
                }
            }

            $lineUnitTotal = $menuItem->price + $singleItemTotalExtras;
            $lineTotal = $lineUnitTotal * $item['quantity'];

            $subtotal += $lineTotal;

            $orderItems[] = [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->price, // Base unit price
                'total_price' => $lineTotal,
                'notes' => $item['notes'] ?? null,
                'extras' => $itemsExtrasData // Store extras
            ];
        }

        // Generate Sequential Order Number
        $nextNumber = $restaurant->next_order_number ?? 1;
        try {
            // Atomically increment the order number to prevent race conditions
            $restaurant->increment('next_order_number');
        } catch (\Exception $e) {
            // Handle legacy cases where next_order_number might be stored as string
            $restaurant->update(['next_order_number' => (int) $nextNumber + 1]);
        }

        // Create order
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'order_number' => 'QR-' . $nextNumber,
            'type' => 'dine_in',
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax' => $subtotal * 0.05, // 5% tax
            'total' => $subtotal * 1.05,
            'payment_status' => 'unpaid', // Changed from 'pending' to 'unpaid' to show in POS
            'payment_method' => 'cash', // Default to cash, can be changed
            'customer_name' => $validated['customer_name'] ?? 'QR Order',
            'customer_phone' => $validated['customer_phone'] ?? null,
            'source' => 'qr_code',
            'ordered_at' => now(),
        ]);

        // Create order items
        foreach ($orderItems as $itemData) {
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'menu_item_id' => $itemData['menu_item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
                'notes' => $itemData['notes'],
                'name' => $itemData['name'],
                'extras' => $itemData['extras'],
            ]);

            $orderItem->save();
        }

        // Update table status
        $table->update(['status' => 'occupied']);

        // Broadcast order created event to POS
        broadcast(new \App\Events\OrderUpdated($order->load([
            'items.menuItem' => function ($q) {
                $q->withTrashed();
            },
            'customer',
            'table'
        ]), 'created'))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'table_name' => $table->name,
            ],
        ]);
    }

    /**
     * Get order status
     */
    public function getOrderStatus(string $token, string $orderNumber)
    {
        $table = Table::where('qr_code_token', $token)->firstOrFail();

        $order = Order::where('order_number', $orderNumber)
            ->where('table_id', $table->id)
            ->with('items')
            ->firstOrFail();

        return response()->json([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'items' => $order->items,
                'created_at' => $order->created_at,
            ],
        ]);
    }
}
