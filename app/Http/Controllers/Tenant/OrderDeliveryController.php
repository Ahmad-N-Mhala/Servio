<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderDeliveryController extends Controller
{
    /**
     * Display the list of ready orders for delivery.
     */
    public function index(Request $request)
    {
        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // If self-service only, hide waiter delivery screen
        if ($restaurant->service_type === 'self_service') {
            abort(404);
        }

        $readyOrders = Order::with([
            'items.menuItem' => function ($query) {
                $query->withTrashed();
            },
            'table',
        ])
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'ready')
            ->orderBy('updated_at', 'asc') // Oldest ready first
            ->get();

        return Inertia::render('Waiter/Delivery', [
            'orders' => $readyOrders,
        ]);
    }

    /**
     * Mark an order as served.
     */
    public function markAsServed(Request $request, Order $order)
    {
        // Ensure the order belongs to the current restaurant
        $currentRestaurant = auth()->user()->currentRestaurant();
        if (! $currentRestaurant || $order->restaurant_id !== $currentRestaurant->id) {
            abort(403);
        }

        if ($order->status !== 'ready') {
            return back()->with('error', 'Order is not ready to be served.');
        }

        $newStatus = 'served';
        if ($order->payment_status === 'paid') {
            $newStatus = 'completed';
        }

        $order->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null,
            // We might want to track who served it, but standard order table doesn't have 'served_by' usually.
            // We can assume valid update.
        ]);

        // Update table status if needed?
        // Typically table is 'occupied' until payment.
        // If it was somehow not occupied, ensure it tracks correctly.
        // But usually table status is managed at order creation/payment.

        return back()->with('success', 'Order marked as served.');
    }

    /**
     * Check for new ready orders (JSON API for polling).
     */
    public function checkNewOrders(Request $request)
    {
        // Permission check manually passed or middleware
        if (! $request->user()->can('deliver_orders')) {
            return response()->json(['ids' => []]);
        }

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            return response()->json(['ids' => []]);
        }

        if ($restaurant->service_type === 'self_service') {
            return response()->json(['ids' => []]);
        }

        // Return IDs as strings to ensure JS compatibility
        $ids = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'ready')
            ->pluck('id')
            ->map(fn ($id) => (string) $id);

        return response()->json(['ids' => $ids]);
    }
}
