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
        Schema::create('plan_interests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('restaurant_name');
            $table->string('plan_id')->nullable();
            $table->string('plan_name')->nullable();
            $table->text('message')->nullable();

            // Status: pending, not_started, in_progress, subscribed, not_subscribed
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_interests');
    }
};
