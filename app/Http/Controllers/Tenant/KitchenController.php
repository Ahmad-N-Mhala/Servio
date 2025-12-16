<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KitchenController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {
    }

    public function index(): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        // Fetch active orders (pending, processing)
        // Ordered by FIFO (First In, First Out)
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'processing', 'served'])
            ->with(['items.menuItem', 'customer', 'table'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Also fetch recently completed orders (last 10)
        $completedOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'completed')
            ->with(['items.menuItem', 'customer', 'table'])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Kitchen/Index', [
            'orders' => $orders,
            'completedOrders' => $completedOrders,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled,served'],
        ]);

        $oldStatus = $order->status;
        $status = $validated['status'];

        // Auto-complete if served and paid
        if ($status === 'served' && $order->payment_status === 'paid') {
            $status = 'completed';
        }

        $order->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);

        // Note: Loyalty points are automatically processed by Order model observer
        // when status changes to 'completed'

        return redirect()->back()->with('message', __('orders.status_updated'));
    }
}
