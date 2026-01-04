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
                    '1 restaurant',
                    'Up to 5 staff members',
                    'Basic menu management',
                    'Order tracking',
                    'Kitchen Display System (KDS)',
                    'Email support',
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
                    '3 restaurants',
                    'Unlimited staff members',
                    'Advanced menu management',
                    'Real-time order tracking',
                    'Kitchen Display System (KDS)',
                    'QR Code Ordering',
                    'Analytics & reports',
                    'Priority support',
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
                    'Unlimited restaurants',
                    'Unlimited staff members',
                    'Advanced menu management',
                    'Real-time order tracking',
                    'Kitchen Display System (KDS)',
                    'QR Code Ordering',
                    'Advanced analytics & reports',
                    'API access',
                    'Custom integrations',
                    'Dedicated support',
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

