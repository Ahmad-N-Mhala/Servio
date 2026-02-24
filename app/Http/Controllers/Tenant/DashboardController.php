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
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function export(Request $request)
    {
        $restaurant = auth()->user()->currentRestaurant() ?? Restaurant::first();
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());
        $format = $request->input('format', 'pdf');

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();

        // Specific Export for Retention Bucket
        if ($request->input('type') === 'retention_bucket' && $format === 'csv') {
            $bucket = (int) $request->input('bucket');
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=customers_visit_count_{$bucket}.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function () use ($restaurant, $startDate, $endDate, $bucket) {
                $file = fopen('php://output', 'w');
                $title = ($bucket === 5) ? "Customers with 5+ Visits" : "Customers with {$bucket} Visits";
                fputcsv($file, [$title]);
                fputcsv($file, [__('reports.restaurant'), $restaurant->name]);
                fputcsv($file, [__('reports.date_range'), $startDate->format('Y-m-d') . ' ' . __('reports.to') . ' ' . $endDate->format('Y-m-d')]);
                fputcsv($file, []);
                fputcsv($file, ['Name', 'Phone', 'Total Visits', 'Total Spent']);

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('customer')
                    ->get()
                    ->groupBy('customer_id')
                    ->map(function ($orders) use ($bucket) {
                        $count = $orders->count();
                        if ($bucket < 5 && $count !== $bucket)
                            return null;
                        if ($bucket === 5 && $count < 5)
                            return null;

                        $customer = $orders->first()->customer;
                        return [
                            $customer ? $customer->name : 'Unknown',
                            $customer ? $customer->phone : 'N/A',
                            $count,
                            (float) (string) $orders->sum('total'),
                        ];
                    })
                    ->filter()
                    ->values();

                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Specific Export for Item Sales
        if ($request->input('tab') === 'item_sales' && $format === 'excel') {
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=item_sales_report.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function () use ($restaurant, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [__('reports.item_sales_report')]);
                fputcsv($file, [__('reports.restaurant'), $restaurant->name]);
                fputcsv($file, [__('reports.date_range'), $startDate->format('Y-m-d') . ' ' . __('reports.to') . ' ' . $endDate->format('Y-m-d')]);
                fputcsv($file, []);

                fputcsv($file, [__('reports.item_name'), __('reports.category'), __('reports.quantity_sold'), __('reports.revenue')]);

                $orders = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', '!=', 'deleted')
                    ->where('status', '!=', 'cancelled')
                    ->where('payment_status', 'paid') // Only paid items
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with([
                        'items.menuItem' => function ($query) {
                            $query->withTrashed()->with([
                                'category' => function ($q) {
                                    $q->withTrashed();
                                }
                            ]);
                        }
                    ])
                    ->get();

                $itemStats = [];

                foreach ($orders as $order) {
                    if ($order->items) {
                        foreach ($order->items as $item) {
                            $id = $item->menu_item_id;
                            $quantity = $item->quantity;
                            $total = (float) ($item->total_price ?? 0);

                            if (!isset($itemStats[$id])) {
                                // Priority 1: Fetch from the Menu Items table (Source of Truth)
                                if ($item->menuItem) {
                                    $name = $item->menuItem->name;
                                } else {
                                    // Priority 2: Fallback to snapshot if the item was permanently deleted from DB
                                    $name = $item->name;
                                }
                                // Localize name
                                if (is_string($name) && str_starts_with($name, '{')) {
                                    $decoded = json_decode($name, true);
                                    $locale = app()->getLocale();
                                    $fallback = config('app.fallback_locale', 'en');
                                    $name = $decoded[$locale] ?? $decoded[$fallback] ?? $decoded['en'] ?? 'Unknown';
                                }

                                if ($item->menuItem && $item->menuItem->trashed()) {
                                    $name .= ' (Deleted - ' . $item->menuItem->deleted_at->format('d-m-Y') . ')';
                                }

                                // Category
                                $categoryName = 'Uncategorized';
                                if ($item->menuItem && $item->menuItem->category) {
                                    $catName = $item->menuItem->category->name;
                                    if (is_string($catName) && str_starts_with($catName, '{')) {
                                        $decoded = json_decode($catName, true);
                                        $locale = app()->getLocale();
                                        $categoryName = $decoded[$locale] ?? $decoded['en'] ?? 'Uncategorized';
                                    } else {
                                        $categoryName = $catName;
                                    }

                                    if ($item->menuItem->category->trashed()) {
                                        $categoryName .= ' (Deleted - ' . $item->menuItem->category->deleted_at->format('d-m-Y') . ')';
                                    }
                                }

                                $itemStats[$id] = [
                                    'name' => $name ?? 'Unknown',
                                    'category' => $categoryName,
                                    'quantity' => 0,
                                    'revenue' => 0,
                                ];
                            }
                            $itemStats[$id]['quantity'] += $quantity;
                            $itemStats[$id]['revenue'] += $total;
                        }
                    }
                }

                // sort by quantity desc by default
                usort($itemStats, fn($a, $b) => $b['quantity'] <=> $a['quantity']);

                foreach ($itemStats as $stat) {
                    fputcsv($file, [$stat['name'], $stat['category'], $stat['quantity'], $stat['revenue']]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Re-fetch stats for the report
        // Base Query
        $baseOrderQuery = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $allOrdersExport = (clone $baseOrderQuery)->with('items')->get();
        $revenueOrders = $allOrdersExport->where('payment_status', 'paid');

        // Stats
        $totalOrders = $allOrdersExport->count();
        $revenue = $revenueOrders->sum('total');
        $activeStaff = Staff::where('restaurant_id', $restaurant->id)->where('is_active', true)->count();
        $totalWaste = WasteLog::where('restaurant_id', $restaurant->id)->whereBetween('log_date', [$startDate, $endDate])->sum('total_loss');
        $monthlyExpenses = MonthlyExpense::where('restaurant_id', $restaurant->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        $netProfit = (float) (string) $revenue - (float) (string) $monthlyExpenses - (float) (string) $totalWaste;

        // Highlights Data (Selection Stats)
        $uniqueCustomersSelection = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();

        $selectionNewCustomers = \App\Models\Customer::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $selectionRepeatCustomers = max(0, $uniqueCustomersSelection - $selectionNewCustomers);

        $rewardsRedeemed = \App\Models\RewardRedemption::where('restaurant_id', $restaurant->id)
            ->whereBetween('redeemed_at', [$startDate, $endDate])
            ->count();

        // Use the pre-loaded orders and their items
        // $revenueOrders is already defined and loaded with items
        $avgDiningTime = $revenueOrders->whereNotNull('completed_at')->avg(function ($order) {
            return $order->completed_at->diffInMinutes($order->created_at);
        }) ?? 0;

        // Top Menu Items & Categories
        $allItems = [];
        $categorySales = [];
        $menuItemIds = [];

        foreach ($revenueOrders as $order) {
            if ($order->items) {
                foreach ($order->items as $item) {
                    $id = $item->menu_item_id;
                    $menuItemIds[] = $id;

                    // Top Items
                    if (!isset($allItems[$id])) {
                        $name = $item->name;
                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $locale = app()->getLocale();
                            $fallback = config('app.fallback_locale', 'en');
                            $name = $decoded[$locale] ?? $decoded[$fallback] ?? $decoded['en'] ?? 'Unknown';
                        }
                        $allItems[$id] = ['name' => $name, 'quantity' => 0];
                    }
                    $allItems[$id]['quantity'] += $item->quantity;
                }
            }
        }
        usort($allItems, fn($a, $b) => $b['quantity'] <=> $a['quantity']);
        $topMenuItems = array_slice($allItems, 0, 5);

        // Fetch Categories
        $menuItemsWithCategories = \App\Models\MenuItem::whereIn('id', array_unique($menuItemIds))
            ->with('category')
            ->get()
            ->keyBy('id');

        foreach ($revenueOrders as $order) {
            if ($order->items) {
                foreach ($order->items as $item) {
                    $mItem = $menuItemsWithCategories[$item->menu_item_id] ?? null;
                    $catName = $mItem && $mItem->category ? $mItem->category->name : 'Uncategorized';

                    // Translate Category
                    if (is_string($catName) && str_starts_with($catName, '{')) {
                        $decoded = json_decode($catName, true);
                        $locale = app()->getLocale();
                        $catName = $decoded[$locale] ?? $decoded['en'] ?? 'Uncategorized';
                    }

                    if (!isset($categorySales[$catName])) {
                        $categorySales[$catName] = 0;
                    }
                    // Use stored total_price which effectively includes extras
                    $itemTotal = (float) ($item->total_price ?? 0);
                    $categorySales[$catName] += $itemTotal;
                }
            }
        }

        $topCategories = collect($categorySales)
            ->map(function ($total, $name) {
                return ['name' => $name, 'value' => $total];
            })
            ->sortByDesc('value')
            ->values()
            ->take(5)
            ->toArray();


        // Recent Orders
        // -- Customer Retention (Visits Funnel) --
        $customerVisits = $revenueOrders
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', 'Guest')
            ->groupBy('customer_name')
            ->map(fn($o) => $o->count());

        $totalCustomers = $customerVisits->count();
        $retentionStats = [];

        for ($i = 1; $i <= 5; $i++) {
            $label = $i === 5 ? '5th+ Visit' : ($i === 1 ? '1st Visit' : $i . match ($i) { 2 => 'nd', 3 => 'rd', default => 'th'} . ' Visit');

            if ($totalCustomers === 0) {
                $retentionStats[] = ['label' => $label, 'percentage' => 0, 'count' => 0];
                continue;
            }

            $countAtLeast = $customerVisits->filter(fn($visits) => $visits >= $i)->count();
            $percentage = ($countAtLeast / $totalCustomers) * 100;

            $retentionStats[] = [
                'label' => $label,
                'percentage' => round($percentage, 1),
                'count' => $countAtLeast
            ];
        }

        // Top Customers
        $topCustomers = $revenueOrders
            ->groupBy('customer_name') // Group by name for now
            ->map(function ($orders, $name) {
                return [
                    'name' => $name ?: 'Guest',
                    'count' => $orders->count(),
                    'total' => (float) (string) $orders->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(5);

        // -- Status Distribution --
        $statusDistribution = $allOrdersExport
            ->groupBy('status')
            ->map(function ($group, $status) {
                return [
                    'status' => $status,
                    'count' => $group->count(),
                ];
            })->values();

        // -- Peak Hours --
        $peakHours = $allOrdersExport
            ->groupBy(function ($order) {
                return $order->created_at->format('H');
            })->map(function ($group, $hour) {
                return [
                    'hour' => (int) $hour,
                    'count' => $group->count(),
                ];
            })->values()->sortBy('hour')->values();

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

        // -- Avg Completion Time Chart --
        $avgCompletionTimeChart = $this->getAverageCompletionTimeChart($restaurant->id, $startDate, $endDate);

        // -- Low Stock / Critical --
        $criticalIngredients = $this->getCriticalIngredients($restaurant->id);
        $lowStockCount = $criticalIngredients->count();

        // -- Inventory Value --
        $ingredientIds = Ingredient::where('restaurant_id', $restaurant->id)->pluck('id')->toArray();
        $inventoryValue = DB::table('ingredient_batches')
            ->whereIn('ingredient_id', $ingredientIds)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->sum(function ($batch) {
                $qty = isset($batch->quantity_remaining) ? (string) $batch->quantity_remaining : 0;
                $cost = isset($batch->cost_per_unit) ? (string) $batch->cost_per_unit : 0;
                return (float) $qty * (float) $cost;
            });

        // -- Revenue Chart --
        // $revenueOrders already configured
        $revenueChart = $revenueOrders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            return [
                'date' => $date,
                'revenue' => (float) (string) $group->sum('total'),
            ];
        })->values()->sortBy('date')->values();

        $data = [
            'restaurant' => $restaurant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currency' => $restaurant->currency ?? 'AED',
            'stats' => [
                'total_orders' => $totalOrders,
                'revenue' => (float) (string) $revenue,
                'net_profit' => $netProfit,
                'total_waste' => (float) (string) $totalWaste,
                'active_staff' => $activeStaff,
                'avg_dining_time' => round((float) $avgDiningTime, 0),
                'low_stock_count' => $lowStockCount,
                'inventory_value' => (float) $inventoryValue,
                'monthly_expenses' => (float) (string) $monthlyExpenses,
                'new_customers' => $selectionNewCustomers,
                'repeat_customers' => $selectionRepeatCustomers,
                'rewards_redeemed' => $rewardsRedeemed,
                'total_unique_customers' => $uniqueCustomersSelection
            ],
            'topMenuItems' => $topMenuItems,
            'topCategories' => $topCategories,
            'topCustomers' => $topCustomers,
            'retentionStats' => $retentionStats,
            'revenueChart' => $revenueChart,
            'statusDistribution' => $statusDistribution,
            'peakHours' => $peakHours,
            'wasteChart' => $wasteChart,
            'avgCompletionTime' => $avgCompletionTimeChart,
        ];

        if ($format === 'excel') {
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=dashboard_report.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                // Header
                // Header
                fputcsv($file, [__('reports.dashboard_report')]);
                fputcsv($file, [__('reports.restaurant'), $data['restaurant']->name]);
                fputcsv($file, [__('reports.date_range'), $data['startDate']->format('Y-m-d') . ' ' . __('reports.to') . ' ' . $data['endDate']->format('Y-m-d')]);
                fputcsv($file, []);

                // Stats
                fputcsv($file, [__('reports.key_metrics')]);
                fputcsv($file, [__('reports.total_orders'), $data['stats']['total_orders']]);
                fputcsv($file, [__('reports.revenue'), $data['stats']['revenue']]);
                fputcsv($file, [__('reports.net_profit'), $data['stats']['net_profit']]);
                fputcsv($file, [__('reports.total_waste'), $data['stats']['total_waste']]);
                fputcsv($file, [__('reports.inventory_value'), $data['stats']['inventory_value']]);
                fputcsv($file, [__('dashboard.rewards_redeemed'), $data['stats']['rewards_redeemed']]);
                fputcsv($file, [__('dashboard.new_customers'), $data['stats']['new_customers']]);
                fputcsv($file, [__('dashboard.repeat_customers'), $data['stats']['repeat_customers']]);
                fputcsv($file, []);

                // Top Items
                fputcsv($file, [__('reports.top_menu_items')]);
                fputcsv($file, [__('reports.item_name'), __('reports.quantity_sold')]);
                foreach ($data['topMenuItems'] as $item) {
                    fputcsv($file, [$item['name'], $item['quantity']]);
                }
                fputcsv($file, []);

                // Top Categories
                fputcsv($file, [__('reports.top_categories')]);
                fputcsv($file, [__('reports.category'), __('reports.sales_value')]);
                foreach ($data['topCategories'] as $cat) {
                    fputcsv($file, [$cat['name'], $cat['value']]);
                }
                fputcsv($file, []);

                // Customer Retention
                fputcsv($file, [__('reports.customer_retention')]);
                fputcsv($file, [__('reports.milestone'), __('reports.count'), __('reports.percentage')]);
                foreach ($data['retentionStats'] as $stat) {
                    fputcsv($file, [$stat['label'], $stat['count'], $stat['percentage'] . '%']);
                }
                fputcsv($file, []);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $pdf = Pdf::loadView('exports.dashboard', $data);
        return $pdf->stream('dashboard-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function index(Request $request): Response
    {
        $restaurant = auth()->user()->currentRestaurant() ?? Restaurant::first();

        // Get date range from request or default to last 7 days
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();  // Include full start day
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();  // Include full end day

        if ($request->input('tab') === 'item_sales') {
            $query = $request->input('q');
            $sortBy = $request->input('sort', 'quantity_desc'); // quantity_desc, quantity_asc, revenue_desc, revenue_asc, name_asc, name_desc
            $perPage = 10;
            $currentPage = $request->input('page', 1);

            // Fetch all orders with items in date range
            $orders = Order::where('restaurant_id', $restaurant->id)
                ->where('status', '!=', 'deleted')
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', 'paid') // Only paid items
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with([
                    'items.menuItem' => function ($query) {
                        $query->withTrashed()->with([
                            'category' => function ($q) {
                                $q->withTrashed();
                            }
                        ]);
                    }
                ])
                ->get();

            $itemStats = [];

            foreach ($orders as $order) {
                if ($order->items) {
                    foreach ($order->items as $item) {
                        $id = $item->menu_item_id;
                        $quantity = $item->quantity;
                        $total = (float) ($item->total_price ?? 0);

                        if (!isset($itemStats[$id])) {
                            // Priority 1: Fetch from the Menu Items table (Source of Truth)
                            if ($item->menuItem) {
                                $name = $item->menuItem->name;
                            } else {
                                // Priority 2: Fallback to snapshot if the item was permanently deleted from DB
                                $name = $item->name;
                            }

                            // Localize name
                            if (is_string($name) && str_starts_with($name, '{')) {
                                $decoded = json_decode($name, true);
                                $locale = app()->getLocale();
                                $fallback = config('app.fallback_locale', 'en');
                                $name = $decoded[$locale] ?? $decoded[$fallback] ?? $decoded['en'] ?? 'Unknown';
                            }

                            // Check if deleted
                            if ($item->menuItem && $item->menuItem->trashed()) {
                                $name .= ' (Deleted - ' . $item->menuItem->deleted_at->format('d-m-Y') . ')';
                            }

                            // Calculate Category
                            $categoryName = 'Uncategorized';
                            if ($item->menuItem && $item->menuItem->category) {
                                $catName = $item->menuItem->category->name;
                                if (is_string($catName) && str_starts_with($catName, '{')) {
                                    $decoded = json_decode($catName, true);
                                    $locale = app()->getLocale();
                                    $categoryName = $decoded[$locale] ?? $decoded['en'] ?? 'Uncategorized';
                                } else {
                                    $categoryName = $catName;
                                }

                                if ($item->menuItem->category->trashed()) {
                                    $categoryName .= ' (Deleted - ' . $item->menuItem->category->deleted_at->format('d-m-Y') . ')';
                                }
                            }

                            $itemStats[$id] = [
                                'id' => $id,
                                'name' => $name ?? 'Unknown',
                                'category' => $categoryName,
                                'quantity' => 0,
                                'revenue' => 0,
                            ];
                        }

                        $itemStats[$id]['quantity'] += $quantity;
                        $itemStats[$id]['revenue'] += $total;
                    }
                }
            }

            // Convert to collection for filtering/sorting
            $statsCollection = collect($itemStats)->values();

            // Filter
            if ($query) {
                $statsCollection = $statsCollection->filter(function ($item) use ($query) {
                    return stripos($item['name'], $query) !== false || stripos($item['category'], $query) !== false;
                });
            }

            // Sort
            $statsCollection = $statsCollection->sort(function ($a, $b) use ($sortBy) {
                switch ($sortBy) {
                    case 'quantity_asc':
                        return $a['quantity'] <=> $b['quantity'];
                    case 'quantity_desc':
                        return $b['quantity'] <=> $a['quantity'];
                    case 'revenue_asc':
                        return $a['revenue'] <=> $b['revenue'];
                    case 'revenue_desc':
                        return $b['revenue'] <=> $a['revenue'];
                    case 'name_asc':
                        return strnatcasecmp($a['name'], $b['name']);
                    case 'name_desc':
                        return strnatcasecmp($b['name'], $a['name']);
                    default:
                        return $b['quantity'] <=> $a['quantity'];
                }
            });

            // Paginate
            $totalItems = $statsCollection->count();
            $pagedData = $statsCollection->forPage($currentPage, $perPage)->values();

            return Inertia::render('Dashboard/Home', [
                'active_tab' => 'item_sales',
                'item_sales_data' => [
                    'data' => $pagedData,
                    'current_page' => (int) $currentPage,
                    'last_page' => ceil($totalItems / $perPage),
                    'total' => $totalItems,
                    'per_page' => $perPage,
                    'from' => $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0,
                    'to' => min($currentPage * $perPage, $totalItems),
                ],
                'filters' => [
                    'q' => $query,
                    'sort' => $sortBy
                ],
                'date_range' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
            ]);
        }

        // -- Base Query --
        // Dashboard Revamp Logic

        // 1. Highlights for Selection
        $selectionStats = [
            'sales' => (float) (string) Order::where('restaurant_id', $restaurant->id)
                ->where('status', '!=', 'deleted')
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'orders' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', '!=', 'deleted')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'customers' => \App\Models\Customer::where('restaurant_id', $restaurant->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'rewards_redeemed' => \App\Models\RewardRedemption::where('restaurant_id', $restaurant->id)
                ->whereBetween('redeemed_at', [$startDate, $endDate])
                ->count(),
        ];

        // New vs Repeat Customers for Selection
        $uniqueCustomersSelection = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();

        $selectionNewCustomers = \App\Models\Customer::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $selectionRepeatCustomers = max(0, $uniqueCustomersSelection - $selectionNewCustomers);

        $selectionStats['new_customers'] = $selectionNewCustomers;
        $selectionStats['repeat_customers'] = $selectionRepeatCustomers;

        // 2. Statistics for Selected Period (Default 30 days if not set, handled at top)
        $baseOrderQuery = Order::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'deleted')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $allOrders = (clone $baseOrderQuery)->with('items')->get();
        $revenueOrders = $allOrders->where('payment_status', 'paid');

        $totalSalesPeriod = (float) $revenueOrders->sum('total');
        $validOrdersCount = $allOrders->count();
        $cancelledOrdersCount = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // 3. Visits/Orders Chart
        $dailyVisits = $allOrders
            ->groupBy(fn($date) => $date->created_at->format('Y-m-d'))
            ->map->count();

        $visitsChartData = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $visitsChartData[] = [
                'date' => $dateStr,
                'count' => $dailyVisits[$dateStr] ?? 0,
            ];
            $current->addDay();
        }

        // 4. Customer Insights (Active vs Inactive)
        $totalCustomersCount = \App\Models\Customer::where('restaurant_id', $restaurant->id)->count();
        $activeCustomersCount = Order::where('restaurant_id', $restaurant->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();
        $inactiveCustomersCount = max(0, $totalCustomersCount - $activeCustomersCount);

        // 5. Upcoming Celebrations
        $upcomingCelebrations = \App\Models\Customer::where('restaurant_id', $restaurant->id)
            ->whereNotNull('dob')
            ->get()
            ->filter(function ($customer) {
                if (!$customer->dob)
                    return false;
                $dob = Carbon::parse($customer->dob)->setYear(now()->year);
                if ($dob->isPast())
                    $dob->addYear();
                return $dob->between(now(), now()->addDays(30));
            })
            ->sortBy(function ($customer) {
                $dob = Carbon::parse($customer->dob)->setYear(now()->year);
                if ($dob->isPast())
                    $dob->addYear();
                return $dob->timestamp;
            })
            ->take(5)
            ->values()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'dob' => $c->dob,
                'type' => 'Birthday'
            ]);

        // 6. Top Insights & Pareto
        $totalLifeTimeRevenue = Order::where('restaurant_id', $restaurant->id)
            ->where('payment_status', 'paid')
            ->sum('total');
        $totalLifeTimeRevenue = (float) (string) $totalLifeTimeRevenue;

        $top20PercentCount = (int) ceil($totalCustomersCount * 0.2);
        if ($top20PercentCount > 0) {
            $top20Revenue = Order::where('restaurant_id', $restaurant->id)
                ->where('payment_status', 'paid')
                ->whereNotNull('customer_id')
                ->get()
                ->groupBy('customer_id')
                ->map(fn($orders) => $orders->sum(fn($o) => (float) (string) $o->total))
                ->sortDesc()
                ->take($top20PercentCount)
                ->sum();

            $paretoRevenuePercent = $totalLifeTimeRevenue > 0 ? round(($top20Revenue / $totalLifeTimeRevenue) * 100) : 0;
        } else {
            $paretoRevenuePercent = 0;
        }

        // Avg Order Value
        $totalOrders = $allOrders->count();
        $avgOrderValue = $totalOrders > 0 ? round($totalSalesPeriod / $totalOrders, 2) : 0;

        // Avg Visits Lifetime
        $avgVisitsPerCustomer = $totalCustomersCount > 0 ? round(Order::where('restaurant_id', $restaurant->id)->count() / $totalCustomersCount, 1) : 0;

        // 7. Popular Times
        $popularTimes = $allOrders
            ->groupBy(function ($order) {
                return $order->created_at->format('l') . '|' . $order->created_at->format('H');
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'day' => $first->created_at->format('l'),
                    'hour' => (int) $first->created_at->format('H'),
                    'count' => $group->count(),
                    'revenue' => $group->sum(fn($o) => (float) (string) $o->total)
                ];
            })
            ->sortByDesc('count')
            ->values();

        $mostPopularTime = $popularTimes->first();
        $leastPopularTime = $popularTimes->sortBy('count')->first();

        $formatTimePeriod = function ($day, $hour) {
            if ($day === null)
                return "Unknown";
            $dayName = $day;
            if ($hour >= 5 && $hour < 12)
                return "$dayName morning";
            if ($hour >= 12 && $hour < 17)
                return "$dayName afternoon";
            if ($hour >= 17 && $hour < 21)
                return "$dayName evening";
            return "$dayName night";
        };

        // 8. Customer Frequency
        $customerVisitCounts = $allOrders
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->map->count();

        $freqStats = [
            '1' => $customerVisitCounts->filter(fn($c) => $c === 1)->count(),
            '2' => $customerVisitCounts->filter(fn($c) => $c === 2)->count(),
            '3-5' => $customerVisitCounts->filter(fn($c) => $c >= 3 && $c <= 5)->count(),
            '6+' => $customerVisitCounts->filter(fn($c) => $c >= 6)->count(),
        ];

        // 9. Top Rewards
        $topRewards = \App\Models\RewardRedemption::where('restaurant_id', $restaurant->id)
            ->whereBetween('redeemed_at', [$startDate, $endDate])
            ->with('reward')
            ->get()
            ->groupBy('reward_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'name' => $first->reward ? $first->reward->name ?? 'Unknown' : 'Unknown',
                    'description' => $first->reward ? $first->reward->description : '',
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(4)
            ->values();

        // Revenue Chart (Daily Sales)
        $revenueChart = $revenueOrders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            return [
                'date' => $date, // Consider fixing timezone if needed
                'revenue' => (float) (string) $group->sum('total'),
            ];
        })->values()->sortBy('date')->values();

        // Top Items & Categories
        $allItems = [];
        $categorySales = [];
        $menuItemIds = [];
        foreach ($revenueOrders as $order) {
            if ($order->items) {
                foreach ($order->items as $item) {
                    $id = $item->menu_item_id;
                    $menuItemIds[] = $id;
                    if (!isset($allItems[$id])) {
                        $name = $item->name;
                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $locale = app()->getLocale();
                            $name = $decoded[$locale] ?? $decoded['en'] ?? 'Unknown';
                        }
                        $allItems[$id] = ['name' => $name, 'quantity' => 0];
                    }
                    $allItems[$id]['quantity'] += $item->quantity;
                }
            }
        }
        usort($allItems, fn($a, $b) => $b['quantity'] <=> $a['quantity']);
        $topMenuItems = array_slice($allItems, 0, 5);

        $menuItemsWithCategories = \App\Models\MenuItem::whereIn('id', array_unique($menuItemIds))
            ->with('category')->get()->keyBy('id');

        foreach ($revenueOrders as $order) {
            if ($order->items) {
                foreach ($order->items as $item) {
                    $mItem = $menuItemsWithCategories[$item->menu_item_id] ?? null;
                    $catName = $mItem && $mItem->category ? $mItem->category->name : 'Uncategorized';
                    if (is_string($catName) && str_starts_with($catName, '{')) {
                        $decoded = json_decode($catName, true);
                        $locale = app()->getLocale();
                        $catName = $decoded[$locale] ?? $decoded['en'] ?? 'Uncategorized';
                    }
                    if (!isset($categorySales[$catName]))
                        $categorySales[$catName] = 0;
                    $categorySales[$catName] += (float) ($item->total_price ?? 0);
                }
            }
        }
        $topCategories = collect($categorySales)
            ->map(fn($v, $k) => ['name' => $k, 'value' => $v])
            ->sortByDesc('value')->values()->take(5)->toArray();

        // 10. Status Distribution
        $statusDistribution = $allOrders
            ->groupBy('status')
            ->map(function ($group, $status) {
                return [
                    'status' => $status,
                    'count' => $group->count(),
                ];
            })->values();

        // 11. Payment Distribution
        $paymentDistribution = $revenueOrders
            ->groupBy(fn($o) => strtolower((string) ($o->payment_method ?: 'cash')))
            ->map(function ($group, $method) {
                return [
                    'method' => ucwords(str_replace('_', ' ', (string) $method)),
                    'value' => (float) (string) $group->sum('total'),
                    'count' => $group->count()
                ];
            })->values();

        // 12. Peak Hours
        $peakHours = $allOrders
            ->groupBy(function ($order) {
                return $order->created_at->format('H');
            })->map(function ($group, $hour) {
                return [
                    'hour' => (int) $hour,
                    'count' => $group->count(),
                ];
            })->values()->sortBy('hour')->values();

        // 13. Waste Chart
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

        // These unused heavy calculations have been removed to improve speed and memory usage.

        // 14. Retention Stats
        $customerVisits = $revenueOrders
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->map(fn($o) => $o->count());

        $totalCustomers = $customerVisits->count();
        $retentionStats = [];
        for ($i = 1; $i <= 5; $i++) {
            $label = $i === 5 ? '5th+ Visit' : ($i === 1 ? '1st Visit' : $i . match ($i) { 2 => 'nd', 3 => 'rd', default => 'th'} . ' Visit');
            if ($totalCustomers === 0) {
                $retentionStats[] = ['label' => $label, 'percentage' => 0, 'count' => 0];
                continue;
            }
            $countAtLeast = $customerVisits->filter(fn($visits) => $visits >= $i)->count();
            $percentage = ($countAtLeast / $totalCustomers) * 100;
            $retentionStats[] = [
                'label' => $label,
                'percentage' => round($percentage, 1),
                'count' => $countAtLeast
            ];
        }

        return Inertia::render('Dashboard/Home', [
            'active_tab' => 'overview',
            'item_sales_data' => null,
            'filters' => [],
            'date_range' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            // New Dashboard Structure
            'highlights' => $selectionStats,
            'period_sales' => [
                'total' => $totalSalesPeriod,
                'valid_count' => $validOrdersCount,
                'blocked_count' => $cancelledOrdersCount,
                'chart' => $revenueChart
            ],
            'period_visits' => [
                'total' => $validOrdersCount,
                'chart' => $visitsChartData
            ],
            'customer_insights' => [
                'total' => $totalCustomersCount,
                'active' => $activeCustomersCount,
                'inactive' => $inactiveCustomersCount
            ],
            'upcoming_celebrations' => $upcomingCelebrations,
            'top_insights' => [
                'pareto_percent' => $paretoRevenuePercent,
                'avg_order_value' => $avgOrderValue,
                'avg_items_per_order' => 0.6, // Placeholder
                'avg_visits_per_year' => $avgVisitsPerCustomer
            ],
            'popular_times' => [
                'most_popular' => $mostPopularTime ? [
                    'label' => $formatTimePeriod($mostPopularTime['day'], $mostPopularTime['hour']),
                    'orders' => $mostPopularTime['count'],
                    'revenue' => $mostPopularTime['revenue']
                ] : null,
                'least_popular' => $leastPopularTime ? [
                    'label' => $formatTimePeriod($leastPopularTime['day'], $leastPopularTime['hour']),
                    'orders' => $leastPopularTime['count'],
                    'revenue' => $leastPopularTime['revenue']
                ] : null
            ],
            'customer_frequency' => $freqStats,
            'top_rewards' => $topRewards,
            'top_items' => $topMenuItems,
            'top_categories' => $topCategories,
            'retention_stats' => $retentionStats,
            'status_distribution' => $statusDistribution,
            'payment_distribution' => $paymentDistribution,
            'peak_hours' => $peakHours,
            'waste_chart' => $wasteChart,
            'avg_completion_time' => $this->getAverageCompletionTimeChart($restaurant->id, $startDate, $endDate),

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
                return $from->diffInMinutes($start, true);
            });

            return [
                'date' => $date,
                'minutes' => round((float) $avgMinutes, 1),
            ];
        })->values()->sortBy('date')->values();
    }

    /**
     * Get ingredients that are either below reorder level or blocking a menu item.
     */
    private function getCriticalIngredients($restaurantId)
    {
        $ingredients = Ingredient::where('restaurant_id', $restaurantId)->get();
        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->whereNotNull('recipe')
            ->get(['id', 'name', 'recipe']);

        $blockingIds = [];
        foreach ($menuItems as $menuItem) {
            $recipe = $menuItem->recipe;
            if (is_array($recipe)) {
                foreach ($recipe as $component) {
                    $ingId = (string) ($component['ingredient_id'] ?? '');
                    $qtyNeeded = (float) ($component['quantity'] ?? 0);

                    $ingredient = $ingredients->firstWhere('id', $ingId);
                    if ($ingredient && $ingredient->current_stock < $qtyNeeded) {
                        $blockingIds[] = (string) $ingredient->id;
                    }
                }
            }
        }

        $blockingIds = array_unique($blockingIds);

        return $ingredients->filter(function ($item) use ($blockingIds) {
            $isBelowReorder = $item->current_stock <= $item->reorder_level;
            $isBlocking = in_array((string) $item->id, $blockingIds);
            return $isBelowReorder || $isBlocking;
        });
    }

    public function getDetails(Request $request)
    {
        $restaurant = auth()->user()->currentRestaurant() ?? Restaurant::first();
        $type = $request->input('type');
        $startDate = $request->input('start_date', now()->subDays(7)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        if (is_string($startDate))
            $startDate = Carbon::parse($startDate)->startOfDay();
        if (is_string($endDate))
            $endDate = Carbon::parse($endDate)->endOfDay();

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

            case 'selection_sales':
            case 'revenue':
                $title = 'Revenue Details';
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'payment_method', 'label' => 'Payment Method'],
                    ['key' => 'created_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'deleted')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->limit(100)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name ?: 'Guest',
                            'total' => $order->total,
                            'payment_method' => ucwords($order->payment_method ?? 'Cash'),
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    });
                break;

            case 'new_customers':
                $title = 'New Customers';
                $columns = [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'joined_at', 'label' => 'Joined Date', 'format' => 'datetime'],
                ];

                $data = \App\Models\Customer::where('restaurant_id', $restaurant->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(fn($c) => [
                        'name' => $c->name,
                        'phone' => $c->phone,
                        'email' => $c->email,
                        'joined_at' => $c->created_at->toIso8601String()
                    ]);
                break;

            case 'repeat_customers':
                $title = 'Repeat Customers';
                $columns = [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'visit_count', 'label' => 'Visits'],
                    ['key' => 'total_spent', 'label' => 'Total Spent', 'format' => 'currency'],
                ];

                // Customers who had their first visit BEFORE start date but visited AGAIN in this period
                $customerIdsWithActivity = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('customer_id')
                    ->unique()
                    ->toArray();

                $data = \App\Models\Customer::whereIn('id', $customerIdsWithActivity)
                    ->get()
                    ->filter(function ($c) use ($startDate) {
                        return $c->created_at < $startDate;
                    })
                    ->map(function ($c) use ($startDate, $endDate) {
                        $periodOrders = Order::where('customer_id', $c->id)
                            ->where('payment_status', 'paid')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->get();

                        return [
                            'name' => $c->name,
                            'phone' => $c->phone,
                            'visit_count' => $periodOrders->count(),
                            'total_spent' => (float) $periodOrders->sum('total')
                        ];
                    })->values();
                break;

            case 'rewards_redeemed':
                $title = 'Rewards Redeemed';
                $columns = [
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'reward_name', 'label' => 'Reward'],
                    ['key' => 'points_cost', 'label' => 'Points'],
                    ['key' => 'redeemed_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = DB::table('reward_redemptions')
                    ->where('restaurant_id', $restaurant->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($r) {
                        $customer = \App\Models\Customer::find($r->customer_id);
                        $reward = DB::table('rewards')->where('id', $r->reward_id)->first();

                        $rewardName = $reward ? $reward->name : 'Unknown Reward';
                        if (is_string($rewardName) && str_starts_with($rewardName, '{')) {
                            $decoded = json_decode($rewardName, true);
                            $rewardName = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                        }

                        return [
                            'customer_name' => $customer ? $customer->name : 'Guest',
                            'reward_name' => $rewardName,
                            'points_cost' => $r->points_spent,
                            'redeemed_at' => Carbon::parse($r->created_at)->toIso8601String()
                        ];
                    });
                break;

            case 'status_slice':
                $status = $request->input('status');
                $title = "Orders: " . ucfirst($status);
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'total', 'label' => 'Total', 'format' => 'currency'],
                    ['key' => 'status', 'label' => 'Status', 'format' => 'status'],
                    ['key' => 'payment_status', 'label' => 'Payment'],
                    ['key' => 'created_at', 'label' => 'Date', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', $status)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->limit(100)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name ?: 'Guest',
                            'total' => $order->total,
                            'status' => $order->status,
                            'payment_status' => ucfirst($order->payment_status ?: 'unpaid'),
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    });
                break;

            case 'revenue_chart_point':
                $date = $request->input('date');
                $title = "Revenue for " . $date;
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'customer_name', 'label' => 'Customer'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
                ];

                $startDatePoint = Carbon::parse($date)->startOfDay();
                $endDatePoint = Carbon::parse($date)->endOfDay();

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'deleted')
                    ->whereBetween('created_at', [$startDatePoint, $endDatePoint])
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name ?: 'Guest',
                            'total' => $order->total,
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    });
                break;

            case 'low_stock':
                $title = __('dashboard.low_stock_items');
                $columns = [
                    ['key' => 'name', 'label' => __('dashboard.ingredient')],
                    ['key' => 'affected_items', 'label' => __('dashboard.affected_items')],
                    ['key' => 'current_stock', 'label' => __('dashboard.current_stock')],
                    ['key' => 'reorder_level', 'label' => __('dashboard.reorder_level')],
                    ['key' => 'unit', 'label' => 'Unit'],
                    ['key' => 'status_message', 'label' => __('dashboard.status')],
                ];

                // Use the shared helper to get the base set of problematic ingredients
                $ingredients = Ingredient::where('restaurant_id', $restaurant->id)->get();
                $criticalIngredients = $this->getCriticalIngredients($restaurant->id);

                $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
                    ->where('is_available', true)
                    ->whereNotNull('recipe')
                    ->get(['id', 'name', 'recipe']);

                $dependencyMap = [];
                $blockingMap = [];

                foreach ($menuItems as $menuItem) {
                    $recipe = $menuItem->recipe;
                    if (is_array($recipe)) {
                        foreach ($recipe as $component) {
                            $ingId = (string) ($component['ingredient_id'] ?? '');
                            $qtyNeeded = (float) ($component['quantity'] ?? 0);

                            $ingredient = $ingredients->firstWhere('id', $ingId);
                            if ($ingredient) {
                                $name = $menuItem->getTranslation('name', app()->getLocale()) ?: $menuItem->name;
                                if (is_array($name)) {
                                    $name = $name[app()->getLocale()] ?? $name['en'] ?? $name['ar'] ?? 'Unknown';
                                }
                                $dependencyMap[$ingId][] = $name;

                                // If stock is less than needed for THIS recipe
                                if ($ingredient->current_stock < $qtyNeeded) {
                                    $blockingMap[$ingId][] = $name;
                                }
                            }
                        }
                    }
                }

                $data = $criticalIngredients->map(function ($item) use ($dependencyMap, $blockingMap) {
                    $name = $item->getTranslation('name', app()->getLocale()) ?: $item->name;
                    if (is_array($name)) {
                        $name = $name[app()->getLocale()] ?? $name['en'] ?? $name['ar'] ?? 'Unknown';
                    }

                    $affected = array_unique($dependencyMap[(string) $item->id] ?? []);
                    $blocked = array_unique($blockingMap[(string) $item->id] ?? []);
                    $affectedStr = !empty($affected) ? implode(', ', $affected) : 'None';

                    $statusParts = [];
                    if ($item->current_stock <= $item->reorder_level) {
                        $statusParts[] = __('dashboard.low_stock_status');
                    }
                    if (!empty($blocked)) {
                        $statusParts[] = __('dashboard.critical_status', ['items' => implode(', ', $blocked)]);
                    }

                    return [
                        'name' => $name,
                        'affected_items' => $affectedStr,
                        'current_stock' => $item->current_stock,
                        'reorder_level' => $item->reorder_level,
                        'unit' => $item->unit,
                        'status_message' => !empty($statusParts) ? implode(' | ', $statusParts) : 'Attention Needed',
                    ];
                })->values();
                break;

            case 'payment_method_slice':
                $method = $request->input('method');
                // Reverse map the display method back to key if needed, or send key from frontend
                // Assuming frontend sends the key (e.g. 'cash', 'card', 'online')
                // But wait, the chart uses the grouped names.
                // Let's assume we pass the raw db value from frontend if possible, or fuzzy match.
                // Actually the chart `initPaymentChart` uses `paymentDistribution` values which have `method` property which is `ucwords(...)`.
                // Better to make sure we query somewhat loosely or update frontend to send raw key.
                // For now, let's try to match case-insensitive or expect mapped value.
                // Ideally, we'd store raw key in chart data and use it.
                // Let's assume frontend sends something we can query.

                // If the frontend sends "Cash", we search for "cash" etc.
                $searchMethod = strtolower(str_replace(' ', '_', $method));

                $title = "Orders Paid via " . ucfirst($method);
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'deleted')
                    ->where('payment_method', $searchMethod)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    });
                break;

            case 'peak_hour_slice':
                $hour = (int) $request->input('hour');
                $title = "Orders at " . sprintf('%02d:00', $hour);
                $columns = [
                    ['key' => 'order_number', 'label' => 'Order #'],
                    ['key' => 'total', 'label' => 'Amount', 'format' => 'currency'],
                    ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
                ];

                // Filter by hour. MongoDB aggregation or simple collection filter?
                // Order::whereRaw check? MongoDB extraction in where is tricky in Eloquent without raw.
                // Let's fetch ranges or use whereTime if supported (whereTime usually for time component).
                // Or filter in memory since we have date range.
                // Ideally:
                // $query->where(function($q) use ($hour) { ... })
                // actually whereTime works for specific time comparisons, not "hour part of any day".

                // For MongoDB, we can use whereRaw with Mongo query syntax if needed, or just iterate.
                // Given pagination limits, iterating might be slow if tons of orders.
                // But for now, let's try a collection filter on the fetched range (which is limited by date).

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('status', '!=', 'deleted')
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get() // Pull into memory (careful with memory)
                    ->filter(function ($order) use ($hour) {
                        return $order->created_at->hour === $hour;
                    })
                    ->sortByDesc('created_at')
                    ->take(50)
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    })
                    ->values(); // reset keys
                break;

            case 'waste_chart_point':
                $date = $request->input('date');
                $title = "Waste for " . $date;
                $columns = [
                    ['key' => 'item_name', 'label' => 'Item'], // Ingredient or Menu Item
                    ['key' => 'quantity', 'label' => 'Qty'],
                    ['key' => 'loss', 'label' => 'Loss', 'format' => 'currency'],
                    ['key' => 'reason', 'label' => 'Reason'],
                    ['key' => 'time', 'label' => 'Time', 'format' => 'datetime']
                ];

                $start = Carbon::parse($date)->startOfDay();
                $end = Carbon::parse($date)->endOfDay();

                $data = WasteLog::where('restaurant_id', $restaurant->id)
                    ->whereBetween('log_date', [$start, $end])
                    ->get()
                    ->map(function ($log) {
                        // Resolve name
                        $name = 'Unknown';
                        if ($log->wasteable_type === 'App\Models\Ingredient') {
                            $ing = Ingredient::find($log->wasteable_id);
                            $name = $ing ? $ing->name : 'Deleted Ingredient';
                        } elseif ($log->wasteable_type === 'App\Models\MenuItem') {
                            $item = \App\Models\MenuItem::find($log->wasteable_id);
                            $name = $item ? $item->name : 'Deleted Item';
                        }

                        if (is_string($name) && str_starts_with($name, '{')) {
                            $decoded = json_decode($name, true);
                            $name = $decoded['en'] ?? $decoded['ar'] ?? 'Unknown';
                        }

                        return [
                            'item_name' => $name,
                            'quantity' => $log->quantity . ' ' . $log->unit,
                            'loss' => $log->total_loss,
                            'reason' => $log->reason,
                            'time' => $log->created_at->toIso8601String()
                        ];
                    });
                break;

            case 'retention_bucket':
                $range = $request->input('range'); // '1', '2', '3-5', '6+'
                $title = "Customers with " . $range . " visit" . ($range === '1' ? '' : 's');

                $columns = [
                    ['key' => 'name', 'label' => __('common.name')],
                    ['key' => 'phone', 'label' => __('common.phone')],
                    ['key' => 'visit_count', 'label' => __('charts.visits'), 'align' => 'center'],
                    ['key' => 'total_spent', 'label' => __('common.total'), 'format' => 'currency', 'align' => 'right'],
                ];

                $data = Order::where('restaurant_id', $restaurant->id)
                    ->where('payment_status', 'paid')
                    ->whereNotNull('customer_id')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('customer')
                    ->get()
                    ->groupBy('customer_id')
                    ->map(function ($orders) use ($range) {
                        $count = $orders->count();

                        $match = false;
                        if ($range === '1' && $count === 1)
                            $match = true;
                        elseif ($range === '2' && $count === 2)
                            $match = true;
                        elseif ($range === '3-5' && $count >= 3 && $count <= 5)
                            $match = true;
                        elseif ($range === '6+' && $count >= 6)
                            $match = true;

                        if (!$match)
                            return null;

                        $customer = $orders->first()->customer;
                        return [
                            'name' => $customer ? $customer->name : 'Unknown',
                            'phone' => $customer ? $customer->phone : 'N/A',
                            'visit_count' => $count,
                            'total_spent' => (float) (string) $orders->sum('total'),
                        ];
                    })
                    ->filter()
                    ->sortByDesc('visit_count')
                    ->values();
                break;
        }

        return response()->json([
            'title' => $title,
            'columns' => $columns,
            'data' => $data
        ]);
    }
}
