<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OrderStatusScreenController extends Controller
{
    public function index()
    {
        $this->checkServiceType();
        return Inertia::render('Orders/StatusScreen', [
            'orders' => $this->getOrders(),
            'canManage' => Auth::check() && Auth::user()->can('manage_order_status_screen'),
        ]);
    }

    public function manage()
    {
        $this->checkServiceType();
        return Inertia::render('Orders/StatusManager', [
            'orders' => $this->getOrders(),
        ]);
    }

    public function updateState(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'action' => 'required|in:mark_ready,mark_completed'
        ]);

        $order = Order::findOrFail($request->order_id);

        // Security check: belong to current restaurant
        if ($order->restaurant_id != session('active_restaurant_id')) {
            abort(403);
        }

        if ($request->action === 'mark_ready') {
            $order->update(['status' => 'ready']);
        } elseif ($request->action === 'mark_completed') {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return redirect()->back();
    }

    private function checkServiceType()
    {
        $restaurantId = session('active_restaurant_id');
        if (!$restaurantId) {
            abort(403, 'No active restaurant session');
        }

        $restaurant = \App\Models\Restaurant::find($restaurantId);
        // If table service only, hide the status screen/manager
        if ($restaurant && $restaurant->service_type === 'table_service') {
            abort(404);
        }
    }

    public function poll()
    {
        $this->checkServiceType();
        return response()->json([
            'orders' => $this->getOrders(),
        ]);
    }

    private function getOrders()
    {
        // Statuses used in Kitchen View: 'pending', 'processing', 'ready', 'served'
        // We map 'pending' + 'processing' -> Preparing
        // We map 'ready' -> Ready
        $activeOrders = Order::query()
            ->whereIn('status', ['pending', 'processing', 'preparing', 'cooking', 'ready', 'ready_for_pickup'])
            ->where('created_at', '>=', now()->subDay()) // Optimization: only recent orders
            ->select('id', 'order_number', 'transaction_number', 'status', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return [
            'preparing' => $activeOrders->whereIn('status', ['pending', 'processing', 'preparing', 'cooking'])->values(),
            'ready' => $activeOrders->whereIn('status', ['ready', 'ready_for_pickup'])->values(),
        ];
    }
}
