<?php

namespace Tests\Feature;

use App\Models\ActionLog;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    // use RefreshDatabase;

    protected $restaurant;
    protected $order;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        \Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale('en');

        $this->restaurant = Restaurant::factory()->create();

        $this->customer = Customer::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'John Doe',
            'phone' => '+1234567890',
        ]);

        $this->order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-' . Str::random(10),
            'total_amount' => 50.00,
            'status' => 'completed',
            'feedback_token' => Str::random(32),
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->order) {
            Feedback::where('order_id', $this->order->id)->delete();
            $this->order->delete();
        }
        if ($this->customer) {
            $this->customer->delete();
        }
        if ($this->restaurant) {
            $this->restaurant->delete();
        }
        parent::tearDown();
    }

    public function test_feedback_page_loads_with_valid_token()
    {
        $response = $this->get(route('public.feedback.create', ['identifier' => $this->order->feedback_token]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Public/FeedbackForm')
                ->has('order', fn($prop) => $prop->where('id', $this->order->id)->etc())
                ->has('settings')
        );
    }

    public function test_feedback_page_loads_localized()
    {
        $response = $this->get('/en/feedback/' . $this->order->feedback_token);
        $response->assertStatus(200);
    }

    public function test_feedback_submission()
    {
        $response = $this->post(route('public.feedback.store', ['identifier' => $this->order->feedback_token]), [
            'rating' => 5,
            'comment' => 'Great food!',
            'redirected_to_google' => true
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('feedback', [
            'order_id' => $this->order->id,
            'restaurant_id' => $this->restaurant->id,
            'rating' => 5,
            'comment' => 'Great food!',
            'redirected_to_google' => true
        ]);
    }

    public function test_feedback_submission_validates_rating()
    {
        $response = $this->post(route('public.feedback.store', ['identifier' => $this->order->feedback_token]), [
            'rating' => 6, // Invalid > 5
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_invalid_token_returns_404()
    {
        $response = $this->get(route('public.feedback.create', ['identifier' => 'invalid-token-xyz']));
        $response->assertStatus(404);
    }

    public function test_already_submitted_feedback_returns_error_or_redirect()
    {
        // First submission
        Feedback::create([
            'restaurant_id' => $this->restaurant->id,
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'rating' => 5,
            'comment' => 'First time',
        ]);

        // Try accessing page again
        $response = $this->get(route('public.feedback.create', ['identifier' => $this->order->feedback_token]));

        // Should probably redirect or show "already submitted"
        $response->assertStatus(302); // assuming we redirect back or to a thank you page if already done
        $response->assertSessionHas('error');
    }
}
