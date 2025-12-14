<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_provider')->nullable()->after('type'); // e.g. noon, talabat
            $table->string('delivery_order_id')->nullable()->after('delivery_provider'); // External ID
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_provider', 'delivery_order_id']);
        });
    }
};
