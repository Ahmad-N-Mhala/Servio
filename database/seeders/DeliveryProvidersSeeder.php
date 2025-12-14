<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryIntegration;

class DeliveryProvidersSeeder extends Seeder
{
    public function run(): void
    {
        // Get first restaurant
        $restaurant = \App\Models\Restaurant::first();

        if (!$restaurant) {
            $this->command->warn('No restaurants found. Please create a restaurant first.');
            return;
        }

        $providers = [
            [
                'provider' => 'Noon',
                'is_enabled' => true,
                'restaurant_id' => $restaurant->id,
            ],
            [
                'provider' => 'Kareem',
                'is_enabled' => true,
                'restaurant_id' => $restaurant->id,
            ],
            [
                'provider' => 'UberEats',
                'is_enabled' => true,
                'restaurant_id' => $restaurant->id,
            ],
            [
                'provider' => 'Deliveroo',
                'is_enabled' => true,
                'restaurant_id' => $restaurant->id,
            ],
            [
                'provider' => 'Talabat',
                'is_enabled' => true,
                'restaurant_id' => $restaurant->id,
            ],
        ];

        foreach ($providers as $provider) {
            DeliveryIntegration::updateOrCreate(
                ['provider' => $provider['provider'], 'restaurant_id' => $restaurant->id],
                $provider
            );
        }

        $this->command->info('✓ Created ' . count($providers) . ' delivery providers for ' . $restaurant->name);
    }
}
