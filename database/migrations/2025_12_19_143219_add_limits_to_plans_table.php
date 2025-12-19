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
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_restaurants')->default(1)->after('features');
            $table->integer('max_users')->default(5)->after('max_restaurants');
            $table->integer('max_orders_per_month')->nullable()->after('max_users');
            $table->boolean('is_featured')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_restaurants', 'max_users', 'max_orders_per_month', 'is_featured']);
        });
    }
};
