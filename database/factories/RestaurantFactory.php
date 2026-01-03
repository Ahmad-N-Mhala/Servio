<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber,
            'is_active' => true,
            'owner_id' => \App\Models\User::factory()->create()->id, // Create an owner for relation integrity if needed
            'currency' => 'AED',
            'theme' => 'default',
            'google_map_location' => null
        ];
    }
}
