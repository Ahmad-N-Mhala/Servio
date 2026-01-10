<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = session('active_restaurant_id');
        if (!$restaurantId)
            abort(404, 'Restaurant context not found');

        // Date Range (Default: Last 30 Days)
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        // Normalize dates - make range inclusive
        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();  // Include full start day (00:00:00)
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();  // Include full end day (23:59:59)

        // Daily Sales Chart Data - Process in PHP
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'deleted')
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

        // Summary Stats
        $totalRevenue = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? (float) (string) $totalRevenue / $totalOrders : 0;

        // Payment Stats (Full dataset for period)
        $statsOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'deleted')
            ->get(); // Need collection for grouping/summing

        $paymentStats = [
            'cash' => (float) (string) $statsOrders->where('payment_method', 'cash')->sum('total'),
            'card' => (float) (string) $statsOrders->where('payment_method', 'card')->sum('total'),
            'online' => (float) (string) $statsOrders->where('payment_method', 'online')->sum('total'),
            'total' => (float) (string) $statsOrders->sum('total'),
        ];

        // Payment History (Paginated & Filtered)
        $paymentQuery = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->with(['waiter', 'table']);

        // 1. Filter by Payment Method
        if ($request->has('payment_method') && $request->payment_method) {
            $paymentQuery->where('payment_method', $request->payment_method);
        }

        // 2. Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $paymentQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");

                // Attempt searching relations if possible, but simplest is keeping it direct for now.
                // Mongo 'like' is essentially regex.
            });
        }

        // 3. Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allow sorting by specific columns
        if (in_array($sortField, ['created_at', 'total', 'order_number', 'payment_method'])) {
            $paymentQuery->orderBy($sortField, $sortDirection);
        } else {
            $paymentQuery->orderBy('created_at', 'desc');
        }

        $paymentHistory = $paymentQuery->paginate(10)
            ->withQueryString();

        return \Inertia\Inertia::render('Reports/Sales', [
            'salesData' => $dailySales,
            'paymentStats' => $paymentStats,
            'paymentHistory' => $paymentHistory,
            'stats' => [
                'total_revenue' => (float) (string) $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => (float) $averageOrderValue,
            ],
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'search' => $request->search ?? '',
                'payment_method' => $request->payment_method ?? null,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    public function export(Request $request)
    {
        $restaurantId = session('active_restaurant_id');
        if (!$restaurantId)
            abort(404, 'Restaurant context not found');

        $restaurant = \App\Models\Restaurant::find($restaurantId);

        // Date Range
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();

        $type = $request->input('type', 'sales'); // 'sales' or 'payments'

        // Determine Timezone
        $country = \App\Models\Country::where('name', $restaurant->country)->first();
        $timezone = $country && $country->timezone ? $country->timezone : config('app.timezone');

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$type}_report_" . now()->format('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($restaurantId, $startDate, $endDate, $type, $timezone) {
            $file = fopen('php://output', 'w');

            if ($type === 'sales') {
                // Daily Sales Report
                fputcsv($file, [
                    __('reports.date'),
                    __('reports.total_orders'),
                    __('reports.total_revenue')
                ]);

                $orders = Order::where('restaurant_id', $restaurantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'deleted')
                    ->get()
                    ->groupBy(function ($order) {
                        return $order->created_at->format('Y-m-d');
                    });

                foreach ($orders as $date => $dayOrders) {
                    fputcsv($file, [
                        $date,
                        $dayOrders->count(),
                        $dayOrders->sum('total')
                    ]);
                }
            } else {
                // Payment History Report
                fputcsv($file, [
                    __('reports.date_time'),
                    __('reports.order') . ' #',
                    __('pos.table'),
                    __('orders.customer'),
                    __('reports.waiter'),
                    __('reports.payment_methods'),
                    __('reports.amount')
                ]);

                Order::where('restaurant_id', $restaurantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'deleted')
                    ->with(['waiter', 'table'])
                    ->orderBy('created_at', 'desc')
                    ->chunk(100, function ($orders) use ($file, $timezone) {
                        foreach ($orders as $order) {
                            fputcsv($file, [
                                $order->created_at->setTimezone($timezone)->format('Y-m-d H:i:s'),
                                $order->order_number,
                                $order->table ? $order->table->name : __('reports.takeaway'),
                                $order->customer_name ?: __('reports.guest'),
                                $order->waiter ? $order->waiter->name : '-',
                                __('reports.' . strtolower($order->payment_method ?? 'unknown')) ?: ucfirst($order->payment_method),
                                $order->total
                            ]);
                        }
                    });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
