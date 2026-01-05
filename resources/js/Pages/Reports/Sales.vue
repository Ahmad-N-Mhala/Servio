<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <div class="flex justify-between items-center mb-8">
                <div>
                   <h1 class="text-3xl font-bold text-gray-900">{{ $t('reports.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $t('reports.subtitle') }}</p> 
                </div>
                
                <!-- Date Filter & Actions -->
                <div class="flex gap-2 items-center">
                    <button 
                        @click="exportReport('sales')"
                        class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        {{ $t('reports.export_report') }}
                    </button>
                    <div class="h-8 w-px bg-gray-200 mx-1"></div>
                    <Input 
                        type="date" 
                        v-model="filters.start_date"
                        placeholder="Start date"
                    />
                    <span class="text-gray-400">{{ $t('reports.to') }}</span>
                    <Input 
                        type="date" 
                        v-model="filters.end_date"
                        placeholder="End date"
                    />
                    <Button @click="applyFilters" variant="primary">{{ $t('reports.apply') }}</Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $t('reports.total_revenue') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $t('reports.total_orders') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_orders }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $t('reports.avg_order_value') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.average_order_value) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                <div class="h-80 w-full">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $t('reports.payment_methods') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Cards -->
                     <button 
                        @click="togglePaymentFilter('cash')"
                        class="bg-white rounded-xl shadow-sm p-6 border transition-all text-left"
                        :class="selectedPaymentMethod === 'cash' ? 'border-green-500 ring-1 ring-green-500 bg-green-50' : 'border-gray-100 hover:border-green-200'"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-green-100 text-green-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('reports.cash') }}</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(paymentStats.cash) }}</p>
                            </div>
                        </div>
                    </button>

                    <button 
                        @click="togglePaymentFilter('card')"
                        class="bg-white rounded-xl shadow-sm p-6 border transition-all text-left"
                        :class="selectedPaymentMethod === 'card' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50' : 'border-gray-100 hover:border-blue-200'"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('reports.card') }}</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(paymentStats.card) }}</p>
                            </div>
                        </div>
                    </button>

                    <button 
                         @click="togglePaymentFilter('online')"
                        class="bg-white rounded-xl shadow-sm p-6 border transition-all text-left"
                        :class="selectedPaymentMethod === 'online' ? 'border-purple-500 ring-1 ring-purple-500 bg-purple-50' : 'border-gray-100 hover:border-purple-200'"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('reports.online') }}</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(paymentStats.online) }}</p>
                            </div>
                        </div>
                    </button>

                     <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 flex flex-col justify-center items-center">
                        <p class="text-sm font-medium text-gray-500 mb-1">{{ $t('reports.total_payments') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(paymentStats.total) }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment History Table -->
            <Table
                :columns="columns"
                :data="paymentHistory.data"
                :pagination="paymentHistory"
                v-model:search="search"
                :title="$t('reports.transaction_history')"
                :server-side="true"
                @sort="handleSort"
            >
                <template #header-actions>
                    <div class="flex items-center gap-4">
                        <span v-if="selectedPaymentMethod" class="text-sm font-normal text-gray-500 capitalize">
                            Filtered by: <strong>{{ selectedPaymentMethod }}</strong>
                        </span>
                        <button 
                            @click="exportReport('payments')"
                            class="text-primary hover:text-primary-hover text-sm font-medium flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            {{ $t('reports.export_csv') }}
                        </button>
                    </div>
                </template>

                <!-- Custom Cell: Date -->
                <template #cell-created_at="{ row }">
                   {{ new Date(row.created_at).toLocaleString() }}
                </template>

                <!-- Custom Cell: Order # -->
                <template #cell-order_number="{ row }">
                    <span class="font-mono text-gray-600">#{{ row.order_number }}</span>
                </template>

                <!-- Custom Cell: Customer/Table -->
                <template #cell-table="{ row }">
                    <div class="flex flex-col">
                        <span class="font-medium">{{ row.table ? row.table.name : 'Takeaway' }}</span>
                        <span class="text-xs text-gray-500">{{ row.customer_name || 'Guest' }}</span>
                    </div>
                </template>

                <!-- Custom Cell: Payment Method -->
                <template #cell-payment_method="{ row }">
                     <span class="px-2 py-1 text-xs font-bold rounded-full capitalize"
                        :class="{
                            'bg-green-100 text-green-800': row.payment_method === 'cash',
                            'bg-blue-100 text-blue-800': row.payment_method === 'card',
                            'bg-purple-100 text-purple-800': row.payment_method === 'online'
                        }"
                    >
                        {{ row.payment_method }}
                    </span>
                </template>

                <!-- Custom Cell: Amount -->
                <template #cell-total="{ row }">
                    <span class="font-bold text-gray-900">{{ formatCurrency(row.total) }}</span>
                </template>

                 <!-- Custom Cell: Waiter -->
                <template #cell-waiter="{ row }">
                    {{ row.waiter ? row.waiter.name : '-' }}
                </template>
            </Table>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Table from '@/Components/Table.vue';
import { debounce } from 'lodash';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const { t } = useI18n();

const props = defineProps<{
    salesData: Array<{ date: string; total: number; count: number }>;
    paymentStats: {
        cash: number;
        card: number;
        online: number;
        total: number;
    };
    paymentHistory: any; 
    stats: {
        total_revenue: number;
        total_orders: number;
        average_order_value: number;
    };
    filters: {
        start_date: string;
        end_date: string;
        search: string;
        payment_method: string | null;
        sort_field?: string;
        sort_direction?: string;
    }
}>();

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = [
    { key: 'created_at', label: t('reports.date_time'), sortable: true },
    { key: 'order_number', label: t('reports.order') + ' #', sortable: true },
    { key: 'table', label: t('reports.customer_table'), sortable: false },
    { key: 'payment_method', label: t('reports.method'), sortable: true },
    { key: 'waiter', label: t('reports.waiter'), sortable: false },
    { key: 'total', label: t('reports.amount'), sortable: true, align: 'right' as const },
];

const filters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    sort_field: props.filters.sort_field || 'created_at',
    sort_direction: props.filters.sort_direction || 'desc'
});

const search = ref(props.filters.search || '');
const selectedPaymentMethod = ref(props.filters.payment_method);

const route = (window as any).route;

const applyFilters = () => {
    router.get(route('reports.sales'), {
        ...filters.value,
        search: search.value,
        payment_method: selectedPaymentMethod.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = (key: string, direction: 'asc' | 'desc') => {
    filters.value.sort_field = key;
    filters.value.sort_direction = direction;
    applyFilters();
};

// Watch for search and payment method changes
watch(search, debounce((value: string) => {
    applyFilters();
}, 500));

const togglePaymentFilter = (method: string) => {
    selectedPaymentMethod.value = selectedPaymentMethod.value === method ? null : method;
    applyFilters();
};

const exportReport = (type: 'sales' | 'payments') => {
    const url = route('reports.export', {
        ...filters.value,
        search: search.value,
        payment_method: selectedPaymentMethod.value,
        type: type
    });
    window.location.href = url;
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-AE', { style: 'currency', currency: currency.value }).format(value);
};

const chartData = computed(() => ({
  labels: props.salesData.map(d => d.date),
  datasets: [
    {
      label: t('reports.daily_revenue'),
      backgroundColor: '#f87979',
      data: props.salesData.map(d => d.total)
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}
</script>

