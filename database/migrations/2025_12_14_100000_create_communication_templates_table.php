<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel'); // 'sms' or 'email'
            $table->string('trigger_event'); // 'registration', 'order_created', 'order_completed', etc.
            $table->string('subject')->nullable(); // For emails
            $table->text('content');
            $table->json('conditions')->nullable(); // e.g. { "min_order_value": 100 }
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('communication_logs', function (Blueprint $table) {
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('communication_logs', function (Blueprint $table) {
            $table->dropForeign(['communication_template_id']);
            $table->dropColumn('communication_template_id');
        });

        Schema::dropIfExists('communication_templates');
    }
};
