<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MongoDB, we need to handle indexes carefully
        $collection = DB::connection('mongodb')->getCollection('orders');

        // 1. Drop the old unique index on just order_number
        try {
            $collection->dropIndex('order_number_1');
        } catch (\Exception $e) {
            // Index might not exist or have a different name
        }

        // 2. Create a new compound unique index: restaurant_id + order_number
        // This allows different restaurants to have the same order number (e.g., ORD-1)
        $collection->createIndex(
            ['restaurant_id' => 1, 'order_number' => 1],
            ['unique' => true, 'name' => 'restaurant_order_unique_idx']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $collection = DB::connection('mongodb')->getCollection('orders');

        try {
            $collection->dropIndex('restaurant_order_unique_idx');
            $collection->createIndex(['order_number' => 1], ['unique' => true, 'name' => 'order_number_1']);
        } catch (\Exception $e) {
        }
    }
};
