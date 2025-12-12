<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('dashboard.welcome') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('dashboard.subtitle') }}</p>
            </div>

            <!-- Date Range Picker -->
            <DateRangePicker
                :initial-start-date="dateRange.start_date"
                :initial-end-date="dateRange.end_date"
                @update="onDateRangeUpdate"
            />

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatsCard
                    :title="$t('dashboard.total_orders')"
                    :value="stats.total_orders"
                    icon="orders"
                    color="blue"
                    :trend="12"
                    subtitle="vs previous period"
                    class="cursor-pointer"
                    @click="fetchDetails('total_orders')"
                />
                
                <StatsCard
                    :title="$t('dashboard.today_orders')"
                    :value="stats.today_orders"
                    icon="orders"
                    color="green"
                    :trend="8"
                    subtitle="orders today"
                    class="cursor-pointer"
                    @click="fetchDetails('today_orders')"
                />
                
                <StatsCard
                    :title="$t('dashboard.revenue')"
                    :value="formatCurrency(stats.revenue)"
                    icon="revenue"
                    color="yellow"
                    :trend="15"
                    subtitle="total revenue"
                    class="cursor-pointer"
                    @click="fetchDetails('revenue')"
                />
                
                <StatsCard
                    :title="$t('dashboard.active_staff')"
                    :value="stats.active_staff"
                    icon="staff"
                    color="purple"
                    subtitle="team members"
                    class="cursor-pointer"
                    @click="fetchDetails('active_staff')"
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Trend Chart -->
                <ChartCard title="Revenue Trend" height="300px">
                    <canvas ref="revenueChartCanvas"></canvas>
                </ChartCard>

                <!-- Order Status Distribution -->
                <ChartCard title="Order Status" height="300px">
                    <canvas ref="statusChartCanvas"></canvas>
                </ChartCard>

                <!-- Peak Hours -->
                <ChartCard title="Peak Hours" height="300px">
                    <canvas ref="peakHoursChartCanvas"></canvas>
                </ChartCard>

                <!-- Top Menu Items -->
                <ChartCard title="Top Menu Items" height="300px">
                    <canvas ref="topItemsChartCanvas"></canvas>
                </ChartCard>

                <!-- Avg Completion Time -->
                <ChartCard title="Avg Completion Time (Minutes)" height="300px">
                    <canvas ref="completionTimeChartCanvas"></canvas>
                </ChartCard>
            </div>

            <!-- Recent Orders Table -->
            <Card title="Recent Orders" class="mb-8">
                <div v-if="recentOrders.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ order.order_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ order.customer_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">{{ formatCurrency(order.total) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(order.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ order.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">No recent orders</p>
            </Card>
        </div>

        <!-- Details Modal -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ detailsTitle }}</h2>
                
                <div v-if="loadingDetails" class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <div v-else-if="detailsData.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th v-for="col in detailsColumns" :key="col.key" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ col.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(row, idx) in detailsData" :key="idx" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td v-for="col in detailsColumns" :key="col.key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <template v-if="col.format === 'currency'">{{ formatCurrency(row[col.key]) }}</template>
                                    <template v-else-if="col.format === 'status'">
                                        <span :class="getStatusClass(row[col.key])" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ row[col.key] }}
                                        </span>
                                    </template>
                                     <template v-else-if="col.format === 'datetime'">{{ new Date(row[col.key]).toLocaleString() }}</template>
                                    <template v-else>{{ row[col.key] }}</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No details available.</p>
                
                 <div class="mt-6 flex justify-end">
                    <button @click="showDetailsModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import MainLayout from '@/Layouts/MainLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import StatsCard from '@/Components/StatsCard.vue';
import ChartCard from '@/Components/ChartCard.vue';
import Modal from '@/Components/Modal.vue';
import Card from '@/Components/Card.vue';
import axios from 'axios';

Chart.register(...registerables);

const page = usePage();
const route = (window as any).route;

const isRtl = computed(() => page.props.isRtl as boolean);
const stats = computed(() => page.props.stats as any);
const recentOrders = computed(() => page.props.recent_orders as any[]);
const revenueChart = computed(() => page.props.revenue_chart as any[]);
const statusDistribution = computed(() => page.props.status_distribution as any[]);
const peakHours = computed(() => page.props.peak_hours as any[]);
const topMenuItems = computed(() => page.props.top_menu_items as any[]);
const avgCompletionTime = computed(() => page.props.avg_completion_time as any[]);
const dateRange = computed(() => page.props.date_range as any);

const showDetailsModal = ref(false);
const loadingDetails = ref(false);
const detailsTitle = ref('');
const detailsColumns = ref<any[]>([]);
const detailsData = ref<any[]>([]);

const fetchDetails = async (type: string, params: any = {}) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    detailsData.value = [];
    
    try {
        const response = await axios.get(route('dashboard.details'), {
            params: {
                type,
                start_date: dateRange.value.start_date,
                end_date: dateRange.value.end_date,
                ...params
            }
        });
        
        detailsTitle.value = response.data.title;
        detailsColumns.value = response.data.columns;
        detailsData.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch dashboard details', error);
        detailsTitle.value = 'Error';
    } finally {
        loadingDetails.value = false;
    }
};

const revenueChartCanvas = ref<HTMLCanvasElement | null>(null);
const statusChartCanvas = ref<HTMLCanvasElement | null>(null);
const peakHoursChartCanvas = ref<HTMLCanvasElement | null>(null);
const topItemsChartCanvas = ref<HTMLCanvasElement | null>(null);
const completionTimeChartCanvas = ref<HTMLCanvasElement | null>(null);

let revenueChartInstance: Chart | null = null;
let statusChartInstance: Chart | null = null;
let peakHoursChartInstance: Chart | null = null;
let topItemsChartInstance: Chart | null = null;
let completionTimeChartInstance: Chart | null = null;

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: 'AED',
    }).format(amount);
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    router.get(window.location.pathname, {
        start_date: range.startDate,
        end_date: range.endDate,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const initRevenueChart = () => {
    if (!revenueChartCanvas.value) return;
    
    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }

    const ctx = revenueChartCanvas.value.getContext('2d');
    if (!ctx) return;

    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueChart.value.map((item: any) => item.date),
            datasets: [{
                label: 'Revenue',
                data: revenueChart.value.map((item: any) => item.revenue),
                borderColor: 'rgb(255, 107, 53)',
                backgroundColor: 'rgba(255, 107, 53, 0.1)',
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatCurrency(value as number),
                    },
                },
            },
            onClick: (e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const dataIndex = activeElements[0].index;
                    const date = revenueChart.value[dataIndex].date;
                    fetchDetails('revenue_chart_point', { date });
                }
            },
        },
    });
};

const initStatusChart = () => {
    if (!statusChartCanvas.value) return;
    
    if (statusChartInstance) {
        statusChartInstance.destroy();
    }

    const ctx = statusChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const colors = {
        pending: 'rgb(234, 179, 8)',
        completed: 'rgb(34, 197, 94)',
        cancelled: 'rgb(239, 68, 68)',
    };

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusDistribution.value.map((item: any) => item.status),
            datasets: [{
                data: statusDistribution.value.map((item: any) => item.count),
                backgroundColor: statusDistribution.value.map((item: any) => colors[item.status as keyof typeof colors] || 'rgb(156, 163, 175)'),
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
            onClick: (e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const dataIndex = activeElements[0].index;
                    const status = statusDistribution.value[dataIndex].status;
                    fetchDetails('status_slice', { status });
                }
            },
        },
    });
};

const initPeakHoursChart = () => {
    if (!peakHoursChartCanvas.value) return;
    
    if (peakHoursChartInstance) {
        peakHoursChartInstance.destroy();
    }

    const ctx = peakHoursChartCanvas.value.getContext('2d');
    if (!ctx) return;

    // Fill in missing hours with 0
    const hourlyData = Array.from({ length: 24 }, (_, i) => {
        const found = peakHours.value.find((item: any) => item.hour === i);
        return found ? found.count : 0;
    });

    peakHoursChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
            datasets: [{
                label: 'Orders',
                data: hourlyData,
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });
};

const initTopItemsChart = () => {
    if (!topItemsChartCanvas.value) return;
    
    if (topItemsChartInstance) {
        topItemsChartInstance.destroy();
    }

    const ctx = topItemsChartCanvas.value.getContext('2d');
    if (!ctx) return;

    topItemsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topMenuItems.value.map((item: any) => item.name),
            datasets: [{
                label: 'Quantity Sold',
                data: topMenuItems.value.map((item: any) => item.quantity),
                backgroundColor: 'rgba(168, 85, 247, 0.8)',
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                },
            },
        },
    });
};

const initCompletionTimeChart = () => {
    if (!completionTimeChartCanvas.value) return;
    
    if (completionTimeChartInstance) {
        completionTimeChartInstance.destroy();
    }

    const ctx = completionTimeChartCanvas.value.getContext('2d');
    if (!ctx) return;

    completionTimeChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: avgCompletionTime.value.map((item: any) => item.date),
            datasets: [{
                label: 'Avg Minutes',
                data: avgCompletionTime.value.map((item: any) => item.minutes),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
};

onMounted(() => {
    initRevenueChart();
    initStatusChart();
    initPeakHoursChart();
    initTopItemsChart();
    initCompletionTimeChart();
});

watch([revenueChart, statusDistribution, peakHours, topMenuItems, avgCompletionTime], () => {
    initRevenueChart();
    initStatusChart();
    initPeakHoursChart();
    initTopItemsChart();
    initCompletionTimeChart();
});
</script>
