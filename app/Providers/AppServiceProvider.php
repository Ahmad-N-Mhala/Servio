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
                return null; // Fallback to standard logic if no context
            }

            try {
                // Get Role Name from Pivot Table for this Context
                $pivotRole = \Illuminate\Support\Facades\DB::table('restaurant_user')
                    ->where('email', $user->email)
                    ->where('restaurant_id', $restaurant->id)
                    ->value('role');

                if (!$pivotRole) {
                    return null;
                }

                // Check Spatie Permissions for this Role Name
                // We use 'web' guard as default for this app
                $role = \Spatie\Permission\Models\Role::findByName($pivotRole, 'web');

                if ($role && $role->hasPermissionTo($ability)) {
                    return true;
                }
            } catch (\Exception $e) {
                // Role might not exist or permission not found
                // Continue to other checks
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

