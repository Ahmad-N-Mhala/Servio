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
            $table->string('logo_url')->nullable(); // Store file path or URL
            $table->string('api_documentation_url')->nullable();

            // Configuration requirements (What credentials we need from the RESTAURANT)
            $table->boolean('requires_api_key')->default(false);
            $table->boolean('requires_api_secret')->default(false);
            $table->boolean('requires_client_id')->default(false); // OAuth
            $table->boolean('requires_client_secret')->default(false); // OAuth
            $table->boolean('requires_username')->default(false); // Legacy/Basic
            $table->boolean('requires_password')->default(false); // Legacy/Basic
            $table->boolean('requires_store_id')->default(true);
            $table->boolean('requires_webhook_secret')->default(false);
            $table->json('configuration_fields')->nullable(); // Additional custom fields definition

            // Integration Settings (How WE connect to THEM or THEY connect to US)
            $table->string('webhook_url_template')->nullable(); // e.g. https://api.servio.com/webhooks/delivery/{provider}/{store_id}
            $table->json('supported_webhook_events')->nullable(); // ['order.created', 'order.cancelled', etc.]
            $table->json('api_settings')->nullable(); // Internal setting for Auth type, API Base URL, etc.

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
