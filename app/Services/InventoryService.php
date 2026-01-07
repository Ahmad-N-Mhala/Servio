<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryLog;
use App\Models\IngredientBatch;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct stock for a specific ingredient using FIFO batch logic.
     */
    public function deductStock(Ingredient $ingredient, float $quantity, string $reason, $userId = null)
    {
        if ($quantity <= 0)
            return;

        $remainingQty = $quantity;

        // Fetch valid batches
        $batches = IngredientBatch::where('ingredient_id', (string) $ingredient->id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $batchesUsed = [];

        foreach ($batches as $batch) {
            if ($remainingQty <= 0)
                break;

            // Determine deduction amount for this batch
            $deduct = min($remainingQty, (float) $batch->quantity_remaining);

            // Update batch (Atomic Decrement)
            $batch->decrement('quantity_remaining', $deduct);

            $remainingQty -= $deduct;
            $batchesUsed[] = "{$batch->batch_number} ({$deduct})";
        }

        // Handle case where we ran out of batches but still need to deduct (Negative Stock allowed conceptually or forced)
        // Usually we track negative stock on the Ingredient model.

        // Update Ingredient Cost
        $ingredient->updateCostFromFIFO();

        // Atomically decrement total stock
        // Note: MongoDB atomic operations are preferred
        $ingredient->decrement('current_stock', $quantity);

        // Refresh to get new value for logging
        $ingredient->refresh();

        // Log transaction
        InventoryLog::create([
            'restaurant_id' => $ingredient->restaurant_id,
            'ingredient_id' => $ingredient->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => 'used_in_menu',
            'quantity_change' => -$quantity,
            'new_stock_level' => $ingredient->current_stock,
            'notes' => $reason . ($batchesUsed ? ' | Batches: ' . implode(', ', $batchesUsed) : ''),
        ]);

        // Check for Low Stock Trigger
        $this->checkLowStock($ingredient);
    }

    /**
     * Check if ingredient stock is low and send notification if not already sent.
     */
    public function checkLowStock(Ingredient $ingredient)
    {
        // If reorder level is set (greater than 0)
        if ($ingredient->reorder_level > 0) {

            // Case 1: Stock drops below or equal to reorder level
            if ($ingredient->current_stock <= $ingredient->reorder_level) {

                // Only send if we haven't sent it yet for this cycle
                if (!$ingredient->low_stock_notification_sent) {
                    $this->sendLowStockNotification($ingredient);

                    $ingredient->low_stock_notification_sent = true;
                    $ingredient->save();
                }
            }
            // Case 2: Stock is healthy (restocked)
            elseif ($ingredient->current_stock > $ingredient->reorder_level) {
                // Reset the flag so we can notify again next time it drops
                if ($ingredient->low_stock_notification_sent) {
                    $ingredient->low_stock_notification_sent = false;
                    $ingredient->save();
                }
            }
        }
    }

    protected function sendLowStockNotification(Ingredient $ingredient)
    {
        $userId = $ingredient->notification_user_id;

        // If no specific user set, try finding the owner from the first batch logic, or just owner of Restaurant
        // Ideally we use the configured ID.
        // If no specific user set, try finding the owner from the first batch logic, or just owner of Restaurant
        // Ideally we use the configured ID.
        // if (!$userId) {
        //     // Fallback removed: If null (No notifications needed), we do NOT send to owner.
        // }

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                try {
                    // Prepare data for dynamic template
                    $data = [
                        'ingredient_name_en' => $ingredient->name['en'] ?? '',
                        'ingredient_name_ar' => $ingredient->name['ar'] ?? ($ingredient->name['en'] ?? ''),
                        'current_stock' => $ingredient->current_stock . ' ' . $ingredient->unit,
                        'reorder_level' => $ingredient->reorder_level . ' ' . $ingredient->unit,
                    ];

                    $commService = app(\App\Services\CommunicationService::class);
                    $sent = $commService->sendNotification('inventory_low_stock_warning', $user, $data);

                    if (!$sent) {
                        \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\LowStockWarningMail($ingredient));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send low stock warning for ingredient {$ingredient->id}: " . $e->getMessage());
                }
            }
        }
    }
}
