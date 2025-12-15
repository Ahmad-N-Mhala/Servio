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
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Talabat_logo.svg/1200px-Talabat_logo.svg.png',
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
                'logo_url' => 'https://z.nooncdn.com/s/app/com/noon/images/logos/noon-logo-en.svg',
                'api_documentation_url' => 'https://developers.noon.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Careem NOW',
                'slug' => 'careem',
                'description' => 'Careem\'s food and grocery delivery service, part of Uber, operating across the Middle East and North Africa.',
                'logo_url' => 'https://www.careem.com/images/logo.svg',
                'api_documentation_url' => 'https://developers.careem.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Deliveroo',
                'slug' => 'deliveroo',
                'description' => 'International food delivery company operating in UAE, Kuwait, and Qatar, known for premium restaurant partnerships.',
                'logo_url' => 'https://cdn.cookielaw.org/logos/dd6b162f-1a32-456a-9cfe-897231c7763c/4345ea78-053c-46d2-b11e-09adaef973dc/Netflix_Logo_PMS.png',
                'api_documentation_url' => 'https://developers.deliveroo.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Uber Eats',
                'slug' => 'ubereats',
                'description' => 'Global food delivery platform by Uber, operating in major cities across the Middle East.',
                'logo_url' => 'https://d3i4yxtzktqr9n.cloudfront.net/web-eats-v2/97c43f8974e6c876.svg',
                'api_documentation_url' => 'https://developer.uber.com/docs/eats',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Zomato',
                'slug' => 'zomato',
                'description' => 'Indian food delivery and restaurant discovery platform expanding in the Middle East region.',
                'logo_url' => 'https://b.zmtcdn.com/web_assets/b40b97e677bc7b2ca77c58c61db266fe1603954218.png',
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
                'logo_url' => 'https://www.hungerstation.com/_next/static/media/logo.svg',
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
                'logo_url' => 'https://www.jahez.com/assets/images/logo.svg',
                'api_documentation_url' => 'https://developers.jahez.com',
                'requires_api_key' => true,
                'requires_api_secret' => true,
                'requires_store_id' => true,
                'requires_webhook_secret' => false,
                'is_active' => true,
                'sort_order' => 8,
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
