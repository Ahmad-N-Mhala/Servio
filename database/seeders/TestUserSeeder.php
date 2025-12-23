<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        $password = Hash::make('password');

        $roles = [
            'manager' => 'Manager',
            'head_chef' => 'Head Chef',
            'kitchen_staff' => 'Kitchen Staff',
            'waiter' => 'Waiter',
            'cashier' => 'Cashier',
            'delivery_driver' => 'Delivery Driver',
        ];

        foreach ($roles as $roleKey => $roleName) {
            $email = strtolower(str_replace(' ', '_', $roleKey)) . '@example.com';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName . ' User',
                    'password' => $password,
                ]
            );

            // Assign role
            $role = Role::where('name', $roleKey)->first();
            if ($role) {
                $user->syncRoles([$role]);
                $this->command->info("Created user: {$email} with role: {$roleKey}");
            } else {
                $this->command->error("Role not found: {$roleKey}");
            }

            // Assign to first restaurant
            $restaurant = \App\Models\Restaurant::first();
            if ($restaurant) {
                // Check if already attached to avoid duplicates if run multiple times
                if (!$user->restaurants()->where('restaurant_id', $restaurant->id)->exists()) {
                    $user->restaurants()->attach($restaurant->id);
                    $this->command->info("Attached user to restaurant: {$restaurant->name}");
                }
            } else {
                $this->command->warn("No restaurant found to attach user to.");
            }
        }
        
        $this->command->info('Test users created successfully. Password for all is "password".');
    }
}
