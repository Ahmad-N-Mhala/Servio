<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('communication_templates', function (Blueprint $table) {
            // Add timing fields
            $table->string('timing_type')->default('immediately'); // 'immediately', 'before', 'after'
            $table->integer('timing_days')->default(0); // number of days
            $table->time('timing_time')->default('12:00:00'); // specific time of day
        });
    }

    public function down(): void
    {
        Schema::table('communication_templates', function (Blueprint $table) {
            $table->dropColumn(['timing_type', 'timing_days', 'timing_time']);
        });
    }
};
