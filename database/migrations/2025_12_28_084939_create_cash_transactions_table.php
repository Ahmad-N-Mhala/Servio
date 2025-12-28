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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('cash_register_id');
            $table->string('restaurant_id');
            $table->string('user_id');
            $table->string('order_id')->nullable(); // If related to an order
            $table->enum('type', ['sale', 'withdrawal', 'deposit', 'opening', 'closing']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('cash_register_id');
            $table->index('restaurant_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
