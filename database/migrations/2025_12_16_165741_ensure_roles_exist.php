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
        $roles = array_keys(config('roles.roles', []));

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not want to automatically delete roles as they might be in use.
    }
};
