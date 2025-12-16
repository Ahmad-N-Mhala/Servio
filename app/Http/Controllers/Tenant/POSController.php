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
        $orders = Order::with(['items.menuItem', 'customer', 'table'])
            ->whereIn('status', ['pending', 'completed', 'processing', 'ready', 'served'])
            ->where('payment_status', 'unpaid')
            ->orderBy('created_at', 'desc')
            ->get();

        $tables = Table::all();

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();
        return Inertia::render('POS/Index', [
            'orders' => $orders,
            'tables' => $tables,
        ]);
    }

    public function settle(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,card,online'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            // Update Order
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'status' => $order->status === 'served' ? 'completed' : $order->status,
                'completed_at' => $order->status === 'served' ? now() : (($order->status === 'completed') ? $order->completed_at : null),
            ]);

            // Note: Loyalty points are automatically processed by Order model observer
            // when status changes to 'completed'

            // Update Table if exists
            if ($order->table_id) {
                $table = Table::find($order->table_id);
                if ($table) {
                    $table->update(['status' => 'available']);
                }
            }
        });

        return redirect()->back()->with('message', 'Order settled and table marked available.');
    }
}
