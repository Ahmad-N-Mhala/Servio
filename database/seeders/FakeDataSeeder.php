<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure plans exist
        $this->call(PlanSeeder::class);
        $plans = \App\Models\Plan::all();

        // Create 10 dummy restaurants with owners
        for ($i = 1; $i <= 10; $i++) {
            $owner = \App\Models\User::updateOrCreate(
                ['email' => "owner$i@example.com"],
                [
                    'name' => "Owner $i",
                    'password' => bcrypt('password'),
                ]
            );

            $restaurantName = "Restaurant $i - " . ['Bistro', 'Cafe', 'Grill', 'Diner', 'Lounge'][rand(0, 4)];
            $restaurant = \App\Models\Restaurant::create([
                'name' => $restaurantName,
                'slug' => \Illuminate\Support\Str::slug($restaurantName . '-' . $i), // Unique slug
                'address' => "$i Main St, Cityville",
                'phone' => "555-010$i",
                'email' => "contact@restaurant$i.com",
            ]);

            // Link owner via pivot directly
            \DB::table('restaurant_user')->insert([
                'restaurant_id' => $restaurant->id,
                'email' => $owner->email,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign a random plan to the restaurant (conceptually, assuming subscription table or column exists, 
            // but for now just establishing existence)
        }

        // Create some extra users
        \App\Models\User::factory(20)->create();
    }
}
