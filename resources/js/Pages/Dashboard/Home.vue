<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('dashboard.welcome') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('dashboard.subtitle') }}</p>
                </div>
                <div class="flex space-x-2">
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'excel' })"
                        target="_blank"
                    >
                         <Button variant="secondary" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>{{ $t('inventory.download_excel') }}</Button>
                    </a>
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'pdf' })"
                        target="_blank"
                    >
                        <Button variant="primary" class="bg-primary-600 text-white hover:bg-primary-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>{{ $t('common.export') }} PDF</Button>
                    </a>
                </div>
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
                    class="cursor-pointer"
                    @click="fetchDetails('total_orders')"
                />
                
                <StatsCard
                    :title="$t('dashboard.revenue')"
                    :value="formatCurrency(stats.revenue)"
                    icon="revenue"
                    color="yellow"
                    :subtitle="$t('dashboard_page.total_revenue_subtitle')"
                    class="cursor-pointer"
                    @click="fetchDetails('revenue')"
                />

                <StatsCard
                    :title="$t('dashboard_page.net_profit')"
                    :value="formatCurrency(stats.net_profit)"
                    icon="revenue"
                    :color="stats.net_profit >= 0 ? 'green' : 'red'"
                    :subtitle="$t('dashboard_page.net_profit_subtitle')"
                />

                <StatsCard
                    :title="$t('dashboard_page.low_stock')"
                    :value="stats.low_stock_count"
                    icon="waste"
                    color="red"
                    :subtitle="$t('dashboard_page.low_stock')"
                    class="cursor-pointer"
                    @click="fetchDetails('low_stock')"
                />

                <StatsCard
                    :title="$t('dashboard_page.avg_dining_time')"
                    :value="stats.avg_dining_time + ' ' + $t('kitchen.min')"
                    icon="time"
                    color="purple"
                    :subtitle="$t('dashboard_page.avg_dining_time_subtitle')"
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Trend Chart -->
                <ChartCard :title="$t('charts.revenue_trend')" height="300px">
                    <canvas ref="revenueChartCanvas"></canvas>
                </ChartCard>

                <!-- Order Status Distribution -->
                <ChartCard :title="$t('charts.order_status')" height="300px">
                    <canvas ref="statusChartCanvas"></canvas>
                </ChartCard>

                <!-- Payment Methods -->
                <ChartCard :title="$t('charts.payment_methods')" height="300px">
                    <canvas ref="paymentChartCanvas"></canvas>
                </ChartCard>

                <!-- Top Menu Items -->
                <ChartCard :title="$t('charts.top_menu_items')" height="300px">
                    <canvas ref="topItemsChartCanvas"></canvas>
                </ChartCard>

                <!-- Peak Hours -->
                <ChartCard :title="$t('charts.peak_hours')" height="300px">
                    <canvas ref="peakHoursChartCanvas"></canvas>
                </ChartCard>

                <!-- Waste Trend -->
                <ChartCard :title="$t('charts.waste_trend')" height="300px">
                    <canvas ref="wasteChartCanvas"></canvas>
                </ChartCard>

                <!-- Top Categories -->
                <ChartCard :title="$t('charts.top_categories')" height="300px">
                    <canvas ref="topCategoriesChartCanvas"></canvas>
                </ChartCard>

                <!-- Top Customers -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('charts.top_customers') }}</h3>
                     <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.name') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('charts.orders') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('common.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(customer, idx) in topCustomers" :key="idx">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ customer.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 text-right">{{ customer.count }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white text-right">{{ formatCurrency(customer.total) }}</td>
                                </tr>
                                <tr v-if="topCustomers.length === 0">
                                    <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">{{ $t('charts.no_data') }}</td>
                                </tr>
                            </tbody>
                        </table>
                     </div>
                </div>
             <!-- Customer Retention -->
             <div class="col-span-1 lg:col-span-2 mb-8">
                <ChartCard :title="$t('charts.customer_retention')" height="300px">
                    <canvas ref="retentionChartCanvas"></canvas>
                </ChartCard>
             </div>
            </div>
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
                
                <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">{{ $t('common.no_details') }}</p>
                
                 <div class="mt-6 flex justify-end">
                    <button @click="showDetailsModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">{{ $t('common.close') }}</button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import StatsCard from '@/Components/StatsCard.vue';
import ChartCard from '@/Components/ChartCard.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import axios from 'axios';

const { t } = useI18n();

Chart.register(...registerables);

const page = usePage();
const route = (window as any).route;

const isRtl = computed(() => page.props.isRtl as boolean);
const stats = computed(() => page.props.stats as any);
const revenueChart = computed(() => page.props.revenue_chart as any[]);
const statusDistribution = computed(() => page.props.status_distribution as any[]);
const peakHours = computed(() => page.props.peak_hours as any[]);
const topMenuItems = computed(() => page.props.top_menu_items as any[]);
const paymentDistribution = computed(() => page.props.payment_distribution as any[]);
const wasteChart = computed(() => page.props.waste_chart as any[]);
const topCategories = computed(() => page.props.top_categories as any[] || []);
const topCustomers = computed(() => page.props.top_customers as any[] || []);
const dateRange = computed(() => page.props.date_range as any);
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const retentionStats = computed(() => page.props.retention_stats as any[] || []);

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
        detailsTitle.value = t('common.error');
    } finally {
        loadingDetails.value = false;
    }
};

const revenueChartCanvas = ref<HTMLCanvasElement | null>(null);
const statusChartCanvas = ref<HTMLCanvasElement | null>(null);
const peakHoursChartCanvas = ref<HTMLCanvasElement | null>(null);
const topItemsChartCanvas = ref<HTMLCanvasElement | null>(null);
const paymentChartCanvas = ref<HTMLCanvasElement | null>(null);
const wasteChartCanvas = ref<HTMLCanvasElement | null>(null);
const topCategoriesChartCanvas = ref<HTMLCanvasElement | null>(null);
const retentionChartCanvas = ref<HTMLCanvasElement | null>(null);

let revenueChartInstance: Chart | null = null;
let statusChartInstance: Chart | null = null;
let peakHoursChartInstance: Chart | null = null;
let topItemsChartInstance: Chart | null = null;
let paymentChartInstance: Chart | null = null;
let wasteChartInstance: Chart | null = null;
let topCategoriesChartInstance: Chart | null = null;
let retentionChartInstance: Chart | null = null;

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: currency.value,
    }).format(amount);
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        ready: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
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
                label: t('charts.revenue'),
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
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const dataIndex = firstElement.index;
                        const date = revenueChart.value[dataIndex].date;
                        fetchDetails('revenue_chart_point', { date });
                    }
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
        pending: 'rgb(234, 179, 8)', // Yellow
        processing: 'rgb(59, 130, 246)', // Blue
        ready: 'rgb(168, 85, 247)', // Purple
        completed: 'rgb(34, 197, 94)', // Green
        cancelled: 'rgb(239, 68, 68)', // Red
    };

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusDistribution.value.map((item: any) => t('orders.' + item.status)),
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
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const dataIndex = firstElement.index;
                        const status = statusDistribution.value[dataIndex].status;
                        fetchDetails('status_slice', { status });
                    }
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
                label: t('charts.orders'),
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
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const hour = firstElement.index; // 0-23
                        fetchDetails('peak_hour_slice', { hour });
                    }
                }
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
                label: t('charts.quantity_sold'),
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

const initPaymentChart = () => {
    if (!paymentChartCanvas.value) return;
    
    if (paymentChartInstance) {
        paymentChartInstance.destroy();
    }

    const ctx = paymentChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const colors = [
        'rgb(34, 197, 94)', // Green (Cash)
        'rgb(59, 130, 246)', // Blue (Card)
        'rgb(168, 85, 247)', // Purple (Online)
        'rgb(234, 179, 8)', // Yellow
    ];

    paymentChartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: paymentDistribution.value.map((item: any) => t('orders.' + item.method)),
            datasets: [{
                data: paymentDistribution.value.map((item: any) => item.total),
                backgroundColor: colors,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${formatCurrency(value)}`;
                        }
                    }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const index = firstElement.index;
                        const method = paymentDistribution.value[index].method;
                        fetchDetails('payment_method_slice', { method });
                    }
                }
             },
        },
    });
};

const initWasteChart = () => {
    if (!wasteChartCanvas.value) return;
    
    if (wasteChartInstance) {
        wasteChartInstance.destroy();
    }

    const ctx = wasteChartCanvas.value.getContext('2d');
    if (!ctx) return;

    wasteChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: wasteChart.value.map((item: any) => item.date),
            datasets: [{
                label: t('charts.waste_value'),
                data: wasteChart.value.map((item: any) => item.loss),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
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
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const index = firstElement.index;
                        const date = wasteChart.value[index].date;
                        fetchDetails('waste_chart_point', { date });
                    }
                }
            },
        },
    });
};

const initTopCategoriesChart = () => {
    if (!topCategoriesChartCanvas.value) return;
    
    if (topCategoriesChartInstance) {
        topCategoriesChartInstance.destroy();
    }

    const ctx = topCategoriesChartCanvas.value.getContext('2d');
    if (!ctx) return;

    // Generate random colors or a fixed palette
    const colors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
    ];

    topCategoriesChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: topCategories.value.map((item: any) => item.name),
            datasets: [{
                label: t('charts.sales'),
                data: topCategories.value.map((item: any) => item.value),
                backgroundColor: colors.slice(0, topCategories.value.length),
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
             plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                     callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${formatCurrency(value)}`;
                        }
                    }
                }
            },
        },
    });
};

const initRetentionChart = () => {
    if (!retentionChartCanvas.value) return;
    
    if (retentionChartInstance) {
        retentionChartInstance.destroy();
    }

    const ctx = retentionChartCanvas.value.getContext('2d');
    if (!ctx) return;

    retentionChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: retentionStats.value.map((item: any) => item.label),
            datasets: [{
                label: t('charts.retention'),
                data: retentionStats.value.map((item: any) => item.percentage),
                backgroundColor: 'rgba(16, 185, 129, 0.8)', // Green
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const index = context.dataIndex;
                            const item = retentionStats.value[index];
                            return `Retention: ${item.percentage}% (${item.count} customers)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%'
                    }
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
    initPaymentChart();
    initWasteChart();
    initTopCategoriesChart();
    initRetentionChart();
});

watch([revenueChart, statusDistribution, peakHours, topMenuItems, paymentDistribution, wasteChart, topCategories, retentionStats], () => {
    initRevenueChart();
    initStatusChart();
    initPeakHoursChart();
    initTopItemsChart();
    initPaymentChart();
    initWasteChart();
    initTopCategoriesChart();
    initRetentionChart();
});
</script>
