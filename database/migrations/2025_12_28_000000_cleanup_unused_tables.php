<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop tables related to Stancl/Tenancy as we are using single-DB multi-tenancy via 'restaurants'
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('domains');

        // Drop potentially unused tables if they exist and aren't used by current logic
        // We keeping standard laravel tables just in case, but tenants/domains are definitely the source of confusion.
    }

    public function down(): void
    {
        // We cannot easily recreate them with data, but we can recreate structure if needed.
        // For now, down does nothing or we could re-create basic tables.
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->json('data')->nullable();
        });

        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->string('tenant_id');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
