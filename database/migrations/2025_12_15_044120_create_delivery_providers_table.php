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
        Schema::create('delivery_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Talabat", "Noon Food", "Careem NOW"
            $table->string('slug')->unique(); // e.g., "talabat", "noon", "careem"
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable(); // URL or path to logo
            $table->string('api_documentation_url')->nullable();

            // Configuration requirements
            $table->boolean('requires_api_key')->default(true);
            $table->boolean('requires_api_secret')->default(true);
            $table->boolean('requires_store_id')->default(true);
            $table->boolean('requires_webhook_secret')->default(false);
            $table->json('configuration_fields')->nullable(); // Additional custom fields

            // Status and ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_providers');
    }
};
