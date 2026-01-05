<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_item_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('child_menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_bundles');
    }
};
