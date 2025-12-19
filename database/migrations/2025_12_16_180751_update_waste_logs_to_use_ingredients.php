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
        Schema::table('waste_logs', function (Blueprint $table) {
            $table->foreignId('ingredient_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('menu_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_logs', function (Blueprint $table) {
            $table->dropForeign(['ingredient_id']);
            $table->dropColumn('ingredient_id');
            // We cannot easily revert nullability without ensuring no nulls exist
        });
    }
};
