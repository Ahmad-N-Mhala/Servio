<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\RestaurantSubscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DummyRestaurantSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Restaurant (Idempotent)
        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'urban-eats'],
            [
                'name' => 'Urban Eats',
                'email' => 'contact@urbaneats.com',
                'phone' => '+1234567890',
                'address' => '123 Foodie Lane',
                'city' => 'Metropolis',
                'country' => 'UAE',
                'currency' => 'AED',
                'locale' => 'en',
                'status' => 'active',
                'service_type' => 'all',
                'next_order_number' => 1,
                'has_cash_drawer' => true,
            ]
        );

        // 2. Create Users
        $password = Hash::make('password');

        $users = [
            ['name' => 'Urban Owner', 'email' => 'owner@urbaneats.com', 'role' => 'owner'],
            ['name' => 'Urban Manager', 'email' => 'manager@urbaneats.com', 'role' => 'manager'],
            ['name' => 'Urban Chef', 'email' => 'chef@urbaneats.com', 'role' => 'head_chef'],
            ['name' => 'Urban Waiter', 'email' => 'waiter@urbaneats.com', 'role' => 'waiter'],
            ['name' => 'Urban Cashier', 'email' => 'cashier@urbaneats.com', 'role' => 'cashier'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => $password]
            );

            // Manual Insert to bypass 'sync' issues with non-standard pivot keys in MongoDB
            $pivotExists = \Illuminate\Support\Facades\DB::connection('mongodb')->table('restaurant_user')
                ->where('restaurant_id', (string) $restaurant->id)
                ->where('email', $u['email'])
                ->exists();

            if (!$pivotExists) {
                \Illuminate\Support\Facades\DB::connection('mongodb')->table('restaurant_user')->insert([
                    'restaurant_id' => (string) $restaurant->id,
                    'email' => $u['email'],
                    'role' => $u['role'],
                    'is_active' => true,
                    'created_at' => now(), // Laravel-MongoDB converts Carbon to UTCDateTime
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Subscription
        $plan = Plan::firstOrCreate(
            ['slug' => 'standard'],
            [
                'name' => 'Standard Plan',
                'price' => 100,
                'currency' => 'AED',
                'billing_cycle' => 'monthly',
                'is_active' => true
            ]
        );

        RestaurantSubscription::firstOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'price' => $plan->price,
                'billing_cycle' => 'yearly',
                'is_paid' => true,
            ]
        );

        // 4. Menu Categories
        $cat1 = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('sort_order', 1)->first();

        if (!$cat1) {
            $cat1 = MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $cat2 = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('sort_order', 2)->first();

        if (!$cat2) {
            $cat2 = MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }

        // 5. Menu Items
        // Use Check-Then-Create to avoid 'firstOrCreate' merging issues with Translatable in MongoDB
        if (!MenuItem::where('restaurant_id', $restaurant->id)->where('name.en', 'Classic Burger')->exists()) {
            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'menu_category_id' => $cat1->id,
                'name' => ['en' => 'Classic Burger', 'ar' => 'برجر كلاسيك'],
                'description' => ['en' => 'Beef patty with cheese', 'ar' => 'لحم بقر مع جبن'],
                'price' => 35.00,
                'is_available' => true,
                'is_active' => true,
            ]);
        }

        if (!MenuItem::where('restaurant_id', $restaurant->id)->where('name.en', 'Cola')->exists()) {
            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'menu_category_id' => $cat2->id,
                'name' => ['en' => 'Cola', 'ar' => 'كولا'],
                'price' => 10.00,
                'is_available' => true,
                'is_active' => true,
            ]);
        }

        // 6. Tables
        for ($i = 1; $i <= 5; $i++) {
            Table::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => "T$i"],
                [
                    'capacity' => 4,
                    'status' => 'available',
                    'qr_code' => "T$i-QR",
                ]
            );
        }

        $this->command->info('Restaurant "Urban Eats" ready.');
    }
}
