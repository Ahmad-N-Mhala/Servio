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
            ->whereRaw(['$expr' => ['$lte' => ['$current_stock', '$reorder_level']]])
            ->get()
            ->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'current_stock' => $ingredient->current_stock,
                    'reorder_level' => $ingredient->reorder_level,
                    'unit' => $ingredient->unit,
                    'image' => $ingredient->image, // Assuming there might be an image
                ];
            });

        // Expiring Batches (within their specific reminder days)
        $expiringBatches = IngredientBatch::query()
            ->with('ingredient')
            ->where('quantity_remaining', '>', 0)
            ->whereRaw([
                '$expr' => [
                    '$lte' => [
                        '$expiration_date',
                        [
                            '$add' => [
                                new \MongoDB\BSON\UTCDateTime(now()),
                                ['$multiply' => [['$ifNull' => ['$reminder_days_before', 7]], 86400000]]
                            ]
                        ]
                    ]
                ]
            ])
            ->whereNotNull('expiration_date')
            ->orderBy('expiration_date', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'ingredient_name' => $batch->ingredient->name ?? 'Unknown',
                    'batch_number' => $batch->batch_number,
                    'quantity_remaining' => $batch->quantity_remaining,
                    'expiration_date' => $batch->expiration_date ? $batch->expiration_date->format('Y-m-d') : 'N/A',
                    'days_remaining' => $batch->expiration_date ? now()->diffInDays($batch->expiration_date, false) : 0,
                    'unit' => $batch->ingredient->unit ?? '',
                ];
            });

        return Inertia::render('Inventory/Reminders', [
            'lowStockIngredients' => $lowStockIngredients,
            'expiringBatches' => $expiringBatches,
        ]);
    }
}
