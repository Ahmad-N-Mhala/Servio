<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Super Admin Dashboard
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Total Restaurants -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">{{ $t('common.total') }} Restaurants</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_restaurants || 0 }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Subscriptions -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Active Subscriptions</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.active_subscriptions || 0 }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">{{ $t('common.total') }} Revenue</p>
                                <p class="text-3xl font-bold mt-2">${{ formatNumber(stats.total_revenue || 0) }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">{{ $t('common.total') }} Orders</p>
                                <p class="text-3xl font-bold mt-2">{{ formatNumber(stats.total_orders || 0) }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users -->
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-pink-100 text-sm font-medium">{{ $t('common.total') }} Users</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_users || 0 }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Plans -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Subscription Plans</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_plans || 0 }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Landing Page Visits -->
                    <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-teal-100 text-sm font-medium">Landing Page Visits</p>
                                <p class="text-3xl font-bold mt-2">{{ formatNumber(stats.landing_visits || 0) }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Restaurant Growth Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Restaurant Growth (Last 6 Months)</h3>
                        <div class="h-72">
                            <canvas ref="restaurantGrowthChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue Trend Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('charts.revenue_trend') }} (Last 6 Months)</h3>
                        <div class="h-72">
                            <canvas ref="revenueTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Subscription Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscriptions by Plan</h3>
                        <div class="h-72">
                            <canvas ref="subscriptionPlanChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Restaurants -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Restaurants by Orders</h3>
                        <div class="h-72">
                            <canvas ref="topRestaurantsChart"></canvas>
                        </div>
                    </div>

                    <!-- Subscription Status -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Status Distribution</h3>
                        <div class="h-72">
                            <canvas ref="subscriptionStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps<{
    stats: any;
    charts: any;
}>();

const restaurantGrowthChart = ref<HTMLCanvasElement | null>(null);
const revenueTrendChart = ref<HTMLCanvasElement | null>(null);
const subscriptionPlanChart = ref<HTMLCanvasElement | null>(null);
const topRestaurantsChart = ref<HTMLCanvasElement | null>(null);
const subscriptionStatusChart = ref<HTMLCanvasElement | null>(null);

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(num);
};

onMounted(() => {
    if (!props.charts) return;

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom' as const,
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14
                },
                bodyFont: {
                    size: 13
                }
            }
        }
    };

    // Restaurant Growth Chart
    if (restaurantGrowthChart.value && props.charts.restaurant_growth) {
        new Chart(restaurantGrowthChart.value, {
            type: 'line',
            data: {
                labels: props.charts.restaurant_growth.map((d: any) => d.month),
                datasets: [{
                    label: 'New Restaurants',
                    data: props.charts.restaurant_growth.map((d: any) => d.count),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Revenue Trend Chart
    if (revenueTrendChart.value && props.charts.revenue_trend) {
        new Chart(revenueTrendChart.value, {
            type: 'bar',
            data: {
                labels: props.charts.revenue_trend.map((d: any) => d.month),
                datasets: [{
                    label: 'Revenue ($)',
                    data: props.charts.revenue_trend.map((d: any) => d.revenue),
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(147, 51, 234, 1)',
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Subscription by Plan Chart
    if (subscriptionPlanChart.value && props.charts.subscription_by_plan) {
        new Chart(subscriptionPlanChart.value, {
            type: 'doughnut',
            data: {
                labels: props.charts.subscription_by_plan.map((d: any) => d.plan_name),
                datasets: [{
                    data: props.charts.subscription_by_plan.map((d: any) => d.count),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.9)',
                        'rgba(16, 185, 129, 0.9)',
                        'rgba(245, 158, 11, 0.9)',
                        'rgba(239, 68, 68, 0.9)',
                        'rgba(139, 92, 246, 0.9)',
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 10,
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        ...chartDefaults.plugins.legend,
                        position: 'right' as const,
                    }
                }
            }
        });
    }

    // Top Restaurants Chart
    if (topRestaurantsChart.value && props.charts.top_restaurants) {
        new Chart(topRestaurantsChart.value, {
            type: 'bar',
            data: {
                labels: props.charts.top_restaurants.map((d: any) => d.name),
                datasets: [{
                    label: 'Total Orders',
                    data: props.charts.top_restaurants.map((d: any) => d.orders),
                    backgroundColor: [
                        'rgba(249, 115, 22, 0.9)',
                        'rgba(251, 146, 60, 0.9)',
                        'rgba(253, 186, 116, 0.9)',
                        'rgba(254, 215, 170, 0.9)',
                        'rgba(255, 237, 213, 0.9)',
                    ],
                    borderColor: 'rgb(249, 115, 22)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                ...chartDefaults,
                indexAxis: 'y' as const,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Subscription Status Chart
    if (subscriptionStatusChart.value && props.charts.subscription_status) {
        new Chart(subscriptionStatusChart.value, {
            type: 'pie',
            data: {
                labels: props.charts.subscription_status.map((d: any) => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
                datasets: [{
                    data: props.charts.subscription_status.map((d: any) => d.count),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.9)',
                        'rgba(239, 68, 68, 0.9)',
                        'rgba(156, 163, 175, 0.9)',
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 10,
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        ...chartDefaults.plugins.legend,
                        position: 'bottom' as const,
                    }
                }
            }
        });
    }
});
</script>
