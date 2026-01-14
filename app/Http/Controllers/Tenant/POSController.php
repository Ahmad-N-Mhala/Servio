<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index(): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        \Log::info('POS Index - Restaurant ID: ' . $restaurant->id);

        $orders = Order::with([
            'items.menuItem' => function ($query) {
                $query->withTrashed();
            },
            'customer',
            'customer',
            'table',
            'waiter'
        ])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'pending_approval', 'completed', 'processing', 'ready', 'served'])
            ->where(function ($query) {
                $query->whereIn('payment_status', ['unpaid', 'pending'])
                    ->orWhereNull('payment_status');  // Include orders without payment_status field
            })
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('POS Index - Orders count: ' . $orders->count());
        \Log::info('POS Index - Total orders for restaurant: ' . Order::where('restaurant_id', $restaurant->id)->count());

        $tables = Table::where('restaurant_id', $restaurant->id)->get();

        // Get menu items for order updates
        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
            ->with(['category', 'extras'])
            ->orderBy('name->en')
            ->get()
            ->filter(fn($item) => (bool) $item->is_available)
            ->values();

        // Get current open cash register for this user
        $currentRegister = \App\Models\CashRegister::where('restaurant_id', $restaurant->id)
            ->where('user_id', auth()->id())
            ->where('status', 'open')
            ->with([
                'transactions' => function ($query) {
                    $query->latest()->limit(20);
                }
            ])
            ->first();

        return Inertia::render('POS/Index', [
            'orders' => $orders,
            'tables' => $tables,
            'menuItems' => $menuItems,
            'currentRegister' => $currentRegister,
            'currentBalance' => $currentRegister ? $currentRegister->getCurrentBalance() : 0,
            'google_map_location' => $restaurant->google_map_location,
            'receipt_template' => $restaurant->receipt_template,
        ]);
    }

    public function settle(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,card,online'],
        ]);

        // Check if cash register is open for cash payments
        if ($validated['payment_method'] === 'cash') {
            $cashRegister = \App\Models\CashRegister::where('restaurant_id', $order->restaurant_id)
                ->where('user_id', auth()->id())
                ->where('status', 'open')
                ->first();

            if (!$cashRegister) {
                return redirect()->back()->withErrors([
                    'payment_method' => 'Cash register must be open to accept cash payments. Please open your cash register first.'
                ]);
            }
        }

        // Note: MongoDB transactions require replica sets, executing without transaction
        // Update Order
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'status' => $order->status === 'served' ? 'completed' : $order->status,
            'completed_at' => $order->status === 'served' ? now() : (($order->status === 'completed') ? $order->completed_at : null),
        ]);

        // Record cash sale in cash register if payment is cash
        if ($validated['payment_method'] === 'cash') {
            $cashRegister = \App\Models\CashRegister::where('restaurant_id', $order->restaurant_id)
                ->where('user_id', auth()->id())
                ->where('status', 'open')
                ->first();

            if ($cashRegister) {
                $currentBalance = $cashRegister->getCurrentBalance();
                $newBalance = $currentBalance + $order->total;

                \App\Models\CashTransaction::create([
                    'cash_register_id' => $cashRegister->id,
                    'restaurant_id' => $order->restaurant_id,
                    'user_id' => auth()->id(),
                    'order_id' => $order->id,
                    'type' => 'sale',
                    'amount' => $order->total,
                    'balance_after' => $newBalance,
                    'notes' => 'Cash payment for order #' . $order->id,
                ]);
            }
        }

        // Note: Loyalty points are automatically processed by Order model observer
        // when status changes to 'completed'

        // Update Table if exists
        if ($order->table_id) {
            $table = Table::find($order->table_id);
            if ($table) {
                $table->update(['status' => 'available']);
            }
        }

        return redirect()->back()->with('message', 'Order settled and table marked available.');
    }
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'items' => ['sometimes', 'array'],
            'items.*.id' => ['sometimes', 'string'],
            'items.*.menu_item_id' => ['sometimes', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.unit_price' => ['sometimes', 'numeric', 'min:0'],
            'items.*.notes' => ['sometimes', 'string', 'nullable'],
            'discount_type' => ['required', 'string', 'in:fixed,percent'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'additional_charge_type' => ['required', 'string', 'in:fixed,percent'],
            'additional_charge_value' => ['required', 'numeric', 'min:0'],
            // Customer details
            'customer_name' => ['sometimes', 'string', 'nullable'],
            'customer_phone' => ['sometimes', 'string', 'nullable'],
            // Order type and table
            'type' => ['sometimes', 'string', 'in:dine_in,takeaway'],
            'table_id' => ['sometimes', 'string', 'nullable'],
        ]);

        // 1. Update Items
        if ($request->has('items')) {
            foreach ($validated['items'] as $itemData) {
                // Check if this is a new item (has menu_item_id) or existing item (has id)
                if (isset($itemData['menu_item_id'])) {
                    // New item - create it
                    $menuItem = \App\Models\MenuItem::find($itemData['menu_item_id']);

                    $extrasCost = 0;
                    if (!empty($itemData['extras']) && is_array($itemData['extras'])) {
                        foreach ($itemData['extras'] as $extra) {
                            $extrasCost += (float) ($extra['price'] ?? 0);
                        }
                    }

                    if ($menuItem) {
                        \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'menu_item_id' => $menuItem->id,
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'] ?? $menuItem->price,
                            'total_price' => $itemData['quantity'] * (($itemData['unit_price'] ?? $menuItem->price) + $extrasCost),
                            'notes' => $itemData['notes'] ?? '',
                            'extras' => $itemData['extras'] ?? null,
                        ]);
                    }
                } elseif (isset($itemData['id'])) {
                    // Existing item - update it
                    $orderItem = $order->items()->where('_id', $itemData['id'])->first();
                    if ($orderItem) {
                        if ($itemData['quantity'] <= 0) {
                            $orderItem->delete();
                        } else {
                            $price = $orderItem->unit_price;
                            if ($price <= 0 && $orderItem->menuItem) {
                                $price = $orderItem->menuItem->price;
                                $orderItem->unit_price = $price;
                            }

                            // storage format of extras in Mongo might be array of objects
                            $existingExtrasCost = 0;
                            if (!empty($orderItem->extras) && is_array($orderItem->extras)) {
                                foreach ($orderItem->extras as $ex) {
                                    $existingExtrasCost += (float) ($ex['price'] ?? 0);
                                }
                            }

                            $orderItem->update([
                                'quantity' => $itemData['quantity'],
                                'total_price' => $itemData['quantity'] * ($price + $existingExtrasCost),
                                'unit_price' => $price
                            ]);
                        }
                    }
                }
            }
        }

        // 2. Recalculate Subtotal
        $subtotal = $order->items()->get()->sum('total_price');

        // 3. Recalculate Tax (Assuming 5%)
        $tax = $subtotal * 0.05;

        // 4. Calculate Discount
        $discountAmount = 0;
        if ($validated['discount_type'] === 'percent') {
            $discountAmount = $subtotal * ($validated['discount_value'] / 100);
        } else {
            $discountAmount = (float) $validated['discount_value'];
        }

        // 5. Calculate Extra Charge
        $extraChargeAmount = 0;
        if ($validated['additional_charge_type'] === 'percent') {
            $extraChargeAmount = $subtotal * ($validated['additional_charge_value'] / 100);
        } else {
            $extraChargeAmount = (float) $validated['additional_charge_value'];
        }

        // 6. Final Total
        $total = $subtotal + $tax + $extraChargeAmount - $discountAmount;

        // 7. Prepare update data
        $updateData = [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount_amount' => $discountAmount,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'additional_charge' => $extraChargeAmount,
            'additional_charge_type' => $validated['additional_charge_type'],
            'additional_charge_value' => $validated['additional_charge_value'],
            'total' => max(0, $total),
        ];

        // 8. Update customer details if provided
        if ($request->has('customer_name')) {
            $updateData['customer_name'] = $validated['customer_name'];
        }
        if ($request->has('customer_phone')) {
            $updateData['customer_phone'] = $validated['customer_phone'];
        }

        // 9. Update order type if provided
        if ($request->has('type')) {
            $updateData['type'] = $validated['type'];
        }

        // 10. Handle table changes
        if ($request->has('table_id')) {
            $oldTableId = $order->table_id;
            $newTableId = $validated['table_id'];

            // Free up old table if it exists and is different
            if ($oldTableId && $oldTableId !== $newTableId) {
                $oldTable = Table::find($oldTableId);
                if ($oldTable) {
                    $oldTable->update(['status' => 'available']);
                }
            }

            // Occupy new table if it exists
            if ($newTableId) {
                $newTable = Table::find($newTableId);
                if ($newTable) {
                    $newTable->update(['status' => 'occupied']);
                }
            }

            $updateData['table_id'] = $newTableId;
        }

        $order->update($updateData);

        return redirect()->back()->with('message', 'Order updated successfully.');
    }
}
