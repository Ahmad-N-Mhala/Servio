<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        try {
            if (Schema::hasTable('system_configurations')) {
                $configs = \App\Models\SystemConfiguration::all()->pluck('value', 'key');

                $overrides = [];

                // Email
                if ($configs->has('mail_host')) {
                    $overrides['mail.mailers.smtp.host'] = $configs['mail_host'];
                }
                if ($configs->has('mail_port')) {
                    $overrides['mail.mailers.smtp.port'] = $configs['mail_port'];
                }
                if ($configs->has('mail_username')) {
                    $overrides['mail.mailers.smtp.username'] = $configs['mail_username'];
                }
                if ($configs->has('mail_password')) {
                    $overrides['mail.mailers.smtp.password'] = $configs['mail_password'];
                }
                if ($configs->has('mail_encryption')) {
                    $overrides['mail.mailers.smtp.encryption'] = $configs['mail_encryption'];
                }

                if ($configs->has('mail_from_address')) {
                    $overrides['mail.from.address'] = $configs['mail_from_address'];
                }
                if ($configs->has('mail_from_name')) {
                    $overrides['mail.from.name'] = $configs['mail_from_name'];
                }

                // SMS Mapping
                if ($configs->has('sms_provider')) {
                    $provider = $configs['sms_provider'];
                    $overrides['services.sms.driver'] = $provider;

                    if ($provider === 'twilio') {
                        if ($configs->has('sms_sid')) {
                            $overrides['services.twilio.sid'] = $configs['sms_sid'];
                        }
                        if ($configs->has('sms_token')) {
                            $overrides['services.twilio.token'] = $configs['sms_token'];
                        }
                        if ($configs->has('sms_from')) {
                            $overrides['services.twilio.from'] = $configs['sms_from'];
                        }
                    } elseif ($provider === 'nexmo') {
                        if ($configs->has('sms_sid')) {
                            $overrides['services.nexmo.key'] = $configs['sms_sid'];
                        }
                        if ($configs->has('sms_token')) {
                            $overrides['services.nexmo.secret'] = $configs['sms_token'];
                        }
                        if ($configs->has('sms_from')) {
                            $overrides['services.nexmo.sms_from'] = $configs['sms_from'];
                        }
                    }
                }

                if (! empty($overrides)) {
                    config($overrides);
                }
            }
        } catch (\Throwable $e) {
            // Suppress errors during migration/setup or if DB not ready
        }

        Schema::defaultStringLength(191);

        // Intercept Gate checks to handle Multi-Tenant Role Permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Super Admin Bypass
            if ($user->is_super_admin) {
                return true;
            }

            // Get Current Context (Restaurant)
            $restaurant = $user->currentRestaurant();
            if (! $restaurant) {
                return null;
            }

            // 1. Check Role Permission first
            try {
                $targetId = (string) $restaurant->id;
                static $roleCache = [];
                $cacheKey = "user_role_{$user->id}_{$targetId}";

                if (! isset($roleCache[$cacheKey])) {
                    $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
                        ->table('restaurant_user')
                        ->where('email', $user->email)
                        ->where('restaurant_id', $targetId)
                        ->first();
                    $roleCache[$cacheKey] = $pivot ? (isset($pivot->role) ? $pivot->role : null) : null;
                }
                $pivotRole = $roleCache[$cacheKey];

                if (! $pivotRole) {
                    return null; // No role context
                }

                $role = \App\Models\Role::findByName($pivotRole, 'web');
                if (! $role || ! $role->hasPermissionTo($ability)) {
                    return null; // Role doesn't have it, let other gates decide (usually deny)
                }

                // 2. Role HAS permission. Now Check Plan Features.
                static $permToFeatureMap = null;
                if ($permToFeatureMap === null) {
                    $coreGroups = ['dashboard', 'orders', 'menu', 'tables', 'customers', 'staff', 'settings', 'service'];

                    // Map Permission Groups to Feature Keys where they differ
                    $groupMapping = [
                        'kitchen' => 'kds',
                        'waste' => 'inventory', // Waste is part of Inventory feature
                        'communication' => 'marketing',
                        'finance' => 'analytics',
                        'qr_ordering' => 'qr_ordering',
                    ];

                    $permToFeatureMap = [];
                    foreach (config('permissions') as $group => $data) {
                        // Skip Core Groups - they don't need a feature check
                        if (in_array($group, $coreGroups)) {
                            continue;
                        }

                        // Determine Feature Key
                        $featureKey = $groupMapping[$group] ?? $group;

                        if (isset($data['permissions'])) {
                            foreach ($data['permissions'] as $perm) {
                                $permToFeatureMap[$perm] = $featureKey;
                            }
                        }
                    }

                    // Explicit Overrides (if needed)
                    $permToFeatureMap['view_analytics'] = 'analytics';
                    $permToFeatureMap['export_reports'] = 'analytics';
                    $permToFeatureMap['view_sales_reports'] = 'analytics';
                }

                // If this permission is NOT in the map, it's either Core or unmapped. Allow it.
                if (! isset($permToFeatureMap[$ability])) {
                    return true;
                }

                $requiredFeature = $permToFeatureMap[$ability];

                // 3. Check if Feature is Enabled in Plan
                static $subscriptionCache = [];
                if (! isset($subscriptionCache[$targetId])) {
                    $subscription = \App\Models\RestaurantSubscription::where('restaurant_id', $targetId)
                        ->where('status', 'active')
                        ->with('plan')
                        ->latest()
                        ->first();

                    if ($subscription && $subscription->plan) {
                        $f = $subscription->plan->enabled_features;
                        if (is_string($f)) {
                            $feats = json_decode($f, true) ?? [];
                        } elseif (is_array($f)) {
                            $feats = $f;
                        } else {
                            $feats = [];
                        }
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
                    if (! $restaurant) {
                        return false;
                    }

                    // 3. Check Subscription & Plan Features
                    // We use a simple static cache to avoid repeated DB queries within the same request
                    static $subscriptionCache = [];

                    if (! array_key_exists($restaurant->id, $subscriptionCache)) {
                        $subscription = \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)
                            ->where('status', 'active')
                            ->with('plan') // Eager load plan
                            ->latest()
                            ->first();

                        // Cache the FEATURES array directly, or empty array if no active sub/plan
                        if ($subscription && $subscription->plan) {
                            $planFeatures = $subscription->plan->enabled_features;
                            if (is_string($planFeatures)) {
                                $planFeatures = json_decode($planFeatures, true) ?? [];
                            } elseif (! is_array($planFeatures)) {
                                $planFeatures = [];
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
            \Log::error('AppServiceProvider Gate Error: '.$e->getMessage());
        }

        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Feedback::observe(\App\Observers\FeedbackObserver::class);
        \App\Models\Customer::observe(\App\Observers\CustomerObserver::class);
    }
}
