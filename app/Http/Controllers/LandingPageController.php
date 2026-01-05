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

        $contactEmail = LandingSetting::get('contact_email', 'admin@demo.com'); // Default fallback

        // Send Email
        try {
            Mail::to($contactEmail)->send(new RegistrationInterest($request->all()));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email. Please try again.');
        }

        return back()->with('success', 'thank_you_message');
    }
}
