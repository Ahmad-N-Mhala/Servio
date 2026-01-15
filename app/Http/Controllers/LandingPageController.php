<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationInterest;
use App\Models\DeliveryProvider;
use App\Models\LandingModule;
use App\Models\LandingSetting;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        // Increment landing page visits
        $currentVisits = LandingSetting::get('landing_page_visits', ['count' => 0]);
        $newCount = ($currentVisits['count'] ?? 0) + 1;
        LandingSetting::set('landing_page_visits', ['count' => $newCount]);

        $plans = Plan::where('is_active', true)->get();

        $modules = LandingModule::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $deliveryProviders = DeliveryProvider::active()
            ->ordered()
            ->select('id', 'name', 'logo_url')
            ->get();

        $settings = LandingSetting::all()->mapWithKeys(function ($item) {
            return [$item->key => $item->value];
        });

        $screenshots = \App\Models\LandingScreenshot::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Welcome', [
            'plans' => $plans,
            'modules' => $modules,
            'deliveryProviders' => $deliveryProviders,
            'landingSettings' => $settings,
            'screenshots' => $screenshots,
        ]);
    }

    public function registerInterest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required', // ID or slug
            'plan_name' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'restaurant_name' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $contactEmail = \App\Models\SystemConfiguration::get('registration_email')
            ?? LandingSetting::get('interest_notification_email')
            ?? LandingSetting::get('contact_email', 'support@kenildock.com');

        // Send Email
        try {
            \Log::info("Attempting to send Registration Interest email to: " . $contactEmail);
            Mail::to($contactEmail)->send(new RegistrationInterest($request->all()));
            \Log::info("Registration Interest email sent successfully.");
        } catch (\Exception $e) {
            \Log::error('Registration Interest Email Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString()); // Log trace
            return back()->with('error', 'Failed to send email. Please try again or contact us directly.');
        }

        return back()->with('success', 'thank_you_message');
    }
}
