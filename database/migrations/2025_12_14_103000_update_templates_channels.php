<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('communication_templates', function (Blueprint $table) {
            // Change 'channel' to JSON to support multiple arrays ['sms', 'email']
            // Since we can't easily change type with data, we will drop and re-add or just use a new migration if this was production.
            // But since this is dev, we can update it directly if we assume empty/safe.
            // For safety, let's just make it nullable string or text, then cast to array in model. 
            // Actually, best practice for 'both' is to have 'channels' json column.

            $table->dropColumn('channel');
            $table->json('channels')->nullable(); // ['sms', 'email']
        });
    }

    public function down(): void
    {
        Schema::table('communication_templates', function (Blueprint $table) {
            $table->dropColumn('channels');
            $table->string('channel')->default('email');
        });
    }
};
