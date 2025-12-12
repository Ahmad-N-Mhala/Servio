<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add balance columns to restaurants table
        Schema::table('restaurants', function (Blueprint $table) {
            $table->integer('sms_balance')->default(0);
            $table->integer('email_balance')->default(0);
        });

        // Create logs table
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'sms' or 'email'
            $table->string('recipient');
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->decimal('cost', 10, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // Create bundles table
        Schema::create('communication_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'sms' or 'email'
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('AED');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_bundles');
        Schema::dropIfExists('communication_logs');

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['sms_balance', 'email_balance']);
        });
    }
};
