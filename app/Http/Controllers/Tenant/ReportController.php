<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; // Added this line

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = session('active_restaurant_id');

        // Date Range (Default: Last 30 Days)
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        // Daily Sales Chart Data
        $dailySales = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Summary Stats
        $totalRevenue = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return \Inertia\Inertia::render('Reports/Sales', [
            'salesData' => $dailySales,
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $averageOrderValue,
            ],
            'filters' => [
                'start_date' => $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate,
                'end_date' => $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate,
            ]
        ]);
    }
}
