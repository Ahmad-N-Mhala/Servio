<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menu Items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('restaurant_id');
            $table->index('menu_category_id');
            $table->index('is_available');
            $table->index(['restaurant_id', 'is_available']);
        });

        // Order Items
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('menu_item_id');
        });

        // Staff
        Schema::table('staff', function (Blueprint $table) {
            $table->index('restaurant_id');
            $table->index('user_id');
            $table->index('is_active');
            $table->index(['restaurant_id', 'is_active']);
        });

        // Orders - Composite indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['customer_id', 'created_at']);
        });

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['restaurant_id', 'phone']);
        });

        // Loyalty
        Schema::table('loyalty_points', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'created_at']);
        });

        Schema::table('loyalty_points', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'phone']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'created_at']);
            $table->dropIndex(['restaurant_id', 'created_at']);
            $table->dropIndex(['restaurant_id', 'status']);
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'is_active']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['restaurant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['menu_item_id']);
            $table->dropIndex(['order_id']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'is_available']);
            $table->dropIndex(['is_available']);
            $table->dropIndex(['menu_category_id']);
            $table->dropIndex(['restaurant_id']);
        });
    }
};
