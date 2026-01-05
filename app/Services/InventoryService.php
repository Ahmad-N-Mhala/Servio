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

            // Update batch
            $batch->quantity_remaining = (float) $batch->quantity_remaining - $deduct;
            $batch->save();

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
    }
}
