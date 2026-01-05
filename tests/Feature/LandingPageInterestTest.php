<?php

namespace Tests\Feature;

use App\Mail\RegistrationInterest;
use App\Models\LandingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LandingPageInterestTest extends TestCase
{
    // use RefreshDatabase; // Disabled due to MongoDB transaction requirement

    protected function tearDown(): void
    {
        LandingSetting::whereIn('key', ['contact_email'])->delete();
        parent::tearDown();
    }

    public function test_user_can_submit_interest_form()
    {
        Mail::fake();

        // Set a contact email
        LandingSetting::set('contact_email', 'admin@example.com');

        $response = $this->post(route('register.interest'), [
            'plan_id' => 1,
            'plan_name' => 'Pro Plan',
            'name' => 'Test User',
            'email' => 'test@user.com',
            'phone' => '123456789',
            'restaurant_name' => 'Test Resto',
            'message' => 'I want to join.',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        Mail::assertQueued(RegistrationInterest::class, function ($mail) {
            return $mail->hasTo('admin@example.com') &&
                $mail->data['email'] === 'test@user.com';
        });
    }

    public function test_submission_fails_validation()
    {
        $response = $this->post(route('register.interest'), [
            'name' => 'Test User',
            // Missing required fields
        ]);

        $response->assertSessionHasErrors(['email', 'phone', 'plan_id']);
    }

    public function test_uses_default_email_if_setting_missing()
    {
        Mail::fake();

        // Ensure no setting exists
        LandingSetting::where('key', 'contact_email')->delete();

        $this->post(route('register.interest'), [
            'plan_id' => 1,
            'plan_name' => 'Basic',
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '123123',
            'restaurant_name' => 'Resto',
        ]);

        Mail::assertQueued(RegistrationInterest::class, function ($mail) {
            // Default usually 'admin@demo.com' defined in controller or via config
            return $mail->hasTo('admin@demo.com');
        });
    }
}
