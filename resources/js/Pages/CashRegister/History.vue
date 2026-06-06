<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('cash_register.history_title') }}</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $t('cash_register.history_subtitle') }}</p>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('reports.start_date') }}</label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('reports.end_date') }}</label>
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
                            {{ $t('reports.apply') }}
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
                                <p class="text-sm text-gray-500">{{ $t('cash_register.cashier') }}: {{ register.user?.name || 'Unknown' }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $t('cash_register.opened') }}: {{ formatDateTime(register.opened_at) }}
                                    <span v-if="register.closed_at"> • {{ $t('cash_register.closed') }}: {{ formatDateTime(register.closed_at) }}</span>
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
                                    {{ $t('cash_register.export_csv') }}
                                </a>
                                <button
                                    @click="toggleDetails(register.id)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    {{ expandedRegisters.includes(register.id) ? $t('cash_register.hide_details') : $t('cash_register.show_details') }}
                                </button>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-4">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('cash_register.opening_balance') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.opening_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('common.expected') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.expected_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('cash_register.actual_balance') || 'Actual' }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(register.closing_balance) }}</p>
                            </div>
                            <div v-if="register.status === 'closed'" class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('common.difference') }}</p>
                                <p class="text-lg font-bold" :class="{
                                    'text-green-600': register.difference > 0,
                                    'text-red-600': register.difference < 0,
                                    'text-gray-600': register.difference === 0
                                }">
                                    {{ register.difference > 0 ? '+' : '' }}{{ formatCurrency(register.difference) }}
                                </p>
                            </div>
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('cash_register.transactions_count') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ register.transactions?.length || 0 }}</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="register.opening_notes || register.closing_notes" class="mt-4 space-y-2">
                            <div v-if="register.opening_notes" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                                <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mb-1">{{ $t('cash_register.opening_notes') }}:</p>
                                <p class="text-sm text-blue-900 dark:text-blue-100">{{ register.opening_notes }}</p>
                            </div>
                            <div v-if="register.closing_notes" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                                <p class="text-xs font-medium text-purple-700 dark:text-purple-300 mb-1">{{ $t('cash_register.closing_notes') }}:</p>
                                <p class="text-sm text-purple-900 dark:text-purple-100">{{ register.closing_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details (Expandable) -->
                    <div v-if="expandedRegisters.includes(register.id)" class="p-6">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">{{ $t('cash_register.transaction_history') }}</h4>
                        
                        <Table
                            :columns="columns"
                            :data="register.transactions || []"
                            :emptyMessage="$t('cash_register.no_transactions')"
                            :currency="currency"
                        >
                            <template #cell-created_at="{ row }">
                                <span class="font-mono text-xs text-gray-500">
                                    {{ formatDateTime(row.created_at) }}
                                </span>
                            </template>
                            
                            <template #cell-type="{ row }">
                                <span class="px-2 py-1 rounded-full text-xs font-bold capitalize" :class="{
                                    'bg-green-100 text-green-700': row.type === 'sale' || row.type === 'deposit',
                                    'bg-red-100 text-red-700': row.type === 'withdrawal',
                                    'bg-blue-100 text-blue-700': row.type === 'opening',
                                    'bg-gray-100 text-gray-700': row.type === 'closing'
                                }">
                                    {{ row.type }}
                                </span>
                            </template>
                            
                            <template #cell-amount="{ row }">
                                <span class="font-bold font-mono text-sm" :class="{
                                    'text-green-600': row.amount > 0,
                                    'text-red-600': row.amount < 0,
                                    'text-gray-600': row.amount === 0
                                }">
                                    {{ row.amount > 0 ? '+' : '' }}{{ formatCurrency(row.amount) }}
                                </span>
                            </template>
                            
                            <template #cell-balance_after="{ row }">
                                <span class="font-bold font-mono text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(row.balance_after) }}
                                </span>
                            </template>
                            
                            <template #cell-notes="{ row }">
                                <div v-if="row.order">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">Order #{{ row.order.order_number }}</span>
                                </div>
                                <div v-else class="text-gray-500 text-sm">
                                    {{ row.notes || '-' }}
                                </div>
                            </template>
                        </Table>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ $t('cash_register.no_records_title') }}</h3>
                <p class="text-gray-500">{{ $t('cash_register.no_records_desc') }}</p>
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
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Table from '@/Components/Table.vue';
import { formatDate, formatDateTime } from '@/Utils/dateHelper';

const page = usePage();
const route = (window as any).route;
const { t } = useI18n();

const props = defineProps<{
    registers: any;
    filters: {
        start_date: string | null;
        end_date: string | null;
    };
}>();

const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = computed(() => [
    { key: 'created_at', label: t('common.time'), sortable: true },
    { key: 'type', label: t('common.type'), sortable: true },
    { key: 'amount', label: t('common.amount'), sortable: true },
    { key: 'balance_after', label: t('cash_register.balance_after'), sortable: true },
    { key: 'notes', label: t('kitchen.notes'), sortable: true }
]);

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

// format helpers from dateHelper.ts are imported
</script>
