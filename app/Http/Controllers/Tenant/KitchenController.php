<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use App\Events\OrderUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KitchenController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService,
        protected \App\Services\InventoryService $inventoryService
    ) {
    }

    public function index(): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Fetch active orders (pending, processing, ready, served)
        // Ordered by FIFO (First In, First Out)
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'processing', 'ready', 'served'])
            ->where(function ($query) {
                // Show order if source is NOT 'qr' OR if source is 'qr' AND payment_status is 'paid'
                $query->where('source', '!=', 'qr')
                    ->orWhere('payment_status', 'paid');
            })
            ->with([
                'items.menuItem' => function ($query) {
                    $query->withTrashed();
                },
                'customer',
                'table'
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        // Also fetch recently completed orders (last 10)
        $completedOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'completed')
            ->with([
                'items.menuItem' => function ($query) {
                    $query->withTrashed();
                },
                'customer',
                'table'
            ])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Kitchen/Index', [
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'restaurant_id' => $restaurant->id,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled,served,ready'],
            'cancellation_reason' => ['nullable', 'string', 'required_if:status,cancelled'],
        ]);

        $oldStatus = $order->status;
        $status = $validated['status'];
        $cancellationReason = $validated['cancellation_reason'] ?? null;

        // Auto-complete if served and paid
        if ($status === 'served' && $order->payment_status === 'paid') {
            $status = 'completed';
        }

        // ====== INVENTORY DEDUCTION LOGIC (Consistent with OrderController) ======
        // Deduct Inventory when status moves from 'pending' to any active cooking/served state
        if ($oldStatus === 'pending' && in_array($status, ['processing', 'completed', 'served', 'ready'])) {
            $order->load(['items.menuItem.ingredients', 'items.menuItem.bundles.childItem', 'items.menuItem.extras']);

            foreach ($order->items as $orderItem) {
                if ($orderItem->menuItem) {
                    // Deduct Main Item and potential Bundles
                    $this->processInventoryForMenuItem($orderItem->menuItem, $orderItem->quantity, $order);
                }
            }
        }
        // ====== END INVENTORY DEDUCTION ======

        $updateData = [
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ];

        if ($status === 'cancelled' && $cancellationReason) {
            $updateData['notes'] = ($order->notes ? $order->notes . "\n" : "") . "Cancelled by Kitchen: " . $cancellationReason;
        }

        $order->update($updateData);

        // Note: Loyalty points are automatically processed by Order model observer
        // when status changes to 'completed'

        // Broadcast order status changed event for real-time updates
        // Broadcast order status changed event for real-time updates
        broadcast(new OrderUpdated($order->load([
            'items.menuItem' => function ($q) {
                $q->withTrashed();
            },
            'customer',
            'table'
        ]), 'status_changed'))->toOthers();

        return redirect()->back()->with('message', __('orders.status_updated'));
    }

    /**
     * Helper to process inventory deduction recursively via Service
     * (Duplicated from OrderController to allow independent processing if needed, but ensures consistency)
     */
    private function processInventoryForMenuItem($menuItem, $qty, $order)
    {
        if (!$menuItem)
            return;

        $itemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;

        // A. If Meal, process bundles
        if (($menuItem->type ?? 'item') === 'meal') {
            if (!$menuItem->relationLoaded('bundles')) {
                $menuItem->load(['bundles.childItem']);
            }

            foreach ($menuItem->bundles as $bundle) {
                if ($bundle->childItem) {
                    $this->processInventoryForMenuItem(
                        $bundle->childItem,
                        $qty * ($bundle->quantity ?? 1),
                        $order
                    );
                }
            }
        }

        // B. Process Recipe (Standard or Legacy)
        $recipe = $menuItem->recipe ?? [];
        if (!empty($recipe)) {
            foreach ($recipe as $component) {
                $ingId = $component['ingredient_id'] ?? null;
                $needed = (float) ($component['quantity'] ?? 0);

                if ($ingId && $needed > 0) {
                    $ingredient = \App\Models\Ingredient::find($ingId);
                    if ($ingredient) {
                        $this->inventoryService->deductStock(
                            $ingredient,
                            $needed * $qty,
                            "Item '{$itemName}' Kitchen Order #{$order->order_number}",
                            auth()->id()
                        );
                    }
                }
            }
        } elseif ($menuItem->relationLoaded('ingredients') && $menuItem->ingredients->isNotEmpty()) {
            // Fallback to legacy Pivot
            foreach ($menuItem->ingredients as $ingredient) {
                $pivotQty = $ingredient->pivot->quantity ?? 0;
                if ($pivotQty > 0) {
                    $this->inventoryService->deductStock(
                        $ingredient,
                        $pivotQty * $qty,
                        "Item '{$itemName}' Kitchen Order #{$order->order_number}",
                        auth()->id()
                    );
                }
            }
        }
    }
}
