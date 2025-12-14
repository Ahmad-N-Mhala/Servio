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

        // Default providers we support
        $providers = [
            [
                'id' => 'noon',
                'name' => 'Noon Food',
                'logo' => '/images/integrations/noon-food.png',
                'description' => 'Integrate with Noon Food for seamless order management.',
            ],
            [
                'id' => 'talabat',
                'name' => 'Talabat',
                'logo' => '/images/integrations/talabat.png',
                'description' => 'Connect your restaurant with Talabat delivery network.',
            ],
            [
                'id' => 'deliveroo',
                'name' => 'Deliveroo',
                'logo' => '/images/integrations/deliveroo.png',
                'description' => 'Sync menus and orders with Deliveroo.',
            ],
            [
                'id' => 'careem',
                'name' => 'Careem Now',
                'logo' => '/images/integrations/careem.png',
                'description' => 'Manage Careem orders directly from your dashboard.',
            ]
        ];

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
