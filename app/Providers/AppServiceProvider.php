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
                return null;
            }

            // 1. Check Role Permission first
            try {
                $targetId = (string) $restaurant->id;
                static $roleCache = [];
                $cacheKey = "user_role_{$user->id}_{$targetId}";

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
                    return null; // No role context
                }

                $role = \App\Models\Role::findByName($pivotRole, 'web');
                if (!$role || !$role->hasPermissionTo($ability)) {
                    return null; // Role doesn't have it, let other gates decide (usually deny)
                }

                // 2. Role HAS permission. Now Check Plan Features.
                // Define Permission -> Feature Map
                // This map helps us know which Feature is required for a given Permission
                static $permToFeatureMap = null;
                if ($permToFeatureMap === null) {
                    $mapConfig = [
                        'menu' => 'menu_management',
                        'pos' => 'pos_system',
                        'orders' => 'order_management',
                        'kitchen' => 'kds',
                        'tables' => 'table_management',
                        'customers' => 'customer_management',
                        'staff' => 'staff_management',
                        'inventory' => 'inventory_management',
                        'waste' => 'waste_management',
                        'loyalty' => 'customer_loyalty',
                        'delivery' => 'delivery_integration',
                        'communication' => 'communication',
                        'finance' => 'financial_management',
                    ];
                    $permToFeatureMap = [];
                    foreach (config('permissions') as $group => $data) {
                        if (isset($mapConfig[$group]) && isset($data['permissions'])) {
                            foreach ($data['permissions'] as $perm) {
                                $permToFeatureMap[$perm] = $mapConfig[$group];
                            }
                        }
                    }
                    // Extras
                    foreach (config('permissions.service.permissions', []) as $p)
                        $permToFeatureMap[$p] = 'order_management';
                    $permToFeatureMap['view_analytics'] = 'reports_analytics';
                    $permToFeatureMap['export_reports'] = 'reports_analytics';
                    $permToFeatureMap['view_sales_reports'] = 'financial_management'; // Ensure consistency
                }

                // If this permission is NOT linked to any feature, it's a Core permission. Allow it.
                if (!isset($permToFeatureMap[$ability])) {
                    return true;
                }

                $requiredFeature = $permToFeatureMap[$ability];

                // 3. Check if Feature is Enabled in Plan
                static $subscriptionCache = [];
                if (!isset($subscriptionCache[$targetId])) {
                    $subscription = \App\Models\RestaurantSubscription::where('restaurant_id', $targetId)
                        ->where('status', 'active')
                        ->with('plan')
                        ->latest()
                        ->first();

                    $feats = [];
                    if ($subscription && $subscription->plan) {
                        $f = $subscription->plan->features;
                        $feats = is_string($f) ? (json_decode($f, true) ?? []) : $f;
                    }
                    $subscriptionCache[$targetId] = $feats ?? [];
                }

                if (in_array($requiredFeature, $subscriptionCache[$targetId])) {
                    return true; // Plan has feature -> Allow
                }

                // Log::info("Gate: Access DENIED for {$ability}. Plan missing feature {$requiredFeature}.");
                return false; // Plan missing feature -> Deny

            } catch (\Exception $e) {
                // Log::error("Gate Check Failed: " . $e->getMessage());
                return null;
            }
        });

        try {
            $features = config('features', []);

            foreach ($features as $key => $label) {
                // Define a gate for each feature key (e.g., 'pos_system', 'inventory_management')
                \Illuminate\Support\Facades\Gate::define($key, function ($user) use ($key) {
                    // 1. Super Admin always has access
                    if ($user->is_super_admin) {
                        return true;
                    }

                    // 2. Get the current restaurant context
                    $restaurant = $user->currentRestaurant();
                    if (!$restaurant) {
                        return false;
                    }

                    // 3. Check Subscription & Plan Features
                    // We use a simple static cache to avoid repeated DB queries within the same request
                    static $subscriptionCache = [];

                    if (!array_key_exists($restaurant->id, $subscriptionCache)) {
                        $subscription = \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)
                            ->where('status', 'active')
                            ->with('plan') // Eager load plan
                            ->latest()
                            ->first();

                        // Cache the FEATURES array directly, or empty array if no active sub/plan
                        if ($subscription && $subscription->plan) {
                            $planFeatures = $subscription->plan->features;
                            if (is_string($planFeatures)) {
                                $planFeatures = json_decode($planFeatures, true) ?? [];
                            }
                            $subscriptionCache[$restaurant->id] = is_array($planFeatures) ? $planFeatures : [];
                        } else {
                            $subscriptionCache[$restaurant->id] = [];
                        }
                    }

                    $allowedFeatures = $subscriptionCache[$restaurant->id];

                    // 4. Return true if this feature key is in the plan's allowed features
                    return in_array($key, $allowedFeatures);
                });
            }
        } catch (\Exception $e) {
            // Fallback during migrations or if table doesn't exist yet
            \Log::error('AppServiceProvider Gate Error: ' . $e->getMessage());
        }
    }
}

