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
        $restaurant = \App\Models\Restaurant::first();

        // Fetch active orders (pending, processing)
        // Ordered by FIFO (First In, First Out)
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with(['items.menuItem', 'customer'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Also fetch recently completed orders (last 10)
        $completedOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'completed')
            ->with(['items.menuItem', 'customer'])
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
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        // Process loyalty points when order is completed
        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $this->loyaltyService->processOrderPoints($order);
        }

        return redirect()->back()->with('message', __('orders.status_updated'));
    }
}
