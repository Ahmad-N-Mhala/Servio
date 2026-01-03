<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function create($identifier)
    {
        $order = \App\Models\Order::where('feedback_token', $identifier)->first();
        $restaurant = null;

        if ($order) {
            $restaurant = $order->restaurant;

            if (\App\Models\Feedback::where('order_id', $order->id)->exists()) {
                return redirect()->route('public.feedback.create', ['identifier' => $restaurant->slug])
                    ->with('error', 'Feedback already submitted for this order.');
            }
        } else {
            // Try finding by ID first, then slug
            $restaurant = \App\Models\Restaurant::where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->firstOrFail();
        }

        return \Inertia\Inertia::render('Public/FeedbackForm', [
            'restaurant' => $restaurant->only(['id', 'name', 'logo', 'slug', 'google_map_location']),
            'settings' => $restaurant->settings ?? [],
            'order' => $order ? $order->only(['id', 'order_number', 'total']) : null,
            'customer' => $order && $order->customer ? $order->customer->only(['id', 'name']) : null,
            'order_id' => $order ? $order->id : request('order_id'),
            'customer_id' => $order ? $order->customer_id : request('customer_id'),
        ]);
    }

    public function store(\Illuminate\Http\Request $request, $identifier)
    {
        $order = \App\Models\Order::where('feedback_token', $identifier)->first();
        $restaurant = null;

        if ($order) {
            $restaurant = $order->restaurant;
        } else {
            $restaurant = \App\Models\Restaurant::where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->firstOrFail();
        }

        // Check for existing feedback if order token is used
        if ($order) {
            if (\App\Models\Feedback::where('order_id', $order->id)->exists()) {
                // If it's an API/JSON request return JSON, else redirect with error
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Feedback already submitted.'], 422);
                }
                return redirect()->back()->with('error', 'Feedback already submitted for this order.');
            }
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => $order ? 'nullable' : 'nullable|exists:orders,id',
            'customer_id' => $order ? 'nullable' : 'nullable|exists:customers,id',
            'redirected_to_google' => 'boolean'
        ]);

        $orderId = $order ? $order->id : ($validated['order_id'] ?? null);
        $customerId = $order ? $order->customer_id : ($validated['customer_id'] ?? null);

        $feedback = \App\Models\Feedback::create([
            'restaurant_id' => $restaurant->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'status' => 'pending',
            'redirected_to_google' => $validated['redirected_to_google'] ?? false,
        ]);



        // Logic check for Google Redirect
        $shouldRedirect = false;
        $redirectUrl = null;

        if ($validated['rating'] >= 4 && !empty($restaurant->google_map_location)) {
            $shouldRedirect = true;
            $redirectUrl = $restaurant->google_map_location;

            // Mark as redirected
            $feedback->update(['redirected_to_google' => true]);
        }

        // --- Loyalty Reward Logic ---
        $template = \App\Models\CommunicationTemplate::where('restaurant_id', $restaurant->id)
            ->where('trigger_event', 'order_completed_feedback')
            ->where('is_active', true)
            ->first();

        if ($template && !empty($template->conditions['feedback_points'])) {
            $points = (int) $template->conditions['feedback_points'];
            $customerId = $validated['customer_id'] ?? null;
            $orderId = $validated['order_id'] ?? null;

            if ($points > 0 && $customerId) {
                $customer = \App\Models\Customer::find($customerId);
                if ($customer) {
                    $loyaltyPoints = $customer->loyaltyPoints()->firstOrCreate(
                        ['customer_id' => $customer->id],
                        ['balance' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
                    );

                    $loyaltyPoints->addPoints(
                        $points,
                        'Reward for Feedback',
                        $orderId
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'redirect_url' => $redirectUrl,
            'should_redirect' => $shouldRedirect
        ]);
    }
}
