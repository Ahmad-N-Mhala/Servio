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
        DB::table('ingredient_batches')
            ->where('batch_number', 'INITIAL-MIGRATION')
            ->update(['batch_number' => 'Batch 1']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ingredient_batches')
            ->where('batch_number', 'Batch 1')
            ->update(['batch_number' => 'INITIAL-MIGRATION']);
    }
};
