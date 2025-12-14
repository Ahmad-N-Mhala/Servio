<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = $request->query('restaurant_id');
        $stats = [];
        $charts = [];

        if ($restaurantId) {
            // Restaurant specific stats
            $stats['total_orders'] = \App\Models\Order::where('restaurant_id', $restaurantId)->count();
            $stats['revenue'] = \App\Models\Order::where('restaurant_id', $restaurantId)->sum('total') ?? 0;
            $stats['completed_orders'] = \App\Models\Order::where('restaurant_id', $restaurantId)->where('status', 'completed')->count();
            $stats['pending_orders'] = \App\Models\Order::where('restaurant_id', $restaurantId)->where('status', 'pending')->count();
        } else {
            // System wide stats for Super Admin
            $stats['total_restaurants'] = \App\Models\Restaurant::count();
            $stats['total_users'] = \App\Models\User::count();
            $stats['total_plans'] = \App\Models\Plan::count();
            $stats['active_subscriptions'] = \App\Models\RestaurantSubscription::where('status', 'active')->count();
            $stats['total_orders'] = \App\Models\Order::count();
            $stats['total_revenue'] = \App\Models\Order::sum('total') ?? 0;

            // Restaurant Growth Chart (Last 6 months)
            $charts['restaurant_growth'] = \App\Models\Restaurant::selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => date('M Y', strtotime($item->month)),
                        'count' => $item->count
                    ];
                });

            // Subscription Distribution by Plan
            $charts['subscription_by_plan'] = \App\Models\RestaurantSubscription::join('plans', 'restaurant_subscriptions.plan_id', '=', 'plans.id')
                ->selectRaw('plans.name as plan_name, COUNT(*) as count')
                ->where('restaurant_subscriptions.status', 'active')
                ->groupBy('plans.name')
                ->get();

            // Revenue Trend (Last 6 months)
            $charts['revenue_trend'] = \App\Models\Order::selectRaw('DATE_TRUNC(\'month\', created_at) as month, SUM(total) as revenue')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => date('M Y', strtotime($item->month)),
                        'revenue' => round($item->revenue ?? 0, 2)
                    ];
                });

            // Top 5 Restaurants by Orders
            $charts['top_restaurants'] = \App\Models\Restaurant::withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($restaurant) {
                    return [
                        'name' => $restaurant->name,
                        'orders' => $restaurant->orders_count
                    ];
                });

            // Subscription Status Distribution
            $charts['subscription_status'] = \App\Models\RestaurantSubscription::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();
        }

        return inertia('Admin/Dashboard', [
            'stats' => $stats,
            'charts' => $charts,
        ]);
    }
}
