<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::first();

        // Get date range from request or default to last 7 days
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        // Get real statistics with date filtering
        $totalOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $todayOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereDate('created_at', today())
            ->count();

        $revenue = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $activeStaff = Staff::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->count();

        // Recent orders
        $recentOrders = Order::with(['items', 'customer'])
            ->where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // Revenue chart data (daily breakdown)
        $revenueChart = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue,
                ];
            });

        // Order status distribution
        $statusDistribution = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                ];
            });

        // Peak hours analysis (hourly order counts)
        $peakHours = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => (int) $item->hour,
                    'count' => $item->count,
                ];
            });

        // Top menu items
        $topMenuItems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->where('orders.status', '!=', 'deleted')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'menu_items.id',
                DB::raw("menu_items.name->>'en' as item_name"),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('menu_items.id', DB::raw("menu_items.name->>'en'"))
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->item_name ?? 'Unknown',
                    'quantity' => (int) $item->total_quantity,
                ];
            });

        return Inertia::render('Dashboard/Home', [
            'stats' => [
                'total_orders' => $totalOrders,
                'today_orders' => $todayOrders,
                'revenue' => (float) $revenue,
                'active_staff' => $activeStaff,
            ],
            'recent_orders' => $recentOrders,
            'revenue_chart' => $revenueChart,
            'status_distribution' => $statusDistribution,
            'peak_hours' => $peakHours,
            'top_menu_items' => $topMenuItems,
            'avg_completion_time' => $this->getAverageCompletionTimeChart($restaurant->id, $startDate, $endDate),
            'date_range' => [
                'start_date' => is_string($startDate) ? $startDate : $startDate->format('Y-m-d'),
                'end_date' => is_string($endDate) ? $endDate : $endDate->format('Y-m-d'),
            ],
        ]);
    }

    private function getAverageCompletionTimeChart($restaurantId, $startDate, $endDate)
    {
        return Order::where('restaurant_id', $restaurantId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (completed_at - created_at))/60) as avg_minutes')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'minutes' => round((float) $item->avg_minutes, 1),
                ];
            });
    }
}

