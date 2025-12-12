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
                DB::raw('CAST(menu_items.name AS TEXT) as name'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('menu_items.id', DB::raw('CAST(menu_items.name AS TEXT)'))
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                // Decode name if it's JSON string
                $name = $item->name;
                if (is_string($name) && str_starts_with($name, '{')) {
                    $decoded = json_decode($name, true);
                    $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                }

                return [
                    'name' => $name,
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

    public function getDetails(Request $request)
    {
        $restaurant = Restaurant::first();
        $type = $request->input('type');
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        // Data to return
        $columns = [];
        $data = [];
        $title = '';

        switch ($type) {
            case 'total_orders':
            case 'today_orders':
                $title = 'Orders';
                $query = Order::with('customer')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('status', '!=', 'deleted');

                if ($type === 'today_orders') {
                    $query->whereDate('created_at', today());
                } else {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }

                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'total', 'label' => 'Total', 'format' => 'currency'],
                    ['key' => 'status', 'label' => 'Status', 'format' => 'status'],
                    ['key' => 'created_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = $query->orderByDesc('created_at')->limit(50)->get()->map(function ($order) {
                    return [
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'total' => $order->total,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                    ];
                });
                break;

            case 'revenue':
                $title = 'Revenue Details';
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'payment_method', 'label' => 'Payment Method'], // Assuming this column exists or similar
                    ['key' => 'created_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'payment_method' => $order->payment_method ?? 'Cash', // Fallback
                            'created_at' => $order->created_at,
                        ];
                    });
                break;

            case 'active_staff':
                $title = 'Active Staff';
                $columns = [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'role', 'label' => 'Role'],
                ];

                $data = Staff::where('restaurant_id', $restaurant->id)
                    ->where('is_active', true)
                    ->get()
                    ->map(function ($staff) {
                        return [
                            'name' => $staff->name,
                            'email' => $staff->email,
                            'role' => $staff->role,
                        ];
                    });
                break;

            // Chart drilldowns
            case 'revenue_chart_point':
                $date = $request->input('date');
                $title = "Revenue for " . $date;
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'created_at' => $order->created_at,
                        ];
                    });
                break;

            case 'status_slice':
                $status = $request->input('status');
                $title = "Orders with status: " . ucfirst($status);
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'total', 'label' => 'Total', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', $status)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name,
                            'total' => $order->total,
                            'created_at' => $order->created_at,
                        ];
                    });
                break;

            case 'top_item_bar':
                // Note: Getting filtered orders for a specific menu item is complex with the current setup if we don't have the ID passed cleanly
                // Ideally we pass the menu_item_id.
                // For now, I'll skip deep filtering for this one or iterate later.
                break;
        }

        return response()->json([
            'title' => $title,
            'columns' => $columns,
            'data' => $data
        ]);
    }
}
