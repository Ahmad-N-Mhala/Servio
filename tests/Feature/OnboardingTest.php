<?php

namespace Tests\Feature;

use App\Models\EarningMethod;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\Staff;
use App\Models\User;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any old test records
        User::where('email', 'onboard_test@example.com')->forceDelete();
        Restaurant::where('email', 'onboard_test@example.com')->forceDelete();
        Plan::where('slug', 'enterprise')->forceDelete();
    }

    protected function tearDown(): void
    {
        // Clean up test records
        User::where('email', 'onboard_test@example.com')->forceDelete();
        Restaurant::where('email', 'onboard_test@example.com')->forceDelete();

        parent::tearDown();
    }

    public function test_user_can_complete_onboarding_successfully(): void
    {
        // 1. Ensure we have an active Enterprise Plan and owner role in database
        $plan = Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'price_monthly' => 400.00,
                'price_yearly' => 4500.00,
                'currency' => 'AED',
                'is_active' => true,
                'features' => ['loyalty'],
                'enabled_features' => ['pos', 'inventory', 'loyalty'],
            ]
        );

        \App\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        // 2. Perform the onboarding request
        $response = $this->post(route('onboard.store'), [
            'restaurant_name' => 'Onboard Test Restaurant',
            'plan_id' => (string) $plan->id,
            'billing_cycle' => 'monthly',
            'name' => 'Test Onboard Owner',
            'phone' => '+971500000000',
            'email' => 'onboard_test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'earning_method_type' => 'order_total',
            'earning_points' => 10,
            'min_spent' => 10,
            'country' => 'United Arab Emirates',
            'state' => 'Dubai',
            'city' => 'Dubai',
            'address' => 'Sheikh Zayed Road',
            'zip_code' => '00000',
            'google_map_location' => 'https://maps.google.com',
            'service_type' => 'both',
        ]);

        // 3. Assert redirects to active dashboard and has no session errors
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 4. Assert records created in MongoDB
        $user = User::where('email', 'onboard_test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('owner'));

        $restaurant = Restaurant::where('email', 'onboard_test@example.com')->first();
        $this->assertNotNull($restaurant);
        $this->assertEquals('Onboard Test Restaurant', $restaurant->name);

        $staff = Staff::where('user_id', $user->id)->first();
        $this->assertNotNull($staff);
        $this->assertEquals('owner', $staff->role);

        $subscription = RestaurantSubscription::where('restaurant_id', $restaurant->id)->first();
        $this->assertNotNull($subscription);
        $this->assertEquals('active', $subscription->status);

        $earningMethod = EarningMethod::where('restaurant_id', $restaurant->id)->first();
        $this->assertNotNull($earningMethod);
        $this->assertEquals('order_total', $earningMethod->type);
    }
}
