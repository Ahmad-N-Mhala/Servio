<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // talabat, noon, deliveroo, careem, etc.
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable(); // Helper text: "Client Secret" or "App Secret"
            $table->string('store_id')->nullable(); // Unique ID from the platform
            $table->string('webhook_secret')->nullable(); // For verifying incoming webhooks
            $table->json('settings')->nullable(); // Any extra config (menus to sync, auto-accept, etc)
            $table->boolean('is_enabled')->default(false);
            $table->boolean('auto_accept_orders')->default(false);
            $table->timestamps();

            $table->unique(['restaurant_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_integrations');
    }
};
