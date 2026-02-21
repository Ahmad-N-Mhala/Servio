<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('dashboard.welcome') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('dashboard.subtitle') }}</p>
                </div>
                <div class="flex gap-4">
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'excel', tab: currentTab })"
                        target="_blank"
                    >
                         <Button variant="secondary" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>{{ $t('inventory.download_excel') }}</Button>
                    </a>
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'pdf', tab: currentTab })"
                        target="_blank"
                    >
                        <Button variant="primary" class="bg-primary-600 text-white hover:bg-primary-700 px-4">
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
                class="mb-6"
            />

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        @click="switchTab('overview')"
                        :class="[
                            currentTab === 'overview'
                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ $t('dashboard.overview') }}
                    </button>
                    <button
                        @click="switchTab('item_sales')"
                        :class="[
                            currentTab === 'item_sales'
                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ $t('dashboard.item_sales') }}
                    </button>
                </nav>
            </div>

            <!-- Overview Content -->
            <div v-if="currentTab === 'overview'" class="space-y-8">
                
                <!-- 1. Highlights Section -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                         <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
                            {{ $t('dashboard.highlights') }}
                         </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <StatsCard
                            :title="$t('dashboard.sales')"
                            :value="formatCurrency(highlights.sales)"
                            icon="revenue"
                            color="green"
                            @click="fetchDetails('selection_sales')"
                            class="cursor-pointer hover:shadow-md transition-shadow"
                        />
                        <StatsCard
                            :title="$t('dashboard.total_orders')"
                            :value="highlights.orders"
                            icon="orders"
                            color="blue"
                            @click="fetchDetails('total_orders')"
                            class="cursor-pointer hover:shadow-md transition-shadow"
                        />
                         <StatsCard
                            :title="$t('dashboard.customers')"
                            :value="highlights.customers"
                            :subtitle="`${highlights.new_customers} ${$t('dashboard.new_customers')} / ${highlights.repeat_customers} ${$t('dashboard.repeat_customers')}`"
                            icon="customers"
                            color="purple"
                            @click="fetchDetails('new_customers')"
                            class="cursor-pointer hover:shadow-md transition-shadow"
                        />
                         <StatsCard
                            :title="$t('dashboard.rewards_redeemed')"
                            :value="highlights.rewards_redeemed"
                            icon="gift"
                            color="yellow"
                            @click="fetchDetails('rewards_redeemed')"
                            class="cursor-pointer hover:shadow-md transition-shadow"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <ChartCard :title="$t('dashboard.period_sales')" :subtitle="formatCurrency(periodSales.total)" height="300px">
                         <canvas ref="revenueChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.period_visits')" :subtitle="periodVisits.total.toString()" height="300px">
                         <canvas ref="visitsChartCanvas"></canvas>
                    </ChartCard>
                </div>

                <!-- 3. Distribution & Trends -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ChartCard :title="$t('dashboard.payment_methods')" height="300px">
                        <canvas ref="paymentChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.order_status')" height="300px">
                        <canvas ref="statusChartCanvas"></canvas>
                    </ChartCard>
                     <ChartCard :title="$t('dashboard.waste_trend')" height="300px">
                        <canvas ref="wasteChartCanvas"></canvas>
                    </ChartCard>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <ChartCard :title="$t('dashboard.peak_hours')" height="300px">
                        <canvas ref="peakHoursChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.avg_completion_time')" height="300px">
                        <canvas ref="completionTimeChartCanvas"></canvas>
                    </ChartCard>
                </div>



                <!-- 4. Top Insights & Popular Times -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Top Insights Grid -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 lg:col-span-2">
                         <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $t('dashboard.top_insights') }}</h3>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                             <!-- Pareto -->
                             <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                 <p class="text-sm text-gray-500 mb-1">{{ $t('dashboard.revenue_distribution') }}</p>
                                 <div class="flex items-end gap-2">
                                     <span class="text-3xl font-bold text-primary-600">{{ topInsights.pareto_percent }}%</span>
                                     <span class="text-xs text-gray-400 mb-1">from top 20% customers</span>
                                 </div>
                             </div>
                             <!-- AOV -->
                             <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                 <p class="text-sm text-gray-500 mb-1">{{ $t('dashboard.avg_order_value') }}</p>
                                 <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(topInsights.avg_order_value) }}</div>
                             </div>
                             <!-- Avg Items -->
                             <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                 <p class="text-sm text-gray-500 mb-1">{{ $t('dashboard.avg_items_per_order') }}</p>
                                 <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ topInsights.avg_items_per_order }}</div>
                             </div>
                             <!-- Avg Visits -->
                             <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                 <p class="text-sm text-gray-500 mb-1">{{ $t('dashboard.avg_visits_per_year') }}</p>
                                 <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ topInsights.avg_visits_per_year }}</div>
                             </div>
                         </div>
                    </div>

                    <!-- Popular Times -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $t('dashboard.popular_times') }}</h3>
                        <div class="space-y-6">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">{{ $t('dashboard.most_popular') }}</p>
                                <p v-if="popularTimes.most_popular" class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ popularTimes.most_popular.label }}
                                </p>
                                <p v-if="popularTimes.most_popular" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ popularTimes.most_popular.orders }} orders
                                </p>
                                <p v-else class="text-sm text-gray-500">N/A</p>
                            </div>
                            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-xl">
                                <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide mb-2">{{ $t('dashboard.least_popular') }}</p>
                                <p v-if="popularTimes.least_popular" class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ popularTimes.least_popular.label }}
                                </p>
                                <p v-if="popularTimes.least_popular" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ popularTimes.least_popular.orders }} orders
                                </p>
                                <p v-else class="text-sm text-gray-500">N/A</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Lists: Customer Frequency, Rewards, Top Items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Customer Frequency -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $t('dashboard.customer_frequency') }}</h3>
                        <div class="space-y-4">
                             <div v-for="(count, label) in customerFrequency" :key="label" 
                                class="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 p-1 rounded transition-colors"
                                @click="fetchDetails('retention_bucket', { range: String(label) })"
                             >
                                 <span class="text-sm text-gray-600 dark:text-gray-400">{{ $t('dashboard.visit_' + String(label).replace('+', '_plus').replace('-', '_')) }}</span>
                                 <div class="flex items-center gap-3">
                                     <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                         <div class="h-full bg-primary-500" :style="`width: ${Math.min(100, (count / (customerInsights.total || 1)) * 100)}%`"></div>
                                     </div>
                                     <span class="text-sm font-bold text-gray-900 dark:text-white w-8 text-right">{{ count }}</span>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- Top Rewards -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $t('dashboard.top_rewards') }}</h3>
                        <div v-if="topRewards.length > 0" class="space-y-4">
                            <div v-for="(reward, idx) in topRewards" :key="idx" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg">
                                <span class="w-6 h-6 flex items-center justify-center bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold">{{ idx + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ reward.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ reward.description }}</p>
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ reward.count }}</span>
                            </div>
                        </div>
                         <p v-else class="text-sm text-gray-500 text-center py-4">{{ $t('common.no_results') }}</p>
                    </div>

                    <!-- Top Items -->
                     <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $t('dashboard.top_menu_items') }}</h3>
                        <div v-if="topItems.length > 0" class="space-y-4">
                             <div v-for="(item, idx) in topItems" :key="idx" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg">
                                <span class="w-6 h-6 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ idx + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ item.quantity }} sold</span>
                            </div>
                        </div>
                         <p v-else class="text-sm text-gray-500 text-center py-4">{{ $t('common.no_results') }}</p>
                    </div>
                </div>

            </div>

             <!-- Item Sales Tab (Existing) -->
            <div v-if="currentTab === 'item_sales'" class="space-y-6">
                <!-- Table -->
                <Table
                    :columns="itemSalesColumns"
                    :data="itemSalesList"
                    :pagination="itemSalesPagination"
                    v-model:search="searchQuery"
                    :title="$t('dashboard.item_sales_report')"
                    :empty-message="$t('common.no_results')"
                    :server-side="true"
                    @sort="handleTableSort"
                >
                    <template #cell-name="{ row }">
                         <span class="font-medium text-gray-900 dark:text-white">{{ row.name }}</span>
                    </template>
                    <template #cell-revenue="{ row }">
                        {{ formatCurrency(row.revenue) }}
                    </template>
                </Table>
            </div>
        </div>

        <!-- Details Modal (retained for backward compatibility if needed, though mostly unused in new layout for now) -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false" max-width="4xl">
             <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ detailsTitle }}</h2>
                    <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div v-if="loadingDetails" class="py-12 flex flex-col items-center justify-center space-y-4">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600"></div>
                    <p class="text-gray-500">{{ $t('common.loading') }}</p>
                </div>
                
                <div v-else>
                    <div v-if="detailsData.length > 0" class="overflow-x-auto">
                        <Table
                            :columns="detailsColumns"
                            :data="detailsData"
                            :title="''"
                            :show-search="false"
                        >
                            <template v-for="col in detailsColumns" :key="col.key" v-slot:[`cell-${col.key}`]="{ row }">
                                <template v-if="col.format === 'currency'">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(row[col.key]) }}</span>
                                </template>
                                <template v-else-if="col.format === 'datetime'">
                                    <span class="text-gray-500 whitespace-nowrap">{{ new Date(row[col.key]).toLocaleString() }}</span>
                                </template>
                                <template v-else-if="col.format === 'status'">
                                    <span :class="{
                                        'px-2 py-0.5 rounded text-xs font-medium': true,
                                        'bg-green-100 text-green-800': row[col.key] === 'completed',
                                        'bg-blue-100 text-blue-800': row[col.key] === 'preparing' || row[col.key] === 'ready',
                                        'bg-yellow-100 text-yellow-800': row[col.key] === 'pending',
                                        'bg-red-100 text-red-800': row[col.key] === 'cancelled',
                                        'bg-gray-100 text-gray-800': row[col.key] === 'deleted'
                                    }">
                                        {{ $t('common.' + row[col.key]) || row[col.key] }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-gray-700 dark:text-gray-300">{{ row[col.key] }}</span>
                                </template>
                            </template>
                        </Table>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-gray-500">{{ $t('common.no_results') }}</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button @click="showDetailsModal = false" variant="secondary">{{ $t('common.close') }}</Button>
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
import Table from '@/Components/Table.vue';
import axios from 'axios';

// Simple debounce
const debounce = (fn: Function, wait: number) => {
    let timeout: any;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
};

const { t } = useI18n();
Chart.register(...registerables);

const page = usePage();
const dateRange = computed(() => page.props.date_range as any);
const currentTab = computed(() => (page.props.active_tab as string) || 'overview');
const isRtl = computed(() => page.props.isRtl as boolean);
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

// Data Props
const highlights = computed(() => page.props.highlights as any || { sales: 0, orders: 0, customers: 0, rewards_redeemed: 0, new_customers: 0, repeat_customers: 0 });
const periodSales = computed(() => page.props.period_sales as any || { total: 0, valid_count: 0, blocked_count: 0, chart: [] });
const periodVisits = computed(() => page.props.period_visits as any || { total: 0, chart: [] });
const customerInsights = computed(() => page.props.customer_insights as any || { total: 0, active: 0, inactive: 0 });

const topInsights = computed(() => page.props.top_insights as any || { pareto_percent: 0, avg_order_value: 0, avg_items_per_order: 0, avg_visits_per_year: 0 });
const popularTimes = computed(() => page.props.popular_times as any || {});
const customerFrequency = computed(() => page.props.customer_frequency as any || {});
const topRewards = computed(() => page.props.top_rewards as any[] || []);
const topItems = computed(() => page.props.top_items as any[] || []);
const paymentDistribution = computed(() => page.props.payment_distribution as any[] || []);
const statusDistribution = computed(() => page.props.status_distribution as any[] || []);
const peakHours = computed(() => page.props.peak_hours as any[] || []);
const wasteTrend = computed(() => page.props.waste_chart as any[] || []);
const completionTimeTrend = computed(() => page.props.avg_completion_time as any[] || []);
// Legacy/Other props
const itemSalesData = computed(() => page.props.item_sales_data as any);
const filters = computed(() => page.props.filters as any || {});


// Tables Logic
const searchQuery = ref(filters.value.q || '');
const currentSort = ref(filters.value.sort || 'quantity_desc');
const itemSalesColumns = computed(() => [
    { key: 'name', label: t('common.item'), sortable: true, align: 'center' as const },
    { key: 'category', label: t('common.category'), sortable: false, align: 'center' as const },
    { key: 'quantity', label: t('dashboard.quantity_sold'), sortable: true, align: 'center' as const },
    { key: 'revenue', label: t('common.revenue'), sortable: true, format: 'currency' as const, align: 'center' as const }
]);
const itemSalesList = computed(() => itemSalesData.value?.data || []);
const itemSalesPagination = computed(() => itemSalesData.value || {});

const handleTableSort = (key: string, direction: 'asc' | 'desc') => {
    const sortValue = `${key}_${direction}`;
    currentSort.value = sortValue;
    updateParams({ sort: sortValue, page: 1 });
};

watch(searchQuery, debounce((val: string) => {
    if (currentTab.value === 'item_sales') updateParams({ q: val, page: 1 });
}, 500));

// Navigation
const updateParams = (params: any) => {
    router.get(window.location.pathname, {
        start_date: dateRange.value.start_date,
        end_date: dateRange.value.end_date,
        tab: currentTab.value,
        ...params
    }, { preserveState: true, preserveScroll: true });
};

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    const params: any = { start_date: range.startDate, end_date: range.endDate, tab: currentTab.value };
    if (currentTab.value === 'item_sales') {
        params.q = searchQuery.value;
        params.sort = currentSort.value;
    }
    router.get(window.location.pathname, params, { preserveState: true, preserveScroll: true });
};

const switchTab = (tab: string) => {
    router.get(window.location.pathname, {
        start_date: dateRange.value.start_date,
        end_date: dateRange.value.end_date,
        tab: tab
    }, { preserveState: true, preserveScroll: true });
};

// Utils
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-AE', { style: 'currency', currency: currency.value }).format(amount);


// Charts
const revenueChartCanvas = ref<HTMLCanvasElement | null>(null);
const visitsChartCanvas = ref<HTMLCanvasElement | null>(null);
const paymentChartCanvas = ref<HTMLCanvasElement | null>(null);
const statusChartCanvas = ref<HTMLCanvasElement | null>(null);
const peakHoursChartCanvas = ref<HTMLCanvasElement | null>(null);
const wasteChartCanvas = ref<HTMLCanvasElement | null>(null);
const completionTimeChartCanvas = ref<HTMLCanvasElement | null>(null);

let revenueChartInstance: Chart | null = null;
let visitsChartInstance: Chart | null = null;
let paymentChartInstance: Chart | null = null;
let statusChartInstance: Chart | null = null;
let peakHoursChartInstance: Chart | null = null;
let wasteChartInstance: Chart | null = null;
let completionTimeChartInstance: Chart | null = null;

const initRevenueChart = () => {
    if (!revenueChartCanvas.value) return;
    if (revenueChartInstance) revenueChartInstance.destroy();
    
    const chartData = periodSales.value.chart || [];
    
    const ctx = revenueChartCanvas.value.getContext('2d');
    if (!ctx) return;
    
    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map((d: any) => d.date),
            datasets: [{
                label: t('dashboard.sales'),
                data: chartData.map((d: any) => d.revenue),
                borderColor: 'rgb(16, 185, 129)', // Green
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (val) => formatCurrency(val as number) } },
                x: { grid: { display: false } }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const dataIndex = firstElement.index;
                        const chartData = periodSales.value.chart || [];
                        if (chartData[dataIndex]) {
                            const date = chartData[dataIndex].date;
                            fetchDetails('revenue_chart_point', { date });
                        }
                    }
                }
            }
        }
    });
};

const initVisitsChart = () => {
    // ... existing initVisitsChart ...
    if (!visitsChartCanvas.value) return;
    if (visitsChartInstance) visitsChartInstance.destroy();
    const chartData = periodVisits.value.chart || [];
    const ctx = visitsChartCanvas.value.getContext('2d');
    if (!ctx) return;
    visitsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map((d: any) => d.date),
            datasets: [{
                label: t('dashboard.period_visits'),
                data: chartData.map((d: any) => d.count),
                backgroundColor: 'rgb(59, 130, 246)', // Blue
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
};

const initPaymentChart = () => {
    if (!paymentChartCanvas.value) return;
    if (paymentChartInstance) paymentChartInstance.destroy();
    const ctx = paymentChartCanvas.value.getContext('2d');
    if (!ctx) return;

    paymentChartInstance = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: paymentDistribution.value.map(d => d.method),
            datasets: [{
                data: paymentDistribution.value.map(d => d.value),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(239, 68, 68, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: function(context: any) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.raw !== null && context.raw !== undefined) {
                                label += formatCurrency(context.raw);
                            }
                            return label;
                        }
                    }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('payment_method_slice', { method: paymentDistribution.value[idx].method });
                }
            }
        }
    });
};

const initStatusChart = () => {
    if (!statusChartCanvas.value) return;
    if (statusChartInstance) statusChartInstance.destroy();
    const ctx = statusChartCanvas.value.getContext('2d');
    if (!ctx) return;

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusDistribution.value.map(d => t('common.' + d.status) || d.status),
            datasets: [{
                data: statusDistribution.value.map(d => d.count),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)', // completed
                    'rgba(245, 158, 11, 0.7)', // pending
                    'rgba(59, 130, 246, 0.7)', // preparing
                    'rgba(239, 68, 68, 0.7)',  // cancelled
                    'rgba(107, 114, 128, 0.7)' // deleted
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('status_slice', { status: statusDistribution.value[idx].status });
                }
            }
        }
    });
};

const initPeakHoursChart = () => {
    if (!peakHoursChartCanvas.value) return;
    if (peakHoursChartInstance) peakHoursChartInstance.destroy();
    const ctx = peakHoursChartCanvas.value.getContext('2d');
    if (!ctx) return;

    peakHoursChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: peakHours.value.map(d => `${d.hour}:00`),
            datasets: [{
                label: t('dashboard.orders'),
                data: peakHours.value.map(d => d.count),
                backgroundColor: 'rgba(139, 92, 246, 0.6)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('peak_hour_slice', { hour: peakHours.value[idx].hour });
                }
            }
        }
    });
};

const initWasteChart = () => {
    // ... existing initWasteChart ...
    if (!wasteChartCanvas.value) return;
    if (wasteChartInstance) wasteChartInstance.destroy();
    const ctx = wasteChartCanvas.value.getContext('2d');
    if (!ctx) return;
    wasteChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: wasteTrend.value.map(d => d.date),
            datasets: [{
                label: t('dashboard.waste_loss'),
                data: wasteTrend.value.map(d => d.loss),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (val) => formatCurrency(val as number) } },
                x: { grid: { display: false } }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('waste_chart_point', { date: wasteTrend.value[idx].date });
                }
            }
        }
    });
};

const initCompletionTimeChart = () => {
    if (!completionTimeChartCanvas.value) return;
    if (completionTimeChartInstance) completionTimeChartInstance.destroy();
    const ctx = completionTimeChartCanvas.value.getContext('2d');
    if (!ctx) return;

    completionTimeChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: completionTimeTrend.value.map(d => d.date),
            datasets: [{
                label: t('dashboard.minutes'),
                data: completionTimeTrend.value.map(d => d.minutes),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: t('dashboard.minutes') } },
                x: { grid: { display: false } }
            }
        }
    });
};

const route = (window as any).route;

// Modals
const showDetailsModal = ref(false);
const loadingDetails = ref(false);
const detailsTitle = ref('');
const detailsColumns = ref<any[]>([]);
const detailsData = ref<any[]>([]);
const currentDetailType = ref('');
const currentDetailParams = ref<any>({});

const fetchDetails = async (type: string, params: any = {}) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    detailsData.value = [];
    currentDetailType.value = type;
    currentDetailParams.value = params;
    
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

onMounted(() => {
    if (currentTab.value === 'overview') {
        initRevenueChart();
        initVisitsChart();
        initPaymentChart();
        initStatusChart();
        initPeakHoursChart();
        initWasteChart();
        initCompletionTimeChart();
    }
});

watch(() => page.props.period_sales, () => {
    if (currentTab.value === 'overview') initRevenueChart();
}, { deep: true });

watch(() => page.props.period_visits, () => {
    if (currentTab.value === 'overview') {
        initRevenueChart();
        initVisitsChart();
        initPaymentChart();
        initStatusChart();
        initPeakHoursChart();
        initWasteChart();
        initCompletionTimeChart();
    }
}, { deep: true });

</script>
