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
        $validated = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:255'],
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'earning_method_type' => ['required', 'in:order_total,visit'],
            'earning_points' => ['required', 'integer', 'min:1'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        DB::beginTransaction();
        try {
            // 1. Create User (Central)
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($validated['password']),
                'email_verified_at' => now(), // Auto-verify for now
            ]);

            // Assign Owner Role
            $user->assignRole('owner');


            // 2. Create Restaurant (Central)
            $restaurant = \App\Models\Restaurant::create([
                'name' => $validated['restaurant_name'],
                'slug' => Str::slug($validated['restaurant_name']) . '-' . Str::random(6),
                'currency' => 'AED',
                'locale' => 'en', // Default locale
            ]);

            // 3. Link User to Restaurant via Pivot
            DB::table('restaurant_user')->insert([
                'restaurant_id' => $restaurant->id,
                'email' => $user->email,
                'role' => 'owner',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create Staff Record
            // Note: Staff model uses restaurant_id field, make sure it is provided
            // Temporarily switching off Global Scope if needed, or manually setting ID
            $staff = new \App\Models\Staff();
            $staff->restaurant_id = $restaurant->id;
            $staff->user_id = $user->id;
            $staff->role = 'owner';
            $staff->is_active = true;
            $staff->joined_at = now();
            $staff->joined_at = now();
            $staff->save();

            // 5. Create Default Earning Method
            \App\Models\EarningMethod::create([
                'restaurant_id' => $restaurant->id,
                'name' => ['en' => 'Standard Loyalty', 'ar' => 'نقاط الولاء'],
                'description' => 'Default earning method set during onboarding.',
                'type' => $validated['earning_method_type'],
                'points' => $validated['earning_points'],
                'currency_amount' => 1,
                'is_active' => true,
            ]);

            DB::commit();

            // Auto-login the user
            \Illuminate\Support\Facades\Auth::login($user);

            // Set active restaurant session
            session(['active_restaurant_id' => $restaurant->id]);

            // Redirect to dashboard
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Onboarding failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Onboarding failed. Please try again.']);
        }
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

