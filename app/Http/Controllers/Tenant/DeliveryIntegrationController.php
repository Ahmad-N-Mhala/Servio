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
        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Get existing integrations
        $integrations = DeliveryIntegration::where('restaurant_id', $restaurant->id)->get();

        // Fetch active delivery providers
        $providers = \App\Models\DeliveryProvider::active()
            ->ordered()
            ->get()
            ->map(function ($provider) {
                return [
                    'id' => $provider->slug, // Using slug as ID for integration linking
                    'name' => $provider->name,
                    'logo_url' => $provider->logo_url,
                    'description' => $provider->description,
                    'api_documentation_url' => $provider->api_documentation_url,
                    'requires_api_key' => $provider->requires_api_key,
                    'requires_api_secret' => $provider->requires_api_secret,
                    'requires_store_id' => $provider->requires_store_id,
                    'requires_webhook_secret' => $provider->requires_webhook_secret,
                    'requires_client_id' => $provider->requires_client_id,
                    'requires_client_secret' => $provider->requires_client_secret,
                    'requires_username' => $provider->requires_username,
                    'requires_password' => $provider->requires_password,
                    'webhook_url_template' => $provider->webhook_url_template,
                ];
            });

        return Inertia::render('Integrations/Delivery', [
            'providers' => $providers,
            'integrations' => $integrations->keyBy('provider'), // Key by provider slug
        ]);
    }

    public function update(Request $request)
    {
        // 1. Validate Provider Slug
        $request->validate(['provider' => 'required|string']);

        // 2. Fetch Provider Definition
        $providerDef = \App\Models\DeliveryProvider::where('slug', $request->provider)->firstOrFail();

        // 3. Dynamic Validation Rules based on Provider Requirements
        $rules = [
            'is_enabled' => 'boolean',
        ];

        if ($providerDef->requires_store_id)
            $rules['store_id'] = 'required|string';
        if ($providerDef->requires_api_key)
            $rules['api_key'] = 'required|string';
        if ($providerDef->requires_api_secret)
            $rules['api_secret'] = 'required|string';
        if ($providerDef->requires_client_id)
            $rules['client_id'] = 'required|string';
        if ($providerDef->requires_client_secret)
            $rules['client_secret'] = 'required|string';
        if ($providerDef->requires_username)
            $rules['username'] = 'required|string';
        if ($providerDef->requires_password)
            $rules['password'] = 'required|string';
        if ($providerDef->requires_webhook_secret)
            $rules['webhook_secret'] = 'nullable|string'; // Often provided BY the platform, so might be input or output

        $validated = $request->validate($rules);

        // 4. Verification Step (Simulated)
        // In a real application, you would call the provider's API (e.g. /ping or /auth) to verify credentials.
        if ($request->is_enabled) {
            $verificationResult = $this->verifyProviderCredentials($providerDef, $request->all());

            if (!$verificationResult['success']) {
                return back()->withErrors(['api_key' => 'Connection failed: ' . $verificationResult['message']]);
            }
        }

        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $integration = DeliveryIntegration::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'provider' => $request->provider,
            ],
            [
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'store_id' => $request->store_id,
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'username' => $request->username,
                'password' => $request->password,
                'webhook_secret' => $request->webhook_secret,
                'is_enabled' => $request->is_enabled,
                'settings' => $request->settings ?? [],
            ]
        );

        return back()->with('success', 'Integration settings verified and saved successfully.');
    }

    /**
     * Simulate verification of credentials with 3rd party provider
     */
    private function verifyProviderCredentials($provider, $data)
    {
        // For demonstration/demo purposes:
        // Fail if any credential field actually contains the word "fail"
        // This allows the user to test the error handling UI.

        $fieldsToCheck = ['api_key', 'api_secret', 'client_id', 'client_secret', 'username', 'password', 'store_id'];

        foreach ($fieldsToCheck as $field) {
            if (isset($data[$field]) && str_contains(strtolower($data[$field] ?? ''), 'fail')) {
                return ['success' => false, 'message' => "Invalid {$field} provided (Simulated Error)"];
            }
        }

        // Real logic would go here:
        // Http::withToken($data['api_key'])->get($provider->base_url . '/check');

        return ['success' => true];
    }

    public function destroy(Request $request, $provider)
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $integration = DeliveryIntegration::where('restaurant_id', $restaurant->id)
            ->where('provider', $provider)
            ->first();

        if ($integration) {
            $integration->delete();
            return back()->with('success', 'Integration disconnected successfully.');
        }

        return back()->with('error', 'Integration not found.');
    }
    public function pushMenu(Request $request, $provider)
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        $integration = DeliveryIntegration::where('restaurant_id', $restaurant->id)
            ->where('provider', $provider)
            ->first();

        if (!$integration || !$integration->is_enabled) {
            return back()->with('error', 'Integration is not active.');
        }

        // Simulate Menu Push
        // In reality, this would Dispatch a Job: PushMenuToProvider::dispatch($restaurant, $integration);
        // And we would verify keys, etc.

        // Simulated Delay and Random Success/Fail (mostly success)
        sleep(1); // Simulate network call

        $simulatedSuccess = true;
        // if (rand(0, 10) > 8) $simulatedSuccess = false; // Optional random failure for testing user handling

        if ($simulatedSuccess) {
            // Store the "Last Pushed" time in settings
            $settings = $integration->settings ?? [];
            $settings['last_menu_push'] = now()->toIso8601String();
            $integration->settings = $settings;
            $integration->save();

            return back()->with('success', "Menu successfully pushed to {$provider}!");
        } else {
            return back()->with('error', "Failed to push menu to {$provider}. Please check credentials and try again.");
        }
    }
}
