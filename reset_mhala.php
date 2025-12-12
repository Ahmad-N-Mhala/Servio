<?php

use App\Models\Tenant;
use App\Models\Restaurant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tenantId = 'ahmadtest';

    echo "1. Handling Tenant...\n";
    $tenant = Tenant::find($tenantId);

    // Ensure a Plan exists
    $plan = \App\Models\Plan::first();
    if (!$plan) {
        $plan = \App\Models\Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'stripe_id' => 'price_123',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'currency' => 'USD',
            'features' => []
        ]);
        echo "Created Default Plan: " . $plan->id . "\n";
    }

    // Force delete existing tenant to ensure fresh start
    if ($tenant) {
        echo "Deleting existing tenant...\n";
        $tenant->delete();
    }

    echo "Creating new tenant...\n";
    $tenant = Tenant::create([
        'id' => $tenantId,
        'identifier' => $tenantId,
        'plan_id' => $plan->id,
        'trial_ends_at' => Carbon::now()->addDays(14),
    ]);
    $tenant->tenancy_db_name = 'restaurfy_tenant_' . $tenantId;
    $tenant->save();
    $tenant->domains()->create([
        'domain' => $tenantId . '.' . 'localhost'
    ]);

    // Manual DB Creation
    $dbName = 'restaurfy_tenant_' . $tenantId;
    echo "Creating Tenant Database: $dbName...\n";
    try {
        DB::connection('pgsql')->statement("CREATE DATABASE \"$dbName\"");
        echo "Database created.\n";
    } catch (\Exception $e) {
        echo "Database creation skipped (might exist): " . $e->getMessage() . "\n";
    }

    // START FIX: Manually switch default connection to tenant
    config(['database.default' => 'tenant']);

    // Manually force the database matching the standard pattern if not set
    $dbName = 'restaurfy_tenant_' . $tenant->id;
    echo "Forcing Tenant DB Name: $dbName\n";
    config(['database.connections.tenant.database' => $dbName]);

    DB::purge('tenant'); // Ensure we get a fresh connection
    DB::reconnect('tenant');
    // END FIX

    echo "Migrating Tenant Database DIRECTLY...\n";
    $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true
    ]);
    echo "Migration Output:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";

    echo "Initializing tenant context...\n";
    tenancy()->initialize($tenant);

    // Debug Tenant Data
    echo "Tenant Object keys: " . implode(',', array_keys($tenant->toArray())) . "\n";
    echo "Tenant ID: " . $tenant->id . "\n";
    echo "Tenancy DB Name (Prop): " . $tenant->tenancy_db_name . "\n";

    echo "DB Connection: " . DB::connection()->getName() . "\n";
    echo "DB Database: " . DB::connection()->getDatabaseName() . "\n";

    echo "2. Wiping Tenant Database Data (Skipped for fresh tenant)...\n";
    // Disable foreign key checks
    // DB::statement('SET session_replication_role = "replica";');

    // Truncate all relevant tables
    // User::truncate();
    // Restaurant::truncate();
    // DB::table('model_has_roles')->truncate();
    // Add other tables if necessary, but these are the core auth ones

    // DB::statement('SET session_replication_role = "origin";');

    echo "3. Seeding Roles...\n";
    $roles = ['owner', 'manager', 'waiter', 'chef'];
    foreach ($roles as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    echo "4. Creating 'Mhala' Restaurant...\n";
    $restaurant = Restaurant::create([
        'name' => 'Mhala',
        'slug' => 'mhala',
        'email' => 'contact@mhala.com',
        'phone' => '0501234567',
        'address' => 'Mhala Restaurant Location',
        'currency' => 'AED',
    ]);

    echo "5. Creating Manager User...\n";
    $email = 'manager@mhala.com';
    $password = 'password123';

    $user = User::create([
        'name' => 'Mhala Manager',
        'email' => $email,
        'password' => $password, // Removed bcrypt() because of 'hashed' cast in User model
        'restaurant_id' => $restaurant->id,
    ]);

    // Assign Role
    $user->assignRole('manager');

    echo "\n=============================================\n";
    echo "✅ FULL RESET & SETUP COMPLETE!\n";
    echo "---------------------------------------------\n";
    echo "URL: http://ahmadtest.localhost:8000/en/login\n";
    echo "---------------------------------------------\n";
    echo "Restaurant: " . $restaurant->name . "\n";
    echo "---------------------------------------------\n";
    echo "NEW CREDENTIALS:\n";
    echo "Email:    " . $email . "\n";
    echo "Password: " . $password . "\n";
    echo "Role:     manager\n";
    echo "=============================================\n";

    // Clear caches to ensure no session artifacts remain
    echo "Clearing application caches...\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "Caches cleared.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
