<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::query();

        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('slug', 'like', '%' . $request->input('search') . '%');
        }

        $plans = $query->orderBy('order')->orderBy('price_monthly')->paginate(20)->withQueryString();

        return inertia('Admin/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Plans/Create', [
            'availableFeatures' => config('features'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'enabled_features' => 'nullable|array',
            'max_restaurants' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Key generation
        $slug = $validated['slug'];
        $validated['name'] = $validated['name_en'];
        $validated['description'] = $validated['description_en'];

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully');
    }

    public function edit(Plan $plan)
    {
        return inertia('Admin/Plans/Edit', [
            'plan' => $plan,
            'availableFeatures' => config('features'),
            'translations' => [
                'name_en' => $plan->name_en ?? $plan->name,
                'name_ar' => $plan->name_ar ?? $plan->name,
                'description_en' => $plan->description_en ?? $plan->description,
                'description_ar' => $plan->description_ar ?? $plan->description,
            ]
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'enabled_features' => 'nullable|array',
            'max_restaurants' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $slug = $validated['slug'];
        $validated['name'] = $validated['name_en'];
        $validated['description'] = $validated['description_en'];

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully');
    }

    public function destroy(Plan $plan)
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = $plan->restaurantSubscriptions()->where('status', 'active')->count();

        if ($activeSubscriptions > 0) {
            return back()->withErrors(['error' => 'Cannot delete plan with active subscriptions']);
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully');
    }
}
