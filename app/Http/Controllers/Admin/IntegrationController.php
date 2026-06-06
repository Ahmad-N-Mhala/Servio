<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryIntegration;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryIntegration::with('restaurant');

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', '%'.$search.'%')
                    ->orWhere('api_key', 'like', '%'.$search.'%')
                    ->orWhereHas('restaurant', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('sort_field')) {
            $sortField = $request->input('sort_field');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        return inertia('Admin/Integrations/Index', [
            'integrations' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
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
            'store_id' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
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
            'provider' => 'required|string|max:255',
            'store_id' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
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
