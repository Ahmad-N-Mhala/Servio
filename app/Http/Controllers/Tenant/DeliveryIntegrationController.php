<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DeliveryIntegration;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeliveryIntegrationController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::first();

        $integrations = DeliveryIntegration::where('restaurant_id', $restaurant->id)->get();

        // Fetch active delivery providers from database (single source of truth)
        // This ensures admin-managed providers are immediately available to tenants
        $providers = \App\Models\DeliveryProvider::active()
            ->ordered()
            ->get()
            ->map(function ($provider) {
                return [
                    'id' => $provider->slug,
                    'name' => $provider->name,
                    'logo' => $provider->logo_url,
                    'description' => $provider->description,
                    'api_documentation_url' => $provider->api_documentation_url,
                    'requires_api_key' => $provider->requires_api_key,
                    'requires_api_secret' => $provider->requires_api_secret,
                    'requires_store_id' => $provider->requires_store_id,
                    'requires_webhook_secret' => $provider->requires_webhook_secret,
                    'configuration_fields' => $provider->configuration_fields,
                ];
            });

        return Inertia::render('Integrations/Delivery', [
            'providers' => $providers,
            'integrations' => $integrations->keyBy('provider'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'store_id' => 'nullable|string',
            'is_enabled' => 'boolean',
        ]);

        $restaurant = Restaurant::first();

        $integration = DeliveryIntegration::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'provider' => $request->provider,
            ],
            [
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret, // In real app, encrypt this
                'store_id' => $request->store_id,
                'is_enabled' => $request->is_enabled,
                'settings' => $request->settings ?? [],
            ]
        );

        return back()->with('success', 'Integration settings saved successfully.');
    }
}
