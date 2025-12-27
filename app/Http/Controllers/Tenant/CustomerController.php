<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $query = Customer::where('restaurant_id', $restaurant->id)
            ->with(['loyaltyPoints'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(10)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search'])
        ]);
    }

    public function show(Customer $customer)
    {
        // Ensure customer belongs to tenant
        $restaurantId = (\App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first())->id;
        if ($customer->restaurant_id !== $restaurantId) {
            abort(403);
        }

        $customer->load(['loyaltyPoints']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'orders' => $customer->orders()->orderBy('created_at', 'desc')->paginate(10, ['*'], 'orders_page'),
            'transactions' => $customer->pointTransactions()->orderBy('created_at', 'desc')->get(), // might be long, but for now ok
            'redemptions' => $customer->rewardRedemptions()->with('reward')->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
