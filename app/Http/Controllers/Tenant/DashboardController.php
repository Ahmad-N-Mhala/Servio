<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Staff;
use App\Models\Ingredient;
use App\Models\WasteLog;
use App\Models\MonthlyExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();

        // Get date range from request or default to last 7 days
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();  // Include full start day
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();  // Include full end day

        // -- Base Query --
        $baseOrderQuery = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Stats
        $totalOrders = (clone $baseOrderQuery)->count();

        $todayOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->count();

        $revenue = (clone $baseOrderQuery)
            ->where('status', 'completed')
            ->sum('total');

        $activeStaff = Staff::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->count();

        // Logged waste
        $totalWaste = WasteLog::where('restaurant_id', $restaurant->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->sum('total_loss');

        // Low Stock
        $lowStockCount = Ingredient::where('restaurant_id', $restaurant->id)
            ->whereRaw(['$expr' => ['$lte' => ['$current_stock', '$reorder_level']]])
            ->count();

        // Inventory Value (PHP Calculation)
        $ingredientIds = Ingredient::where('restaurant_id', $restaurant->id)->pluck('id')->toArray();
        $inventoryValue = DB::table('ingredient_batches')
            ->whereIn('ingredient_id', $ingredientIds)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->sum(function ($batch) {
                // Decimal128 needs string cast first
                $qty = isset($batch->quantity_remaining) ? (string) $batch->quantity_remaining : 0;
                $cost = isset($batch->cost_per_unit) ? (string) $batch->cost_per_unit : 0;
                return (float) $qty * (float) $cost;
            });

        // -- Recent Orders --
        $recentOrders = (clone $baseOrderQuery)
            ->with(['items', 'customer']) // customer relation must exist on Order
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

        // -- Revenue Chart --
        $revenueOrders = (clone $baseOrderQuery)
            ->where('status', 'completed')
            ->get(); // Get all to group in memory

        $revenueChart = $revenueOrders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            return [
                'date' => $date,
                'revenue' => (float) (string) $group->sum('total'),
            ];
        })->values()->sortBy('date')->values();

        // -- Average Dining Time --
        $avgDiningTime = $revenueOrders->whereNotNull('completed_at')->avg(function ($order) {
            return $order->completed_at->diffInMinutes($order->created_at);
        }) ?? 0;

        // -- Status Distribution --
        $statusDistribution = (clone $baseOrderQuery)
            ->get()
            ->groupBy('status')
            ->map(function ($group, $status) {
                return [
                    'status' => $status,
                    'count' => $group->count(),
                ];
            })->values();

        // -- Peak Hours --
        $peakHours = (clone $baseOrderQuery)
            ->get()
            ->groupBy(function ($order) {
                return $order->created_at->format('H');
            })->map(function ($group, $hour) {
                return [
                    'hour' => (int) $hour,
                    'count' => $group->count(),
                ];
            })->values()->sortBy('hour')->values();

        // -- Top Menu Items --
        // Simplified top items logic
        $allItems = [];
        $ordersWithItems = (clone $baseOrderQuery)->with('items')->get();

        foreach ($ordersWithItems as $order) {
            if ($order->items) {
                foreach ($order->items as $item) {
                    $id = $item->menu_item_id; // Assumes OrderItem has this field
                    if (!isset($allItems[$id])) {
                        $name = $item->name;
                        // Attempt JSON decode for name
                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                        }
                        $allItems[$id] = ['name' => $name, 'quantity' => 0];
                    }
                    $allItems[$id]['quantity'] += $item->quantity;
                }
            }
        }

        // Sort and slice
        usort($allItems, fn($a, $b) => $b['quantity'] <=> $a['quantity']);
        $topMenuItems = array_slice($allItems, 0, 5);

        // -- Waste Chart --
        $wasteChart = WasteLog::where('restaurant_id', $restaurant->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($log) {
                return Carbon::parse($log->log_date)->format('Y-m-d');
            })->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'loss' => (float) (string) $group->sum('total_loss'),
                ];
            })->values()->sortBy('date')->values();

        // -- Monthly Expenses (for the date range) --
        $monthlyExpenses = MonthlyExpense::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // -- Net Profit Calculation --
        // Net Profit = Revenue - (Monthly Expenses + Waste)
        $netProfit = (float) (string) $revenue - (float) (string) $monthlyExpenses - (float) (string) $totalWaste;

        return Inertia::render('Dashboard/Home', [
            'stats' => [
                'total_orders' => $totalOrders,
                'today_orders' => $todayOrders,
                'revenue' => (float) (string) $revenue,  // Cast Decimal128 to string then float
                'active_staff' => $activeStaff,
                'total_waste' => (float) (string) $totalWaste,  // Same for waste
                'low_stock_count' => $lowStockCount,
                'inventory_value' => (float) $inventoryValue,
                'monthly_expenses' => (float) (string) $monthlyExpenses,
                'net_profit' => $netProfit,
                'avg_dining_time' => round((float) $avgDiningTime, 0),
            ],
            'recent_orders' => $recentOrders,
            'revenue_chart' => $revenueChart,
            'waste_chart' => $wasteChart,
            'status_distribution' => $statusDistribution,
            'peak_hours' => $peakHours,
            'top_menu_items' => $topMenuItems,
            'avg_completion_time' => $this->getAverageCompletionTimeChart($restaurant->id, $startDate, $endDate),
            'date_range' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    private function getAverageCompletionTimeChart($restaurantId, $startDate, $endDate)
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        })->map(function ($dayOrders, $date) {
            $avgMinutes = $dayOrders->avg(function ($order) {
                $start = Carbon::parse($order->created_at);
                $from = Carbon::parse($order->completed_at);
                return $from->diffInMinutes($start);
            });

            return [
                'date' => $date,
                'minutes' => round((float) $avgMinutes, 1),
            ];
        })->values()->sortBy('date')->values();
    }

    public function getDetails(Request $request)
    {
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();
        $type = $request->input('type');
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate);
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate);

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
                    $query->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
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
                        'created_at' => $order->created_at->toIso8601String(),
                    ];
                });
                break;

            case 'revenue':
                $title = 'Revenue Details';
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'payment_method', 'label' => 'Payment Method'],
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
                            'payment_method' => $order->payment_method ?? 'Cash',
                            'created_at' => $order->created_at->toIso8601String(),
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

                // Staff
                $staffIds = Staff::where('restaurant_id', $restaurant->id)
                    ->where('is_active', true)
                    ->pluck('user_id')
                    ->toArray();

                $staffUsers = User::whereIn('id', $staffIds)->get()->map(function ($u) {
                    // Since we don't know exact role from user table alone, we might need to map back to staff.
                    // Or just fetching staff with user is better.
                    return [
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => 'Employee' // Simplified for logic separation, or fetch properly
                    ];
                });

                // Owners
                $ownerEntries = DB::table('restaurant_user')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('is_active', true)
                    ->where('role', 'owner')
                    ->get();

                $ownerEmails = $ownerEntries->pluck('email')->toArray();
                $ownerUsers = User::whereIn('email', $ownerEmails)->get();

                $mappedOwners = $ownerUsers->map(function ($u) {
                    return [
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => 'Owner'
                    ];
                });

                // Better approach: Get Staff with User models
                $staffModels = Staff::with('user')->where('restaurant_id', $restaurant->id)->where('is_active', true)->get();
                $mappedStaff = $staffModels->map(function ($s) {
                    return [
                        'name' => $s->user->name ?? 'N/A',
                        'email' => $s->user->email ?? 'N/A',
                        'role' => ucfirst($s->role ?? 'staff')
                    ];
                });

                $data = $mappedOwners->merge($mappedStaff);
                break;

            case 'revenue_chart_point':
                $date = $request->input('date');
                $title = "Revenue for " . $date;
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
                ];

                // Filter by string date match or range
                $startDatePoint = Carbon::parse($date)->startOfDay();
                $endDatePoint = Carbon::parse($date)->endOfDay();

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDatePoint, $endDatePoint])
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'created_at' => $order->created_at->toIso8601String(),
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
                            'created_at' => $order->created_at->toIso8601String(),
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

                $ingIds = Ingredient::where('restaurant_id', $restaurant->id)->pluck('id')->toArray();
                $ingredients = Ingredient::whereIn('id', $ingIds)->get()->keyBy('id');

                $batches = DB::table('ingredient_batches')
                    ->whereIn('ingredient_id', $ingIds)
                    ->where('quantity_remaining', '>', 0)
                    ->get();

                $data = $batches->map(function ($b) use ($ingredients) {
                    $ing = $ingredients[$b->ingredient_id] ?? null;
                    $name = $ing ? $ing->name : 'Unknown';
                    // Decode name
                    if (is_string($name) && str_starts_with($name, '{')) {
                        $decoded = json_decode($name, true);
                        $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                    }

                    // Calculation in map
                    $qty = isset($b->quantity_remaining) ? (string) $b->quantity_remaining : 0;
                    $cost = isset($b->cost_per_unit) ? (string) $b->cost_per_unit : 0;
                    $val = (float) $qty * (float) $cost;

                    return [
                        'ingredient_name' => $name,
                        'batch_number' => $b->batch_number ?? '-',
                        'quantity_remaining' => $b->quantity_remaining,
                        'unit' => $ing ? $ing->unit : '-',
                        'cost_per_unit' => $b->cost_per_unit,
                        'batch_value' => $val
                    ];
                })->sortByDesc('batch_value')->values();
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
                    ->whereRaw(['$expr' => ['$lte' => ['$current_stock', '$reorder_level']]])
                    ->get()
                    ->map(function ($item) {
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
        }

        return response()->json([
            'title' => $title,
            'columns' => $columns,
            'data' => $data
        ]);
    }
}
