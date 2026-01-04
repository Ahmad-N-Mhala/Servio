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
        $plans = Plan::where('is_active', true)->orderBy('price_monthly')->get();

        // Get the base domain (skip 127.0.0.1, prefer localhost)
        $baseDomain = collect(config('tenancy.central_domains'))
            ->reject(fn($domain) => in_array($domain, ['127.0.0.1']))
            ->first(fn($domain) => $domain === 'localhost') ?? collect(config('tenancy.central_domains'))->reject(fn($domain) => in_array($domain, ['127.0.0.1', 'localhost']))->first() ?? 'localhost';

        // Auto-detect country from IP
        $defaultCountry = $this->getCountryFromIp(request()->ip());

        $countries = \App\Models\Country::all();

        return Inertia::render('Onboarding/Index', [
            'plans' => $plans,
            'baseDomain' => $baseDomain,
            'availableFeatures' => config('features'),
            'defaultCountry' => $defaultCountry,
            'countries' => $countries,
        ]);
    }

    /**
     * Attempt to determine country name from IP address.
     * Defaults to 'United Arab Emirates' if detection fails or is local.
     */
    private function getCountryFromIp(?string $ip): string
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'])) {
            return 'United Arab Emirates';
        }

        try {
            // Use a public free API for demonstration (ip-api.com)
            // In production, use a dedicated package or paid service
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['country'])) {
                    return $data['country'];
                }
            }
        } catch (\Exception $e) {
            // Be silent on failure
        }

        return 'United Arab Emirates';
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
            // Location Details
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'], // Street Name
            'zip_code' => ['nullable', 'string', 'max:20'],
            'google_map_location' => ['nullable', 'url', 'max:500'],
            'service_type' => ['required', 'in:self_service,table_service,both'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        // Check if plan is free (ensure strictly numeric zero)
        $planPrice = $validated['billing_cycle'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        // Treat as free if price is 0 or less (e.g. 0.00) OR FORCE BYPASS
        $isFree = true; // (float) $planPrice <= 0; // Forces bypass of payment for all plans during development

        // Determine if we can use transactions (Database Transactions)
        // FORCE DISABLE TRANSACTIONS for standalone MongoDB support
        $useTransactions = false;

        /* 
        // Transaction logic disabled to support standalone MongoDB
        try {
            DB::beginTransaction();
        } catch (\Exception $e) {
             // ...
        }
        */

        // Lookup selected country to get currency
        $selectedCountry = \App\Models\Country::where('name', $validated['country'])->first();
        $currency = $selectedCountry ? $selectedCountry->currency : 'AED';

        try {
            // 1. Create User (Central)
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($validated['password']),
                'password_set_at' => now(), // User is actively setting their password
                'email_verified_at' => now(), // Auto-verify for now
            ]);

            // Assign Owner Role
            $user->assignRole('owner');

            // 2. Create Restaurant (Central)
            $restaurant = \App\Models\Restaurant::create([
                'name' => $validated['restaurant_name'],
                'slug' => Str::slug($validated['restaurant_name']) . '-' . Str::random(6),
                'currency' => $currency, // Dynamic currency based on country
                'locale' => 'en', // Default locale
                'country' => $validated['country'],
                'state' => $validated['state'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'zip_code' => $validated['zip_code'] ?? null,
                'google_map_location' => $validated['google_map_location'] ?? null,
                'email' => $validated['email'], // Reuse owner email for restaurant
                'phone' => $validated['phone'], // Reuse owner phone for restaurant
                'service_type' => $validated['service_type'],
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
            $staff = new \App\Models\Staff();
            $staff->restaurant_id = $restaurant->id;
            $staff->user_id = $user->id;
            $staff->role = 'owner';
            $staff->is_active = true;
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

            // 6. Create Subscription
            // For free plans, create active subscription immediately and bypass payment
            \App\Models\Subscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => $isFree ? 'active' : 'pending', // Active for free, pending for paid
                'billing_cycle' => $validated['billing_cycle'],
                'starts_at' => now(),
                'ends_at' => $validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth(),
                // Generate a dummy ID to satisfy unique index constraint on MongoDB where nulls collide
                'stripe_subscription_id' => 'sub_free_' . Str::random(16),
            ]);

            if ($useTransactions) {
                try {
                    DB::commit();
                } catch (\Exception $e) {
                    // If commit fails due to transaction support, ignore it as operations likely ran in standalone mode
                    if (!Str::contains($e->getMessage(), ['replica set member', 'mongos', 'Transaction numbers'])) {
                        throw $e;
                    }
                }
            }

            // Auto-login the user
            \Illuminate\Support\Facades\Auth::login($user);

            // Trigger Registration Notification
            $commService = app(\App\Services\CommunicationService::class);
            $commService->sendNotification('user_registered', $user, [
                'restaurant_name' => $restaurant->name,
                'link' => route('login'),
            ]);

            // Set active restaurant session
            session(['active_restaurant_id' => $restaurant->id]);

            // Redirect Logic
            if ($isFree) {
                // CASE 1: FREE PLAN -> No Payment Required
                return redirect($user->getLandingRoute())->with('success', 'Welcome to RestoFy! Your free account has been created successfully.');
            } else {
                // CASE 2: PAID PLAN -> Should Redirect to Payment Gateway
                // TODO: Integrate Stripe Checkout here.
                // For now, we fall back to dashboard but with meaningful message/state (or auto-activate in dev)

                // In a real scenario:
                // return redirect()->route('payment.checkout', ['subscription_id' => ...]);

                // Current Dev Fallback (Bypass Payment):
                return redirect($user->getLandingRoute())->with('success', 'Welcome to RestoFy! Account created. (Payment simulation skipped)');
            }

        } catch (\Exception $e) {
            if ($useTransactions) {
                try {
                    DB::rollBack();
                } catch (\Exception $eRollback) {
                    // Ignore rollback errors
                }
            }
            \Log::error('Onboarding failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Onboarding failed: ' . $e->getMessage()]);
        }
    }

    protected function migrateTenantDatabase(): void
    {
        // Deprecated: Single DB architecture usage
    }

    protected function seedTenantDatabase(Tenant $tenant): void
    {
        // Deprecated: Single DB architecture usage
    }
}

