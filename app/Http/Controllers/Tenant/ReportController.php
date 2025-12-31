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

        return \Inertia\Inertia::render('Reports/Sales', [
            'salesData' => $dailySales,
            'stats' => [
                'total_revenue' => (float) (string) $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => (float) $averageOrderValue,
            ],
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
