<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delivery_integrations', function (Blueprint $table) {
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_integrations', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'client_secret', 'username', 'password']);
        });
    }
};
