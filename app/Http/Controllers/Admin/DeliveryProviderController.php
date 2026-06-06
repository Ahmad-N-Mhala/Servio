<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\DeliveryProvider::query();

        if ($request->input('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        $providers = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($provider) {
                // Manually map count
                $provider->integrations_count = $provider->integrations()->count();

                return $provider;
            });

        $stats = [
            'total_providers' => \App\Models\DeliveryProvider::count(),
            'active_providers' => \App\Models\DeliveryProvider::where('is_active', true)->count(),
            // Count total integrations directly from the Integration model (more efficient)
            'total_integrations' => \App\Models\DeliveryIntegration::count(),
        ];

        return inertia('Admin/DeliveryProviders/Index', [
            'providers' => $providers,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Admin/DeliveryProviders/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:delivery_providers,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // File upload
            'api_documentation_url' => 'nullable|url|max:500',
            'requires_api_key' => 'boolean',
            'requires_api_secret' => 'boolean',
            'requires_client_id' => 'boolean',
            'requires_client_secret' => 'boolean',
            'requires_username' => 'boolean',
            'requires_password' => 'boolean',
            'requires_store_id' => 'boolean',
            'requires_webhook_secret' => 'boolean',
            'configuration_fields' => 'nullable|array',
            'webhook_url_template' => 'nullable|string',
            'supported_webhook_events' => 'nullable|array',
            'api_settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle File Upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('delivery-providers', 'public');
            $validated['logo_url'] = $path;
        }

        unset($validated['logo']); // Remove file object from data array

        DeliveryProvider::create($validated);

        return redirect()->route('admin.delivery-providers.index')
            ->with('success', 'Delivery provider created successfully');
    }

    public function edit(DeliveryProvider $deliveryProvider)
    {
        $deliveryProvider->integrations_count = $deliveryProvider->integrations()->count();

        return inertia('Admin/DeliveryProviders/Edit', [
            'provider' => $deliveryProvider,
        ]);
    }

    public function update(Request $request, DeliveryProvider $deliveryProvider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:delivery_providers,slug,'.$deliveryProvider->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // Allow file upload
            'logo_url' => 'nullable|string|max:500', // Allow keeping existing URL
            'api_documentation_url' => 'nullable|url|max:500',
            'requires_api_key' => 'boolean',
            'requires_api_secret' => 'boolean',
            'requires_client_id' => 'boolean',
            'requires_client_secret' => 'boolean',
            'requires_username' => 'boolean',
            'requires_password' => 'boolean',
            'requires_store_id' => 'boolean',
            'requires_webhook_secret' => 'boolean',
            'webhook_url_template' => 'nullable|string',
            'configuration_fields' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle File Upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('delivery-providers', 'public');
            $validated['logo_url'] = $path;
        }

        unset($validated['logo']); // Remove file object

        $deliveryProvider->update($validated);

        return redirect()->route('admin.delivery-providers.index')
            ->with('success', 'Delivery provider updated successfully');
    }

    public function destroy(DeliveryProvider $deliveryProvider)
    {
        // Check if provider is being used
        $integrationsCount = $deliveryProvider->integrations()->count();

        if ($integrationsCount > 0) {
            return back()->with('error', "Cannot delete this provider. It is currently being used by {$integrationsCount} restaurant(s).");
        }

        $deliveryProvider->delete();

        return redirect()->route('admin.delivery-providers.index')
            ->with('success', 'Delivery provider deleted successfully');
    }

    /**
     * Toggle provider active status
     */
    public function toggleStatus(DeliveryProvider $deliveryProvider)
    {
        $deliveryProvider->update([
            'is_active' => ! $deliveryProvider->is_active,
        ]);

        $status = $deliveryProvider->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Provider {$status} successfully");
    }
}
