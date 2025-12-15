<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->date('log_date');
            $table->integer('added_amount')->default(0); // Items produced/stocked
            $table->integer('waste_amount')->default(0); // Items remaining/wasted
            $table->decimal('cost_per_unit', 10, 2); // Snapshot of price/cost
            $table->decimal('total_loss', 10, 2); // waste_amount * cost_per_unit
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};
