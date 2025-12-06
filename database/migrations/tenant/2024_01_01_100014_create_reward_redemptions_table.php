<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points_used');
            $table->string('status')->default('pending'); // pending, applied, expired, cancelled
            $table->string('code')->unique()->nullable(); // Redemption code
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // Store discount details, etc.
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};

