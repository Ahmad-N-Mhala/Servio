<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monthly_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('category'); // rent, salaries, utilities, supplies, marketing, etc.
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('month'); // Format: YYYY-MM (e.g., 2025-12)
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Index for faster queries
            $table->index(['restaurant_id', 'month']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_expenses');
    }
};
