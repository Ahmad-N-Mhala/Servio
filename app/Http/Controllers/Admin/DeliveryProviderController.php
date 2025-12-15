<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryProvider;
use Illuminate\Support\Str;

class DeliveryProviderController extends Controller
{
    public function index()
    {
        $providers = DeliveryProvider::withCount('integrations')
            ->ordered()
            ->paginate(20);

        return inertia('Admin/DeliveryProviders/Index', [
            'providers' => $providers,
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
            'logo_url' => 'nullable|string|max:500',
            'api_documentation_url' => 'nullable|url|max:500',
            'requires_api_key' => 'boolean',
            'requires_api_secret' => 'boolean',
            'requires_store_id' => 'boolean',
            'requires_webhook_secret' => 'boolean',
            'configuration_fields' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        DeliveryProvider::create($validated);

        return redirect()->route('admin.delivery-providers.index')
            ->with('success', 'Delivery provider created successfully');
    }

    public function edit(DeliveryProvider $deliveryProvider)
    {
        return inertia('Admin/DeliveryProviders/Edit', [
            'provider' => $deliveryProvider->loadCount('integrations'),
        ]);
    }

    public function update(Request $request, DeliveryProvider $deliveryProvider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:delivery_providers,slug,' . $deliveryProvider->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|max:500',
            'api_documentation_url' => 'nullable|url|max:500',
            'requires_api_key' => 'boolean',
            'requires_api_secret' => 'boolean',
            'requires_store_id' => 'boolean',
            'requires_webhook_secret' => 'boolean',
            'configuration_fields' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

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
            'is_active' => !$deliveryProvider->is_active
        ]);

        $status = $deliveryProvider->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Provider {$status} successfully");
    }
}
