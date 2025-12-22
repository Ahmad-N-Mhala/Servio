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
            'cancellation_reason' => ['nullable', 'string', 'required_if:status,cancelled'],
        ]);

        $oldStatus = $order->status;
        $status = $validated['status'];
        $cancellationReason = $validated['cancellation_reason'] ?? null;

        // Auto-complete if served and paid
        if ($status === 'served' && $order->payment_status === 'paid') {
            $status = 'completed';
        }

        // ====== INVENTORY DEDUCTION LOGIC (Duplicated from OrderController to ensure consistency) ======
        // Deduct Inventory when status moves from 'pending' to any active cooking/served state
        if ($oldStatus === 'pending' && in_array($status, ['processing', 'completed', 'served'])) {
            $order->load(['items.menuItem.ingredients']);

            foreach ($order->items as $item) {
                $menuItem = $item->menuItem;
                if ($menuItem) {
                    $recipe = $menuItem->recipe ?? [];
                    $hasRecipe = !empty($recipe);

                    if ($hasRecipe) {
                        // NEW LOGIC: Use embedded recipe
                        foreach ($recipe as $component) {
                            $ingId = $component['ingredient_id'] ?? null;
                            $qtyNeeded = (float) ($component['quantity'] ?? 0);
                            if ($ingId && $qtyNeeded > 0) {
                                $ingredient = \App\Models\Ingredient::find($ingId);
                                if ($ingredient) {
                                    $totalNeeded = $qtyNeeded * $item->quantity;

                                    $remainingQty = $totalNeeded;
                                    $batches = \App\Models\IngredientBatch::where('ingredient_id', (string) $ingredient->id)
                                        ->where('quantity_remaining', '>', 0)
                                        ->orderBy('created_at', 'asc')->get();

                                    $batchesUsed = [];
                                    foreach ($batches as $batch) {
                                        if ($remainingQty <= 0)
                                            break;

                                        $deduct = min($remainingQty, (float) $batch->quantity_remaining);
                                        $batch->quantity_remaining = (float) $batch->quantity_remaining - $deduct;
                                        $batch->save();

                                        $remainingQty -= $deduct;
                                        $batchesUsed[] = "{$batch->batch_number} ({$deduct})";
                                    }

                                    $ingredient->updateCostFromFIFO();
                                    $ingredient = $ingredient->fresh();
                                    $ingredient->current_stock = (float) $ingredient->current_stock - $totalNeeded;
                                    $ingredient->save();

                                    \App\Models\InventoryLog::create([
                                        'restaurant_id' => $order->restaurant_id,
                                        'ingredient_id' => $ingredient->id,
                                        'user_id' => auth()->id(),
                                        'action' => 'used_in_menu',
                                        'quantity_change' => -$totalNeeded,
                                        'new_stock_level' => $ingredient->current_stock,
                                        'notes' => 'Kitchen Order #' . $order->order_number . ' | Batches: ' . implode(', ', $batchesUsed),
                                    ]);
                                }
                            }
                        }
                    } elseif ($menuItem->ingredients->isNotEmpty()) {
                        foreach ($menuItem->ingredients as $ingredient) {
                            $pivotQty = 0;
                            if ($ingredient->pivot && isset($ingredient->pivot->quantity)) {
                                $pivotQty = $ingredient->pivot->quantity;
                            } else {
                                $pivotRecord = \App\Models\MenuItemIngredient::where('menu_item_id', $menuItem->id)
                                    ->where('ingredient_id', $ingredient->id)->first();
                                if ($pivotRecord)
                                    $pivotQty = $pivotRecord->quantity;
                            }

                            if ($pivotQty <= 0)
                                continue;

                            $neededQty = $pivotQty * $item->quantity;
                            $remainingQty = $neededQty;

                            $batches = \App\Models\IngredientBatch::where('ingredient_id', (string) $ingredient->id)
                                ->where('quantity_remaining', '>', 0)
                                ->orderBy('created_at', 'asc')->get();

                            $batchesUsed = [];
                            foreach ($batches as $batch) {
                                if ($remainingQty <= 0)
                                    break;

                                $deduct = min($remainingQty, (float) $batch->quantity_remaining);
                                $batch->quantity_remaining = (float) $batch->quantity_remaining - $deduct;
                                $batch->save();

                                $remainingQty -= $deduct;
                                $batchesUsed[] = "{$batch->batch_number} ({$deduct})";
                            }

                            $ingredient->updateCostFromFIFO();
                            $ingredient = $ingredient->fresh();
                            $ingredient->current_stock = (float) $ingredient->current_stock - $neededQty;
                            $ingredient->save();

                            \App\Models\InventoryLog::create([
                                'restaurant_id' => $order->restaurant_id,
                                'ingredient_id' => $ingredient->id,
                                'user_id' => auth()->id(),
                                'action' => 'used_in_menu',
                                'quantity_change' => -$neededQty,
                                'new_stock_level' => $ingredient->current_stock,
                                'notes' => 'Kitchen Order #' . $order->order_number . ' | Batches: ' . implode(', ', $batchesUsed),
                            ]);
                        }
                    }
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

        return redirect()->back()->with('message', __('orders.status_updated'));
    }
}
