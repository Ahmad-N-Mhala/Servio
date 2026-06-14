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
        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

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

        if ($request->has('export') && $request->export === 'excel') {
            $allCustomers = $query->get();
            foreach ($allCustomers as $customer) {
                $customer->orders_count = $customer->orders()->where('payment_status', 'paid')->count();
            }
            return $this->exportToExcel($allCustomers);
        }

        $customers = $query->paginate(10)->withQueryString();

        // Calculate order count manually for the current page to avoid MongoDB withCount limitations
        $customers->getCollection()->transform(function ($customer) {
            $customer->orders_count = $customer->orders()->where('payment_status', 'paid')->count();

            return $customer;
        });

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search']),
        ]);
    }

    private function exportToExcel($customers)
    {
        $locale = app()->getLocale();
        $csvData = [];
        $csvData[] = ['Name', 'Phone', 'Email', 'Member Since', 'Total Orders', 'Points Balance', 'Total Spent'];

        foreach ($customers as $customer) {
            $name = $customer->name;
            if (is_array($name)) {
                $resolvedName = $name[$locale] ?? ($name['en'] ?? (reset($name) ?: 'Unknown'));
            } elseif (is_object($name)) {
                $nameArr = (array) $name;
                $resolvedName = $nameArr[$locale] ?? ($nameArr['en'] ?? (reset($nameArr) ?: 'Unknown'));
            } else {
                $resolvedName = $name ?: 'Unknown';
            }

            $csvData[] = [
                $resolvedName,
                $customer->phone ?: '-',
                $customer->email ?: '-',
                $customer->created_at ? $customer->created_at->format('Y-m-d') : '-',
                $customer->orders_count ?? 0,
                $customer->loyaltyPoints?->balance ?? 0,
                $customer->total_spent ?? 0.00,
            ];
        }

        $filename = 'customers_report_'.date('Y_m_d_H_i_s').'.csv';
        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = chr(0xEF) . chr(0xBB) . chr(0xBF) . stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    public function show(Customer $customer)
    {
        // Ensure customer belongs to tenant (Bypass for Super Admin)
        $restaurantId = session('active_restaurant_id');
        if (! auth()->user()->is_super_admin && (string) $customer->restaurant_id !== (string) $restaurantId) {
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
