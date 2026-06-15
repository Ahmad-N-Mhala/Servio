<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $collection = DB::connection('mongodb')->getCollection('orders');

        // 1. Drop the unique index
        try {
            $collection->dropIndex('restaurant_order_unique_idx');
        } catch (\Exception $e) {
            // Index might not exist
        }

        // 2. Create a non-unique index for performance
        try {
            $collection->createIndex(
                ['restaurant_id' => 1, 'order_number' => 1],
                ['name' => 'restaurant_order_idx']
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $collection = DB::connection('mongodb')->getCollection('orders');

        try {
            $collection->dropIndex('restaurant_order_idx');
        } catch (\Exception $e) {
        }

        $collection->createIndex(
            ['restaurant_id' => 1, 'order_number' => 1],
            ['unique' => true, 'name' => 'restaurant_order_unique_idx']
        );
    }
};
