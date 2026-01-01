<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations for production scalability.
     * These indexes are CRITICAL for 100+ concurrent users across 10 restaurants.
     */
    public function up(): void
    {
        $indexes = [
            'orders' => [
                ['keys' => ['restaurant_id' => 1, 'status' => 1, 'created_at' => -1], 'name' => 'restaurant_status_created_idx'],
                ['keys' => ['restaurant_id' => 1, 'customer_id' => 1], 'name' => 'restaurant_customer_idx'],
            ],
            'menu_items' => [
                ['keys' => ['restaurant_id' => 1, 'is_available' => 1], 'name' => 'restaurant_available_idx'],
                ['keys' => ['menu_category_id' => 1, 'sort_order' => 1], 'name' => 'category_sort_idx'],
            ],
            'ingredients' => [
                ['keys' => ['restaurant_id' => 1, 'current_stock' => 1], 'name' => 'restaurant_stock_idx'],
                ['keys' => ['restaurant_id' => 1, 'is_active' => 1], 'name' => 'restaurant_active_idx'],
            ],
            'customers' => [
                ['keys' => ['restaurant_id' => 1, 'phone' => 1], 'name' => 'restaurant_phone_idx'],
            ],
            'menu_categories' => [
                ['keys' => ['restaurant_id' => 1, 'is_active' => 1, 'sort_order' => 1], 'name' => 'restaurant_active_sort_idx'],
            ],
            'ingredient_batches' => [
                ['keys' => ['ingredient_id' => 1, 'quantity_remaining' => 1, 'created_at' => 1], 'name' => 'ingredient_fifo_idx'],
            ],
            'restaurant_tables' => [
                ['keys' => ['restaurant_id' => 1, 'status' => 1], 'name' => 'restaurant_table_status_idx'],
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($indexes as $collection => $indexList) {
            foreach ($indexList as $index) {
                try {
                    DB::connection('mongodb')->getCollection($collection)->createIndex(
                        $index['keys'],
                        ['name' => $index['name']]
                    );
                    echo "✅ Created index: {$collection}.{$index['name']}\n";
                    $created++;
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), 'already exists')) {
                        echo "⏭️  Skipped existing index: {$collection}.{$index['name']}\n";
                        $skipped++;
                    } else {
                        echo "⚠️  Error creating {$collection}.{$index['name']}: {$e->getMessage()}\n";
                    }
                }
            }
        }

        echo "\n📊 Summary: {$created} indexes created, {$skipped} already existed\n";
        echo "✅ Production indexes ready!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Indexes can be dropped manually if needed
        echo "ℹ️  To drop indexes, use: db.collection.dropIndex('index_name')\n";
    }
};
