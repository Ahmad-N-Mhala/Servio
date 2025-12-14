<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryIntegration;
use App\Models\Restaurant;

class IntegrationController extends Controller
{
    public function index()
    {
        // Get all delivery integrations with restaurant info
        $integrations = DeliveryIntegration::with('restaurant')
            ->latest()
            ->paginate(20);

        return inertia('Admin/Integrations/Index', [
            'integrations' => $integrations,
        ]);
    }

    public function create()
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();

        return inertia('Admin/Integrations/Create', [
            'restaurants' => $restaurants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'provider' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'is_enabled' => 'boolean',
        ]);

        DeliveryIntegration::create($validated);

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Delivery provider added successfully');
    }

    public function edit(DeliveryIntegration $integration)
    {
        $restaurants = Restaurant::select(['id', 'name'])->get();

        return inertia('Admin/Integrations/Edit', [
            'integration' => $integration->load('restaurant'),
            'restaurants' => $restaurants,
        ]);
    }

    public function update(Request $request, DeliveryIntegration $integration)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'provider' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'is_enabled' => 'boolean',
        ]);

        $integration->update($validated);

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Delivery provider updated successfully');
    }

    public function destroy(DeliveryIntegration $integration)
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration deleted successfully');
    }
}
