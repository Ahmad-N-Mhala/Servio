<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->json('name'); // Multi-language name
            $table->text('description')->nullable();
            $table->integer('points_required');
            $table->string('reward_type'); // discount_percentage, discount_fixed, free_item, cashback
            $table->decimal('discount_value', 10, 2)->nullable(); // For discount types
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete(); // For free_item type
            $table->integer('max_redemptions')->nullable(); // null = unlimited
            $table->integer('redemptions_count')->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};

