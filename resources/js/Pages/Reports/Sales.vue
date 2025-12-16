<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <div class="flex justify-between items-center mb-8">
                <div>
                   <h1 class="text-3xl font-bold text-gray-900">Sales Reports</h1>
                    <p class="mt-1 text-sm text-gray-600">Analytics overview for the selected period</p> 
                </div>
                
                <!-- Date Filter -->
                <div class="flex gap-2">
                    <input type="date" v-model="filters.start_date" class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
                    <span class="self-center text-gray-400">to</span>
                    <input type="date" v-model="filters.end_date" class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
                    <button @click="applyFilters" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Apply</button>
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
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
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
                            <p class="text-sm font-medium text-gray-500">Total Orders</p>
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
                            <p class="text-sm font-medium text-gray-500">Avg. Order Value</p>
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
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
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

const props = defineProps<{
    salesData: Array<{ date: string; total: number; count: number }>;
    stats: {
        total_revenue: number;
        total_orders: number;
        average_order_value: number;
    };
    filters: {
        start_date: string;
        end_date: string;
    }
}>();

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);

const filters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date
});

const route = (window as any).route;

const applyFilters = () => {
    router.get(route('reports.sales'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-AE', { style: 'currency', currency: 'AED' }).format(value);
};

const chartData = computed(() => ({
  labels: props.salesData.map(d => d.date),
  datasets: [
    {
      label: 'Daily Revenue',
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

