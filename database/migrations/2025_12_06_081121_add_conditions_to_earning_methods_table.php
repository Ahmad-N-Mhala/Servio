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
        Schema::table('earning_methods', function (Blueprint $table) {
            $table->decimal('min_spent', 10, 2)->nullable()->after('points');
            $table->integer('max_points')->nullable()->after('min_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('earning_methods', function (Blueprint $table) {
            $table->dropColumn(['min_spent', 'max_points']);
        });
    }
};
