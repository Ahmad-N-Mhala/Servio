<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Restaurant;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a User
        $user = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create a Restaurant
        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'demo-resto'],
            [
                'name' => 'Demo Resto',
                'description' => 'A demo restaurant for testing.',
                'currency' => 'AED',
                'locale' => 'en',
            ]
        );

        // 3. Link User to Restaurant via Pivot (using Email as key)
        DB::table('restaurant_user')->updateOrInsert(
            [
                'restaurant_id' => $restaurant->id,
                'email' => $user->email,
            ],
            [
                'role' => 'owner',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Demo User Created:");
        $this->command->info("Email: admin@demo.com");
        $this->command->info("Password: password");
    }
}
