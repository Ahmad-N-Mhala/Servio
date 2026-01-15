<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

            // Landing Page Visits
            $visitsData = \App\Models\LandingSetting::get('landing_page_visits', ['count' => 0]);
            $stats['landing_visits'] = $visitsData['count'] ?? 0;

            // Restaurant Growth Chart (Last 6 months)
            $charts['restaurant_growth'] = \App\Models\Restaurant::where('created_at', '>=', now()->subMonths(6))
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('M Y');
                })
                ->map(function ($group, $month) {
                    return [
                        'month' => $month,
                        'count' => $group->count()
                    ];
                })
                ->values();

            // Subscription Distribution by Plan
            $charts['subscription_by_plan'] = \App\Models\RestaurantSubscription::with('plan')
                ->where('status', 'active')
                ->get()
                ->groupBy(function ($sub) {
                    return $sub->plan->name ?? 'Unknown';
                })
                ->map(function ($group, $name) {
                    return [
                        'plan_name' => $name,
                        'count' => $group->count()
                    ];
                })
                ->values();

            // Revenue Trend (Last 6 months)
            $charts['revenue_trend'] = \App\Models\Order::where('created_at', '>=', now()->subMonths(6))
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('M Y');
                })
                ->map(function ($group, $month) {
                    return [
                        'month' => $month,
                        'revenue' => round($group->sum('total'), 2)
                    ];
                })
                ->values();

            // Top 5 Restaurants by Orders
            // Efficient way: Aggregate orders by restaurant_id
            $topRestaurantIds = \App\Models\Order::get(['restaurant_id'])
                ->groupBy('restaurant_id')
                ->map(fn($orders) => $orders->count())
                ->sortDesc()
                ->take(5);

            $restaurantNames = \App\Models\Restaurant::whereIn('id', $topRestaurantIds->keys())->pluck('name', 'id');

            $charts['top_restaurants'] = $topRestaurantIds->map(function ($count, $id) use ($restaurantNames) {
                return [
                    'name' => $restaurantNames[$id] ?? 'Unknown',
                    'orders' => $count
                ];
            })->values();

            // Subscription Status Distribution
            $charts['subscription_status'] = \App\Models\RestaurantSubscription::get()
                ->groupBy('status')
                ->map(function ($group, $status) {
                    return [
                        'status' => $status,
                        'count' => $group->count()
                    ];
                })
                ->values();
        }

        return inertia('Admin/Dashboard', [
            'stats' => $stats,
            'charts' => $charts,
        ]);
    }
}
