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
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        \Log::info('POS Index - Restaurant ID: ' . $restaurant->id);

        $orders = Order::with(['items.menuItem', 'customer', 'table'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'completed', 'processing', 'ready', 'served'])
            ->where(function ($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhereNull('payment_status');  // Include orders without payment_status field
            })
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('POS Index - Orders count: ' . $orders->count());
        \Log::info('POS Index - Total orders for restaurant: ' . Order::where('restaurant_id', $restaurant->id)->count());

        $tables = Table::where('restaurant_id', $restaurant->id)->get();

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
            'currentRegister' => $currentRegister,
            'currentBalance' => $currentRegister ? $currentRegister->getCurrentBalance() : 0,
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
            'items.*.id' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'discount_type' => ['required', 'string', 'in:fixed,percent'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'additional_charge_type' => ['required', 'string', 'in:fixed,percent'],
            'additional_charge_value' => ['required', 'numeric', 'min:0'],
        ]);

        // 1. Update Items
        if ($request->has('items')) {
            foreach ($validated['items'] as $itemData) {
                // Find via relation to ensure it belongs to order
                $orderItem = $order->items()->where('_id', $itemData['id'])->first();
                if ($orderItem) {
                    if ($itemData['quantity'] <= 0) {
                        $orderItem->delete();
                    } else {
                        // Use unit_price from item (or fallback to menu item price if item has 0 but we want to be safe, ideally item has it)
                        // If item unit_price is 0 (old bug), we try to get from menu item relation if loaded? 
                        // But relation might not be loaded on $orderItem here. Best to rely on stored unit_price or fetch fresh?
                        // Let's rely on stored. If stored is 0, total_price is 0.
                        $price = $orderItem->unit_price;
                        if ($price <= 0 && $orderItem->menuItem) {
                            $price = $orderItem->menuItem->price;
                            $orderItem->unit_price = $price; // fix it
                        }

                        $orderItem->update([
                            'quantity' => $itemData['quantity'],
                            'total_price' => $itemData['quantity'] * $price,
                            'unit_price' => $price
                        ]);
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

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount_amount' => $discountAmount,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'additional_charge' => $extraChargeAmount,
            'additional_charge_type' => $validated['additional_charge_type'],
            'additional_charge_value' => $validated['additional_charge_value'],
            'total' => max(0, $total),
        ]);

        return redirect()->back()->with('message', 'Order updated successfully.');
    }
}
