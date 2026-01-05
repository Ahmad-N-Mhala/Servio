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
        Schema::create('landing_modules', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // {en: "...", ar: "..."}
            $table->json('description'); // {en: "...", ar: "..."}
            $table->string('icon')->nullable(); // stored path or class
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_modules');
    }
};
