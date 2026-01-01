<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cash Register History</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">View historical cash register sessions and transactions</p>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                    </div>

                    <div class="flex items-end">
                        <button
                            @click="applyFilters"
                            class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors font-medium"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Register Sessions -->
            <div v-if="registers.data.length > 0" class="space-y-6">
                <div v-for="register in registers.data" :key="register.id" class="glass-card rounded-2xl overflow-hidden border border-gray-200">
                    <!-- Session Header -->
                    <div class="bg-gray-50 dark:bg-gray-800 p-6 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ formatDate(register.opened_at) }}
                                    </h3>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        register.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                                    ]">
                                        {{ register.status.toUpperCase() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">Cashier: {{ register.user?.name || 'Unknown' }}</p>
                                <p class="text-xs text-gray-400">
                                    Opened: {{ formatTime(register.opened_at) }}
                                    <span v-if="register.closed_at"> • Closed: {{ formatTime(register.closed_at) }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a 
                                    :href="route('cash-register.export', register.id)"
                                    class="px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 flex items-center gap-2"
                                    target="_blank"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export CSV
                                </a>
                                <button
                                    @click="toggleDetails(register.id)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    {{ expandedRegisters.includes(register.id) ? 'Hide Details' : 'Show Details' }}
                                </button>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-4">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Opening</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.opening_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Expected</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.expected_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Actual</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.closing_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Difference</p>
                                <p class="text-lg font-bold" :class="{
                                    'text-green-600': register.difference > 0,
                                    'text-red-600': register.difference < 0,
                                    'text-gray-600': register.difference === 0
                                }">
                                    {{ register.difference > 0 ? '+' : '' }}{{ formatCurrency(register.difference) }}
                                </p>
                            </div>
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Transactions</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ register.transactions?.length || 0 }}</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="register.opening_notes || register.closing_notes" class="mt-4 space-y-2">
                            <div v-if="register.opening_notes" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                                <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mb-1">Opening Notes:</p>
                                <p class="text-sm text-blue-900 dark:text-blue-100">{{ register.opening_notes }}</p>
                            </div>
                            <div v-if="register.closing_notes" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                                <p class="text-xs font-medium text-purple-700 dark:text-purple-300 mb-1">Closing Notes:</p>
                                <p class="text-sm text-purple-900 dark:text-purple-100">{{ register.closing_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details (Expandable) -->
                    <div v-if="expandedRegisters.includes(register.id)" class="p-6">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Transaction History</h4>
                        
                        <div v-if="register.transactions && register.transactions.length > 0" class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in register.transactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ formatTime(transaction.created_at) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold capitalize" :class="{
                                                'bg-green-100 text-green-700': transaction.type === 'sale' || transaction.type === 'deposit',
                                                'bg-red-100 text-red-700': transaction.type === 'withdrawal',
                                                'bg-blue-100 text-blue-700': transaction.type === 'opening',
                                                'bg-gray-100 text-gray-700': transaction.type === 'closing'
                                            }">
                                                {{ transaction.type }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium" :class="{
                                            'text-green-600': transaction.amount > 0,
                                            'text-red-600': transaction.amount < 0,
                                            'text-gray-600': transaction.amount === 0
                                        }">
                                            {{ transaction.amount > 0 ? '+' : '' }}{{ formatCurrency(transaction.amount) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">
                                            {{ formatCurrency(transaction.balance_after) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <div v-if="transaction.order">
                                                <span class="font-medium text-gray-900 dark:text-gray-100">Order #{{ transaction.order.order_number }}</span>
                                            </div>
                                            <div v-else>
                                                {{ transaction.notes || '-' }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No transactions recorded
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="glass-card rounded-2xl p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Records Found</h3>
                <p class="text-gray-500">No cash register sessions match your filters</p>
            </div>

            <!-- Pagination -->
            <div v-if="registers.data.length > 0" class="mt-6 flex justify-center">
                <nav class="flex items-center gap-2">
                    <Link
                        v-for="link in registers.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                            link.active
                                ? 'bg-primary text-white'
                                : link.url
                                ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    ></Link>
                </nav>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const route = (window as any).route;

const props = defineProps<{
    registers: any;
    filters: {
        start_date: string | null;
        end_date: string | null;
    };
}>();

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const expandedRegisters = ref<string[]>([]);

const applyFilters = () => {
    router.get(route('cash-register.history'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleDetails = (registerId: string) => {
    const index = expandedRegisters.value.indexOf(registerId);
    if (index > -1) {
        expandedRegisters.value.splice(index, 1);
    } else {
        expandedRegisters.value.push(registerId);
    }
};

const formatCurrency = (value: number) => {
    const currency = (page.props.current_restaurant as any)?.currency || 'AED';
    return new Intl.NumberFormat('en-AE', { 
        style: 'currency', 
        currency: currency 
    }).format(value || 0);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
