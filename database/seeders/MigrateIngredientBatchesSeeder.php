<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateIngredientBatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = \App\Models\Ingredient::all();

        foreach ($ingredients as $ingredient) {
            if ($ingredient->current_stock > 0) {
                // Check if batches already exist to avoid duplication if re-run
                if ($ingredient->batches()->count() === 0) {
                    \App\Models\IngredientBatch::create([
                        'ingredient_id' => $ingredient->id,
                        'batch_number' => 'INITIAL-MIGRATION',
                        'quantity_initial' => $ingredient->current_stock,
                        'quantity_remaining' => $ingredient->current_stock,
                        'cost_per_unit' => $ingredient->cost,
                        'expiration_date' => null, // or calculate based on assumption
                    ]);
                }
            }
        }
    }
}
