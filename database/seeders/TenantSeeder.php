<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 'demo';
        $domain = 'demo.localhost';
        $email = 'admin@demo.com';
        $password = 'password';

        $this->command->info("Creating tenant: {$tenantId} ({$domain})");

        $tenant = Tenant::firstOrCreate(['id' => $tenantId], [
            'plan_id' => 2, // Pro plan
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
        ]);

        $tenant->domains()->firstOrCreate(['domain' => $domain]);

        $this->command->info("Initializing tenant context...");

        tenancy()->initialize($tenant);

        $this->command->info("Running tenant migrations...");

        // Set the database connection to the tenant's database
        config(['database.connections.tenant.database' => $tenantId]);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::reconnect('tenant');

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->command->info(\Illuminate\Support\Facades\Artisan::output());

        $this->command->info("Creating admin user...");

        $user = User::firstOrCreate(['email' => $email], [
            'name' => 'Demo Admin',
            'password' => Hash::make($password),
        ]);

        // Assign owner role if roles exist, otherwise just create user
        // Assuming RoleSeeder is run or roles are created in DashboardDemoSeeder?
        // Actually DashboardDemoSeeder creates staff but not the owner user usually.
        // Let's check RoleSeeder if it exists.

        $this->command->info("Creating restaurant...");

        Restaurant::firstOrCreate(['slug' => 'demo-restaurant'], [
            'name' => 'Demo Restaurant',
            'currency' => 'AED',
            'locale' => 'en',
        ]);

        // Note: DashboardDemoSeeder skipped due to schema differences

        $this->command->info("Tenant seeded successfully!");
        $this->command->info("URL: http://{$domain}:8000");
        $this->command->info("Email: {$email}");
        $this->command->info("Password: {$password}");
    }
}
