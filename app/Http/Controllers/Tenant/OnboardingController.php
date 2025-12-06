<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Database\Models\Domain;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class OnboardingController extends Controller
{
    public function show(): Response
    {
        $plans = Plan::where('is_active', true)->get();

        // Get the base domain (skip 127.0.0.1, prefer localhost)
        $baseDomain = collect(config('tenancy.central_domains'))
            ->reject(fn($domain) => in_array($domain, ['127.0.0.1']))
            ->first(fn($domain) => $domain === 'localhost') ?? collect(config('tenancy.central_domains'))->reject(fn($domain) => in_array($domain, ['127.0.0.1', 'localhost']))->first() ?? 'localhost';

        return Inertia::render('Onboarding/Index', [
            'plans' => $plans,
            'baseDomain' => $baseDomain,
        ]);
    }

    public function store(Request $request)
    {
        // Get the base domain (skip 127.0.0.1, prefer localhost)
        $baseDomain = collect(config('tenancy.central_domains'))
            ->reject(fn($domain) => in_array($domain, ['127.0.0.1']))
            ->first(fn($domain) => $domain === 'localhost') ?? collect(config('tenancy.central_domains'))->reject(fn($domain) => in_array($domain, ['127.0.0.1', 'localhost']))->first() ?? 'localhost';

        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                'unique:tenants,identifier',
                function ($attribute, $value, $fail) use ($baseDomain) {
                    $fullDomain = $value . '.' . $baseDomain;
                    if (Domain::where('domain', $fullDomain)->exists()) {
                        $fail('This subdomain is already taken.');
                    }
                },
            ],
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'identifier' => $validated['subdomain'],
            'plan_id' => $plan->id,
            'subscription_status' => 'active', // Set active immediately
            'data' => [
                'owner_name' => $validated['name'],
                'owner_email' => $validated['email'],
                'owner_password' => bcrypt($validated['password']),
                'billing_cycle' => $validated['billing_cycle'],
            ],
        ]);

        $domain = Domain::create([
            'domain' => $validated['subdomain'] . '.' . $baseDomain,
            'tenant_id' => $tenant->id,
        ]);

        // Create the tenant database (using tenant ID as database name)
        $dbName = $tenant->id;
        $dbConnection = config('database.connections.pgsql');
        $host = $dbConnection['host'];
        $port = $dbConnection['port'];
        $username = $dbConnection['username'];
        $password = $dbConnection['password'];

        // Create database using psql
        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -c "CREATE DATABASE \"%s\";" 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg(config('database.connections.pgsql.database')),
            $dbName
        );
        exec($command, $output, $returnCode);

        \Log::info("Database creation command: {$command}");
        \Log::info("Database creation output: " . implode("\n", $output));
        \Log::info("Database creation return code: {$returnCode}");

        // BYPASS STRIPE: Redirect directly to success with tenant_id
        return redirect()->route('onboard.success', ['tenant_id' => $tenant->id]);
    }

    public function success(Request $request)
    {
        // BYPASS STRIPE: Get tenant_id directly from request
        $tenantId = $request->query('tenant_id');

        if (!$tenantId) {
            return redirect()->route('onboard')->with('error', 'Tenant ID missing.');
        }

        try {
            $tenant = Tenant::findOrFail($tenantId);

            // Initialize tenancy if not already done (though we might be on central domain here)
            // For seeding, we need to initialize
            tenancy()->initialize($tenant);

            // Check if already migrated to avoid re-running on refresh
            // A simple check is if the user exists
            $userExists = false;
            try {
                $userExists = \App\Models\User::where('email', $tenant->data['owner_email'])->exists();
            } catch (\Exception $e) {
                // Table might not exist yet
            }

            if (!$userExists) {
                DB::transaction(function () use ($tenant) {
                    $tenant->run(function () use ($tenant) {
                        $this->migrateTenantDatabase();
                        $this->seedTenantDatabase($tenant);
                    });

                    $tenant->update([
                        'subscription_status' => 'active',
                        'subscription_ends_at' => now()->add($tenant->data['billing_cycle'] === 'yearly' ? 1 : 0, 'year')->addMonth(),
                    ]);
                });
            }

            return Inertia::render('Onboarding/Success', [
                'subdomain' => $tenant->identifier,
                'domain' => $tenant->domains->first()->domain,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('onboard')->with('error', 'Setup failed: ' . $e->getMessage());
        }
    }

    protected function migrateTenantDatabase(): void
    {
        Artisan::call('tenants:migrate', [
            '--tenants' => [tenant('id')],
        ]);
    }

    protected function seedTenantDatabase(Tenant $tenant): void
    {
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\RoleSeeder::class,
        ]);

        $user = \App\Models\User::create([
            'name' => $tenant->data['owner_name'],
            'email' => $tenant->data['owner_email'],
            'password' => $tenant->data['owner_password'],
            'email_verified_at' => now(),
        ]);

        $restaurant = \App\Models\Restaurant::create([
            'name' => $tenant->identifier,
            'slug' => Str::slug($tenant->identifier),
            'currency' => 'AED',
            'locale' => app()->getLocale(),
        ]);

        \App\Models\Staff::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $user->assignRole('owner');
    }
}

