<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryReminderController extends Controller
{
    public function index()
    {
        // Low Stock Ingredients
        $lowStockIngredients = Ingredient::query()
            ->get()
            ->filter(function ($ingredient) {
                $current = (float) ($ingredient->current_stock ?? 0);
                $reorder = (float) ($ingredient->reorder_level ?? 0);
                return $current <= $reorder;
            })
            ->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'current_stock' => (float) ($ingredient->current_stock ?? 0),
                    'reorder_level' => (float) ($ingredient->reorder_level ?? 0),
                    'unit' => $ingredient->unit,
                    'image' => $ingredient->image,
                ];
            })
            ->values();

        // Expiring Batches (within their specific reminder days)
        $expiringBatches = IngredientBatch::query()
            ->with('ingredient')
            ->where('quantity_remaining', '>', 0)
            ->whereNotNull('expiration_date')
            ->orderBy('expiration_date', 'asc')
            ->get()
            ->filter(function ($batch) {
                $reminderDays = (int) ($batch->reminder_days_before ?? 7);
                $thresholdDate = now()->addDays($reminderDays);
                return $batch->expiration_date && $batch->expiration_date <= $thresholdDate;
            })
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'ingredient_name' => $batch->ingredient->name ?? 'Unknown',
                    'batch_number' => $batch->batch_number,
                    'quantity_remaining' => (float) $batch->quantity_remaining,
                    'expiration_date' => $batch->expiration_date ? $batch->expiration_date->format('Y-m-d') : 'N/A',
                    'days_remaining' => $batch->expiration_date ? now()->diffInDays($batch->expiration_date, false) : 0,
                    'unit' => $batch->ingredient->unit ?? '',
                ];
            })
            ->values();

        return Inertia::render('Inventory/Reminders', [
            'lowStockIngredients' => $lowStockIngredients,
            'expiringBatches' => $expiringBatches,
        ]);
    }
}
