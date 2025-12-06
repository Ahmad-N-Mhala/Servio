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
    public function index(): Response
    {
        $restaurant = \App\Models\Restaurant::first();

        $methods = EarningMethod::where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Loyalty/EarningMethods', [
            'methods' => $methods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:order_total,visit,referral,review'],
            'points' => ['required', 'integer', 'min:1'],
            'currency_amount' => ['nullable', 'required_if:type,order_total', 'numeric', 'min:0.01'],
            'min_spent' => ['nullable', 'numeric', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $restaurant = \App\Models\Restaurant::first();

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
            'type' => ['required', 'in:order_total,visit,referral,review'],
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
