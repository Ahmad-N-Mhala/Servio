<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MonthlyExpense;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        // Enforce strict context - removed implicit fallback for super admin
        if (!$restaurant && $request->user()->is_super_admin && session('active_restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        }

        if (!$restaurant) {
            return redirect()->route('restaurants.selection')->with('error', 'Please select a restaurant to view financials.');
        }

        $tab = $request->input('tab', 'expenses');

        // Monthly Expenses Data
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        $expenses = MonthlyExpense::where('restaurant_id', $restaurant->id)
            ->where('month', $selectedMonth)
            ->orderBy('category')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'category' => $expense->category,
                    'description' => $expense->description,
                    'amount' => (float) (string) $expense->amount,
                    'payment_status' => $expense->payment_status,
                    'paid_at' => $expense->paid_at?->format('Y-m-d'),
                    'notes' => $expense->notes,
                ];
            });

        $totalExpenses = $expenses->sum('amount');
        $paidExpenses = $expenses->where('payment_status', 'paid')->sum('amount');
        $pendingExpenses = $expenses->where('payment_status', 'pending')->sum('amount');

        $availableMonths = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $availableMonths->push([
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ]);
        }

        // Sales Reports Data
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $dailySales = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })
            ->map(function ($dayOrders, $date) {
                return [
                    'date' => $date,
                    'total' => (float) (string) $dayOrders->sum('total'),
                    'count' => $dayOrders->count(),
                ];
            })
            ->values()
            ->sortBy('date')
            ->values();

        $totalRevenue = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? (float) (string) $totalRevenue / $totalOrders : 0;

        return Inertia::render('Financial/Index', [
            'activeTab' => $tab,
            // Monthly Expenses
            'expenses' => $expenses,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'summary' => [
                'total' => $totalExpenses,
                'paid' => $paidExpenses,
                'pending' => $pendingExpenses,
            ],
            'categories' => $this->getExpenseCategories(),
            // Sales Reports
            'salesData' => $dailySales,
            'stats' => [
                'total_revenue' => (float) (string) $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => (float) $averageOrderValue,
            ],
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    private function getExpenseCategories(): array
    {
        return [
            'Rent',
            'Salaries',
            'Utilities',
            'Supplies',
            'Marketing',
            'Insurance',
            'Maintenance',
            'Equipment',
            'Licenses & Permits',
            'Other',
        ];
    }
}
