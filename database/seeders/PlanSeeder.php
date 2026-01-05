<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price_monthly' => 99.00,
                'price_yearly' => 990.00,
                'currency' => 'AED',
                'features' => [
                    'plan_features.one_restaurant',
                    'plan_features.staff_limit_5',
                    'plan_features.basic_menu',
                    'plan_features.order_tracking',
                    'plan_features.kds',
                    'plan_features.email_support',
                ],
                'enabled_features' => ['pos', 'kds'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_monthly' => 299.00,
                'price_yearly' => 2990.00,
                'currency' => 'AED',
                'features' => [
                    'plan_features.three_restaurants',
                    'plan_features.unlimited_staff',
                    'plan_features.advanced_menu',
                    'plan_features.realtime_tracking',
                    'plan_features.kds',
                    'plan_features.qr_ordering',
                    'plan_features.analytics_reports',
                    'plan_features.priority_support',
                ],
                'enabled_features' => ['pos', 'inventory', 'loyalty', 'analytics', 'kds', 'qr_ordering'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 799.00,
                'price_yearly' => 7990.00,
                'currency' => 'AED',
                'features' => [
                    'plan_features.unlimited_restaurants',
                    'plan_features.unlimited_staff',
                    'plan_features.advanced_menu',
                    'plan_features.realtime_tracking',
                    'plan_features.kds',
                    'plan_features.qr_ordering',
                    'plan_features.advanced_analytics',
                    'plan_features.api_access',
                    'plan_features.custom_integrations',
                    'plan_features.dedicated_support',
                ],
                'enabled_features' => ['pos', 'inventory', 'loyalty', 'analytics', 'marketing', 'feedback', 'delivery', 'kds', 'qr_ordering'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
