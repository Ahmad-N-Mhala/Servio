<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryProvider;

class DeliveryProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Talabat',
                'slug' => 'talabat',
                'description' => 'Leading food delivery platform in the Middle East, operating across UAE, Saudi Arabia, Kuwait, Bahrain, Oman, Qatar, Jordan, and Egypt.',
                'logo_url' => '/images/delivery-partners/talabat.svg',
                'api_documentation_url' => 'https://developers.talabat.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Noon Food',
                'slug' => 'noon',
                'description' => 'Noon\'s food delivery service offering fast delivery across UAE and Saudi Arabia with a wide selection of restaurants.',
                'logo_url' => '/images/delivery-partners/noon.svg',
                'api_documentation_url' => 'https://developers.noon.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => false,
                'webhook_url_template' => 'https://your-domain.com/api/webhook/delivery/noon?store_id={store_id}',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Careem Food', // Updated Name
                'slug' => 'careem',
                'description' => 'Careem\'s food and grocery delivery service.',
                'logo_url' => '/images/delivery-partners/careem.svg',
                'api_documentation_url' => 'https://developers.careem.com',
                'requires_api_key' => false,
                'requires_api_secret' => false,
                'requires_client_id' => true,     // OAuth
                'requires_client_secret' => true, // OAuth
                'requires_store_id' => true,
                'requires_webhook_secret' => false, // Usually implicit or standard
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Deliveroo',
                'slug' => 'deliveroo',
                'description' => 'International food delivery company.',
                'logo_url' => '/images/delivery-partners/deliveroo.svg',
                'api_documentation_url' => 'https://developers.deliveroo.com',
                'requires_api_key' => true,    // Basic Auth User
                'requires_api_secret' => true, // Basic Auth Pass
                'requires_store_id' => true, // Not strictly needed for API auth, but good for ref
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Uber Eats',
                'slug' => 'ubereats',
                'description' => 'Global food delivery platform by Uber.',
                'logo_url' => '/images/delivery-partners/ubereats.svg',
                'api_documentation_url' => 'https://developer.uber.com/docs/eats',
                'requires_api_key' => false,
                'requires_api_secret' => false,
                'requires_client_id' => true,     // OAuth
                'requires_client_secret' => true, // OAuth
                'requires_store_id' => true,
                'requires_webhook_secret' => true, // HMAC sig
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Zomato',
                'slug' => 'zomato',
                'description' => 'Indian food delivery and restaurant discovery platform expanding in the Middle East region.',
                'logo_url' => '/images/delivery-partners/zomato.svg',
                'api_documentation_url' => 'https://developers.zomato.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'HungerStation',
                'slug' => 'hungerstation',
                'description' => 'Saudi Arabia\'s leading food delivery app, serving customers across the Kingdom.',
                'logo_url' => '/images/delivery-partners/hungerstation.svg',
                'api_documentation_url' => 'https://developers.hungerstation.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Jahez',
                'slug' => 'jahez',
                'description' => 'Popular Saudi food delivery platform known for fast delivery and local restaurant partnerships.',
                'logo_url' => '/images/delivery-partners/jahez.png',
                'api_documentation_url' => 'https://developers.jahez.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => false,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Keeta',
                'slug' => 'keeta',
                'description' => 'Fast-growing food delivery app in the Middle East, backed by Meituan.',
                'logo_url' => '/images/delivery-partners/keeta.png',
                'api_documentation_url' => 'https://www.keeta.com', // Placeholder
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 9,
            ],
        ];

        foreach ($providers as $provider) {
            DeliveryProvider::updateOrCreate(
                ['slug' => $provider['slug']],
                $provider
            );
        }

        $this->command->info('✓ Created ' . count($providers) . ' delivery providers');
    }
}
