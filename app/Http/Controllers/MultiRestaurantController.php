<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MultiRestaurantController extends Controller
{

    /**
     * Display a listing of the user's restaurants.
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch restaurants where the user's email is associated
        // We use the central 'restaurants' table now
        $allowedIds = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $user->email)
            ->pluck('restaurant_id')
            ->toArray();

        $restaurants = Restaurant::with(['subscription.plan'])
            ->whereIn('id', $allowedIds)
            ->get()
            ->map(function ($restaurant) use ($user) {
                // Fetch pivot data manually
                $pivot = \DB::table('restaurant_user')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('email', $user->email)
                    ->first();

                // Fetch active plan from the subscription relationship
                $planName = $restaurant->subscription && $restaurant->subscription->plan
                    ? $restaurant->subscription->plan->name
                    : 'Free'; // Default to Free if no subscription
    
                // Domain logic is deprecated in single-DB, so we can return null or a logical ID
                $domain = request()->getHost(); // Just current host since we are single domain now
    
                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'slug' => $restaurant->slug,
                    'logo' => null, // Add logo column later
                    'role' => $pivot ? $pivot->role : 'staff',
                    'is_active' => $pivot ? (isset($pivot->is_active) ? (bool) $pivot->is_active : true) : false,
                    'plan' => $planName,
                    'domain' => $domain,
                ];
            });

        // Get the user's current plan (from first restaurant they own)
        $currentPlan = null;
        $maxRestaurants = 1; // Default limit

        // Check if user has an 'owner' role in any restaurant
        $ownerRestaurantIds = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $user->email)
            ->where('role', 'owner')
            ->pluck('restaurant_id')
            ->toArray();

        $firstRestaurant = !empty($ownerRestaurantIds)
            ? Restaurant::with(['subscription.plan'])->whereIn('id', $ownerRestaurantIds)->first()
            : null;

        if ($firstRestaurant && $firstRestaurant->subscription && $firstRestaurant->subscription->plan) {
            $currentPlan = $firstRestaurant->subscription->plan;
            $maxRestaurants = $currentPlan->max_restaurants ?? 1;
        }

        // Check if user can add more restaurants
        $canAddRestaurant = $restaurants->count() < $maxRestaurants;

        return Inertia::render('MultiRestaurant/Index', [
            'restaurants' => $restaurants,
            'canAddRestaurant' => $canAddRestaurant,
            'currentPlan' => $currentPlan ? [
                'name' => $currentPlan->name,
                'max_restaurants' => $maxRestaurants,
            ] : null,
        ]);
    }

    /**
     * Switch context to a specific restaurant.
     * In a Stancl/Tenancy setup, this usually means redirecting to that tenant's domain.
     */
    public function switch(Request $request, Restaurant $restaurant)
    {
        // Verify user has access to this restaurant by email in the pivot table (central DB)
        $hasAccess = \DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->where('email', $request->user()->email)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to this restaurant.');
        }

        // Set the active restaurant in the session
        session(['active_restaurant_id' => $restaurant->id]);

        // Allows redirecting to a specific page after switching context
        if ($request->has('redirect_to') && $request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return redirect($request->user()->getLandingRoute());
    }
    public function create()
    {
        $defaultCountry = $this->getCountryFromIp(request()->ip());
        $countries = \App\Models\Country::all();

        // Get user's plan features to determine if loyalty section should be shown
        $user = Auth::user();
        $existingRestaurantIds = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $user->email)
            ->where('role', 'owner')
            ->pluck('restaurant_id')
            ->toArray();

        $planFeatures = [];
        if (!empty($existingRestaurantIds)) {
            $existingRestaurant = Restaurant::with('subscription.plan')->whereIn('id', $existingRestaurantIds)->first();
            if ($existingRestaurant && $existingRestaurant->subscription && $existingRestaurant->subscription->plan) {
                $plan = $existingRestaurant->subscription->plan;
                $raw = $plan->enabled_features ?? [];
                $features = is_string($raw) ? json_decode($raw, true) : $raw;
                $planFeatures = is_array($features) ? $features : [];
            }
        }

        return Inertia::render('MultiRestaurant/Create', [
            'defaultCountry' => $defaultCountry,
            'countries' => $countries,
            'planFeatures' => $planFeatures,
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
            // Timeout set to 3 seconds to avoid blocking
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
            'earning_method_type' => ['nullable', 'in:order_total,visit'], // Nullable if section hidden
            'earning_points' => ['nullable', 'integer', 'min:1'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
            // Location Details
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB Max
            // New Fields for Alignment
            'email' => ['required', 'email'],
            'notification_email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'service_type' => ['required', 'in:self_service,table_service,both'],
            'google_map_location' => ['nullable', 'url'],
        ]);

        $user = Auth::user();

        // Get user's existing subscription plan from their first restaurant
        $existingRestaurantIds = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $user->email)
            ->where('role', 'owner')
            ->pluck('restaurant_id')
            ->toArray();

        if (empty($existingRestaurantIds)) {
            return back()->withErrors(['error' => 'No existing subscription found. Please contact support.']);
        }

        $existingRestaurant = Restaurant::with('subscription.plan')->whereIn('id', $existingRestaurantIds)->first();

        if (!$existingRestaurant || !$existingRestaurant->subscription) {
            return back()->withErrors(['error' => 'No active subscription found. Please contact support.']);
        }

        $subscription = $existingRestaurant->subscription;
        $plan = $subscription->plan;

        $raw = $plan->enabled_features ?? [];
        $features = is_string($raw) ? json_decode($raw, true) : $raw;
        $planFeatures = is_array($features) ? $features : [];
        $hasLoyalty = in_array('loyalty', $planFeatures);

        $useTransactions = false; // Force false for local dev/standalone mongo
        /*
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $useTransactions = true;
        } catch (\Exception $e) {
            // Check if it's the "Transaction numbers are only allowed on a replica set member" error
            if (str_contains($e->getMessage(), 'replica set member')) {
                $useTransactions = false;
                \Log::warning('MongoDB Transaction skipped: Server is running as standalone instance.');
            } else {
                throw $e;
            }
        }
        */

        try {
            // 1. Create Restaurant
            $countryObj = \App\Models\Country::where('name', $validated['country'])->first();
            $currency = $countryObj ? $countryObj->currency : 'AED';

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('restaurant-logos', 'public');
            }

            $restaurant = Restaurant::create([
                'name' => $validated['restaurant_name'],
                'slug' => \Illuminate\Support\Str::slug($validated['restaurant_name']) . '-' . \Illuminate\Support\Str::random(6),
                'currency' => $currency,
                'locale' => 'en',
                'country' => $validated['country'],
                'state' => $validated['state'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'zip_code' => $validated['zip_code'] ?? null,
                'logo' => $logoPath,
                'email' => $validated['email'],
                'notification_email' => $validated['notification_email'],
                'phone' => $validated['phone'] ?? null,
                'service_type' => $validated['service_type'],
                'google_map_location' => $validated['google_map_location'] ?? null,
            ]);

            // 2. Link User to Restaurant via Pivot
            \Illuminate\Support\Facades\DB::table('restaurant_user')->insert([
                'restaurant_id' => $restaurant->id,
                'email' => $user->email,
                'role' => 'owner',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Create Staff Record
            $staff = new \App\Models\Staff();
            $staff->restaurant_id = $restaurant->id;
            $staff->user_id = $user->id;
            $staff->role = 'owner';
            $staff->is_active = true;
            $staff->joined_at = now();
            $staff->save();

            // 4. Create Default Earning Method (Only if Loyalty is enabled)
            if ($hasLoyalty && !empty($validated['earning_method_type'])) {
                \App\Models\EarningMethod::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => ['en' => 'Standard Loyalty', 'ar' => 'نقاط الولاء'],
                    'description' => 'Default earning method set.',
                    'type' => $validated['earning_method_type'],
                    'points' => $validated['earning_points'] ?? 1,
                    'currency_amount' => 1,
                    'min_spent' => $validated['min_spent'] ?? 0,
                    'is_active' => true,
                ]);
            }

            // 5. Create Subscription (using same plan and billing cycle as existing)
            \App\Models\RestaurantSubscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $subscription->billing_cycle,
                'starts_at' => now(),
                'ends_at' => $subscription->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            /*
            if ($useTransactions) {
                \Illuminate\Support\Facades\DB::commit();
            }
            */

            // Setup Session
            session(['active_restaurant_id' => $restaurant->id]);

            return redirect($user->getLandingRoute())->with('success', 'Restaurant created successfully!');

        } catch (\Exception $e) {
            /*
            if ($useTransactions) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            */
            \Log::error('Restaurant Creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Creation failed: ' . $e->getMessage()]);
        }
    }

    public function edit(Request $request, Restaurant $restaurant)
    {
        // 1. Permission Check
        $isOwner = \DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->where('email', $request->user()->email)
            ->where('role', 'owner')
            ->exists();

        if (!$isOwner && !$request->user()->can('edit_restaurant')) {
            abort(403);
        }

        // Load relationships
        $restaurant->load(['subscription.plan', 'earningMethods']);

        $planFeatures = [];
        if ($restaurant->subscription && $restaurant->subscription->plan) {
            $plan = $restaurant->subscription->plan;
            $raw = $plan->enabled_features ?? [];
            $features = is_string($raw) ? json_decode($raw, true) : $raw;
            $planFeatures = is_array($features) ? $features : [];
        }

        $earningMethod = $restaurant->earningMethods->first();

        // 2. Fetch Countries for Selection
        $countries = \App\Models\Country::select('name', 'currency', 'states')->get();

        return Inertia::render('MultiRestaurant/Edit', [
            'restaurant' => $restaurant,
            'countries' => $countries,
            'planFeatures' => $planFeatures,
            'earningMethod' => $earningMethod,
        ]);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        // 1. Permission Check
        $isOwner = \DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->where('email', $request->user()->email)
            ->where('role', 'owner')
            ->exists();

        if (!$isOwner && !$request->user()->can('edit_restaurant')) {
            abort(403);
        }

        // 2. Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notification_email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'google_map_location' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'service_type' => ['required', 'in:self_service,table_service,both'],
            'has_cash_drawer' => ['boolean'],
            // Loyalty
            'earning_method_type' => ['nullable', 'in:order_total,visit'],
            'earning_points' => ['nullable', 'integer', 'min:1'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
        ]);

        // 3. Update Logic
        // Determine currency if country changed
        if ($restaurant->country !== $validated['country']) {
            $countryObj = \App\Models\Country::where('name', $validated['country'])->first();
            $validated['currency'] = $countryObj ? $countryObj->currency : 'AED';
        }

        // Handle File Upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('restaurant-logos', 'public');
        }

        $restaurant->update($validated);

        // 4. Update Loyalty Earning Method
        // Only update if data is provided. If empty, field might be hidden in UI so ignore.
        // We only check earning_method_type presence to decide
        if ($request->has('earning_method_type') && !empty($validated['earning_method_type'])) {
            \App\Models\EarningMethod::updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'type' => $validated['earning_method_type'],
                    'points' => $validated['earning_points'] ?? 1,
                    'min_spent' => $validated['min_spent'] ?? 0,
                    'name' => ['en' => 'Standard Loyalty', 'ar' => 'نقاط الولاء'],
                    'is_active' => true,
                    'currency_amount' => 1,
                ]
            );
        }

        return redirect()->route('restaurants.index')->with('success', 'Restaurant updated successfully.');
    }
}
