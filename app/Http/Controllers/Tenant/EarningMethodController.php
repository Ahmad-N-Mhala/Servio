<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\EarningMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EarningMethodController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $query = EarningMethod::where('restaurant_id', $restaurant->id);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name.en', 'like', "%{$search}%")
                    ->orWhere('name.ar', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('points', (int) $search);
                }
            });
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['name', 'type', 'points', 'is_active', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'name') {
                $query->orderBy('name.en', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $methods = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('Loyalty/EarningMethods', [
            'methods' => $methods,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
            'settings' => $restaurant->settings ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:order_total,visit'],
            'points' => ['required', 'integer', 'min:1'],
            'currency_amount' => ['nullable', 'required_if:type,order_total', 'numeric', 'min:0.01'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        // Enforce single earning method
        if (EarningMethod::where('restaurant_id', $restaurant->id)->exists()) {
            return redirect()->back()->withErrors(['error' => 'Each store can have only one earning method. Please edit the existing one.']);
        }

        EarningMethod::create(array_merge($validated, [
            'restaurant_id' => $restaurant->id,
        ]));

        return redirect()->back()->with('message', 'Earning method created successfully.');
    }

    public function update(Request $request, EarningMethod $earningMethod)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:order_total,visit'],
            'points' => ['required', 'integer', 'min:1'],
            'currency_amount' => ['nullable', 'required_if:type,order_total', 'numeric', 'min:0.01'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $earningMethod->update($validated);

        return redirect()->back()->with('message', 'Earning method updated successfully.');
    }

    public function destroy(EarningMethod $earningMethod)
    {
        $earningMethod->delete();

        return redirect()->back()->with('message', 'Earning method deleted successfully.');
    }
}
