<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Intercept Gate checks to handle Multi-Tenant Role Permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Super Admin Bypass
            if ($user->is_super_admin) {
                return true;
            }

            // Get Current Context (Restaurant)
            $restaurant = $user->currentRestaurant();
            if (!$restaurant) {
                \Log::warning('Gate: No current restaurant context');
                return null; // Fallback to standard logic if no context
            }

            try {
                $targetId = (string) $restaurant->id;
                $cacheKey = "user_role_{$user->id}_{$targetId}";
                static $roleCache = [];

                if (!isset($roleCache[$cacheKey])) {
                    $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
                        ->table('restaurant_user')
                        ->where('email', $user->email)
                        ->where('restaurant_id', $targetId)
                        ->first();

                    $roleCache[$cacheKey] = $pivot ? (isset($pivot->role) ? $pivot->role : null) : null;
                }

                $pivotRole = $roleCache[$cacheKey];

                if (!$pivotRole) {
                    \Log::warning("Gate: No pivot role found for user {$user->email} in restaurant {$targetId}. Falling back to global roles.");

                    // Fallback: Check if user has the permission via ANY of their global roles
                    if ($user->hasPermissionTo($ability)) {
                        return true;
                    }

                    return null;
                }

                // Check Spatie Permissions for this Role Name
                // We use 'web' guard as default for this app
                $role = \App\Models\Role::findByName($pivotRole, 'web');

                if ($role && $role->hasPermissionTo($ability)) {
                    // \Log::info("Gate: Access GRANTED for {$ability} via role {$pivotRole}");
                    return true;
                } else {
                    \Log::info("Gate: Access DENIED for {$ability}. Role {$pivotRole} does not have it.");
                }
            } catch (\Exception $e) {
                // Role might not exist or permission not found
                // Continue to other checks
                \Log::error("Gate Check Failed: " . $e->getMessage());
            }

            return null; // Continue to next gate checks (e.g. features loop)
        });

        try {
            $features = config('features', []);

            foreach ($features as $key => $label) {
                \Illuminate\Support\Facades\Gate::define($key, function (\App\Models\User $user) use ($key) {
                    if ($user->is_super_admin) {
                        return true;
                    }

                    // 1. Check if user has specific permission via Role
                    // This uses Spatie's permission check. 
                    // Note: Super admins are handled above.
                    // If the permission key is valid in our permission list (not just a feature flag), checking it is good.
                    // However, we are iterating over 'features' config which might be 'pos', 'inventory' etc.
                    // Those match the keys in subscriptions, but specific actions like 'add_stock' are permissions.
                    // The Gate::define here is specifically for FEATURES toggles based on previous context.
                    // BUT, if we want to intercept ALL gate calls (dynamic), we should use Gate::before or similar, 
                    // or ensure we aren't confusing features with permissions.

                    // IF $key is in config('features'), it's a feature toggle.
                    // IF we are calling Gate::authorize('view_inventory'), that is a PERMISSION.
                    // Laravel/Spatie automatically handles permission-based gates if we don't override them here with same name.
                    // If 'view_inventory' is NOT in config('features'), this define won't run for it options.

                    // Wait, the loop iterates `config('features')`. 
                    // If 'view_inventory' is NOT in that array, this custom gate definition does NOT apply to it.
                    // Spatie's permission package registers its own gate checks for permissions found in DB.

                    // PROPOSED FIX:
                    // We need to ensure that checking a FEATURE (like 'inventory-management' from config) 
                    // also respects if the user has a Role that grants access, but primarily subscription controls availability.

                    // Issue: The user asked "are you sure... reflected?".
                    // The previous error was 403 on 'add_stock'.
                    // 'add_stock' is a PERMISSION. It is likely NOT in config('features').
                    // So 'add_stock' is handled by Spatie.

                    // So why did it fail? 
                    // 1. User didn't have the permission in DB (which we asked user to update).
                    // 2. Or Spatie cache needs clearing.

                    // However, let's strengthen the subscription check here:
                    // If this key IS a feature, we must ensure the restaurant has it.

                    $restaurant = $user->currentRestaurant();
                    if (!$restaurant) {
                        return false;
                    }

                    // Simple caching for the request
                    static $subscriptionCache = [];

                    if (!array_key_exists($restaurant->id, $subscriptionCache)) {
                        $subscriptionCache[$restaurant->id] = \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)
                            ->where('status', 'active')
                            ->with('plan')
                            ->latest()
                            ->first();
                    }

                    $subscription = $subscriptionCache[$restaurant->id];

                    if (!$subscription || !$subscription->plan) {
                        return false;
                    }

                    $planFeatures = $subscription->plan->features;

                    if (is_string($planFeatures)) {
                        $planFeatures = json_decode($planFeatures, true) ?? [];
                    }

                    if (!is_array($planFeatures)) {
                        $planFeatures = [];
                    }

                    return in_array($key, $planFeatures);
                });
            }
        } catch (\Exception $e) {
            // Fallback during migrations/setup if tables don't exist
        }
    }
}

