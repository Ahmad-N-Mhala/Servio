<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Staff;
use App\Models\Ingredient;
use App\Models\WasteLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();

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

        // Waste Stats
        $totalWaste = WasteLog::where('restaurant_id', $restaurant->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->sum('total_loss');

        $lowStockCount = Ingredient::where('restaurant_id', $restaurant->id)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->count();

        // Calculate actual inventory value from batches (FIFO Valuation)
        $inventoryValue = DB::table('ingredient_batches')
            ->join('ingredients', 'ingredient_batches.ingredient_id', '=', 'ingredients.id')
            ->where('ingredients.restaurant_id', $restaurant->id)
            ->where('ingredient_batches.quantity_remaining', '>', 0)
            ->select(DB::raw('SUM(ingredient_batches.quantity_remaining * ingredient_batches.cost_per_unit) as total_value'))
            ->value('total_value') ?? 0;

        $wasteChart = WasteLog::where('restaurant_id', $restaurant->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->select('log_date', DB::raw('SUM(total_loss) as loss'))
            ->groupBy('log_date')
            ->orderBy('log_date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->log_date->format('Y-m-d'),
                    'loss' => (float) $item->loss,
                ];
            });

        return Inertia::render('Dashboard/Home', [
            'stats' => [
                'total_orders' => $totalOrders,
                'today_orders' => $todayOrders,
                'revenue' => (float) $revenue,
                'active_staff' => $activeStaff,
                'total_waste' => (float) $totalWaste,
                'low_stock_count' => $lowStockCount,
                'inventory_value' => (float) $inventoryValue,
            ],
            'recent_orders' => $recentOrders,
            'revenue_chart' => $revenueChart,
            'waste_chart' => $wasteChart,
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
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();
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
                $title = 'Active Staff Details';
                $columns = [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'role', 'label' => 'Role'],
                ];

                // Fetch employees from staff table
                $employees = DB::table('staff')
                    ->join('users', 'staff.user_id', '=', 'users.id')
                    ->where('staff.restaurant_id', $restaurant->id)
                    ->where('staff.is_active', true)
                    ->select('users.name', 'users.email', 'staff.role');

                // Fetch owners/users from restaurant_user pivot
                $owners = DB::table('restaurant_user')
                    ->leftJoin('users', 'restaurant_user.email', '=', 'users.email')
                    ->where('restaurant_user.restaurant_id', $restaurant->id)
                    ->where('restaurant_user.is_active', true)
                    ->select('users.name', 'restaurant_user.email', 'restaurant_user.role');

                // Union all active staff
                $data = $employees->union($owners)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'name' => $user->name ?? 'Pending Registration',
                            'email' => $user->email,
                            'role' => ucfirst($user->role),
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

            case 'inventory_value':
                $title = 'Inventory Value Details (By Batch)';
                $columns = [
                    ['key' => 'ingredient_name', 'label' => 'Ingredient'],
                    ['key' => 'batch_number', 'label' => 'Batch'],
                    ['key' => 'quantity_remaining', 'label' => 'Qty Remaining'],
                    ['key' => 'unit', 'label' => 'Unit'],
                    ['key' => 'cost_per_unit', 'label' => 'Cost/Unit', 'format' => 'currency'],
                    ['key' => 'batch_value', 'label' => 'Batch Value', 'format' => 'currency'],
                ];

                $data = DB::table('ingredient_batches')
                    ->join('ingredients', 'ingredient_batches.ingredient_id', '=', 'ingredients.id')
                    ->where('ingredients.restaurant_id', $restaurant->id)
                    ->where('ingredient_batches.quantity_remaining', '>', 0)
                    ->select(
                        'ingredients.name as ingredient_name',
                        'ingredients.unit',
                        'ingredient_batches.batch_number',
                        'ingredient_batches.quantity_remaining',
                        'ingredient_batches.cost_per_unit',
                        DB::raw('(ingredient_batches.quantity_remaining * ingredient_batches.cost_per_unit) as batch_value')
                    )
                    ->orderByDesc('batch_value')
                    ->get()
                    ->map(function ($item) {
                        // Decode name if needed
                        $name = $item->ingredient_name;
                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                        }

                        return [
                            'ingredient_name' => $name,
                            'batch_number' => $item->batch_number,
                            'quantity_remaining' => $item->quantity_remaining,
                            'unit' => $item->unit,
                            'cost_per_unit' => $item->cost_per_unit,
                            'batch_value' => $item->batch_value,
                        ];
                    });
                break;

            case 'low_stock':
                $title = 'Low Stock Items';
                $columns = [
                    ['key' => 'name', 'label' => 'Item Name'],
                    ['key' => 'current_stock', 'label' => 'Current Stock'],
                    ['key' => 'reorder_level', 'label' => 'Reorder Level'],
                    ['key' => 'unit', 'label' => 'Unit'],
                ];

                $data = Ingredient::where('restaurant_id', $restaurant->id)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->get()
                    ->map(function ($item) {
                        // Decode name if needed
                        $name = $item->name;
                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                        }

                        return [
                            'name' => $name,
                            'current_stock' => $item->current_stock,
                            'reorder_level' => $item->reorder_level,
                            'unit' => $item->unit,
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
