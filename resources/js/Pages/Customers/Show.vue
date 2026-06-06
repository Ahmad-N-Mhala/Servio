<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('customers.index')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ customer.name || 'Unknown Customer' }}</h1>
                    <p class="text-sm text-gray-500">{{ $t('customers.details_and_logs') }}</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">{{ $t('customers.loyalty_points_label') }}</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ customer.loyalty_points?.balance || 0 }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">{{ $t('customers.total_spent') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ customer.total_spent }} {{ currency }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">{{ $t('customers.total_orders') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ customer.total_orders }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">{{ $t('customers.current_tier') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 capitalize">{{ customer.loyalty_tier || 'Bronze' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Point Transactions Log -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">{{ $t('customers.points_log') }}</h2>
                    </div>
                    <Table
                        :columns="logColumns"
                        :data="transactions || []"
                        :emptyMessage="$t('customers.no_history')"
                    >
                        <template #cell-created_at="{ row }">
                            <span class="text-sm text-gray-500 whitespace-nowrap font-mono">
                                {{ new Date(row.created_at).toLocaleDateString() }}
                                <span class="text-xs text-gray-400 block">{{ new Date(row.created_at).toLocaleTimeString() }}</span>
                            </span>
                        </template>
                        <template #cell-description="{ row }">
                            <span class="text-sm text-gray-900">
                                {{ row.description }}
                            </span>
                        </template>
                        <template #cell-points="{ row }">
                            <span class="text-sm font-bold font-mono" :class="row.points > 0 ? 'text-green-600' : 'text-red-600'">
                                {{ row.points > 0 ? '+' : '' }}{{ row.points }}
                            </span>
                        </template>
                    </Table>
                </div>

                <!-- Order History -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">{{ $t('customers.order_history') }}</h2>
                    </div>
                    <Table
                        :columns="orderColumns"
                        :data="orders.data || []"
                        :pagination="orders"
                        :emptyMessage="$t('customers.no_orders')"
                    >
                        <template #cell-order_number="{ row }">
                            <span class="text-sm font-bold text-primary font-mono">
                                {{ row.order_number }}
                            </span>
                        </template>
                        <template #cell-created_at="{ row }">
                            <span class="text-sm text-gray-500 whitespace-nowrap font-mono">
                                {{ new Date(row.created_at).toLocaleDateString() }}
                            </span>
                        </template>
                        <template #cell-status="{ row }">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize block w-fit"
                                :class="{
                                    'bg-green-100 text-green-800': row.status === 'completed',
                                    'bg-yellow-100 text-yellow-800': row.status === 'pending' || row.status === 'processing',
                                    'bg-red-100 text-red-800': row.status === 'cancelled' || row.status === 'deleted',
                                    'bg-gray-100 text-gray-800': !['completed', 'pending', 'processing', 'cancelled', 'deleted'].includes(row.status)
                                }">
                                {{ row.status }}
                                <span v-if="['cancelled', 'deleted'].includes(row.status)" class="block text-[10px] opacity-80 font-normal">
                                    {{ new Date(row.updated_at).toLocaleDateString() }}
                                </span>
                            </span>
                        </template>
                        <template #cell-total="{ row }">
                            <span class="text-sm font-bold font-mono text-gray-900">
                                {{ row.total }} {{ row.currency }}
                            </span>
                        </template>
                    </Table>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Table from '@/Components/Table.vue';
import { computed } from 'vue';

const page = usePage();
const { t } = useI18n();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    customer: any;
    orders: any;
    transactions: any[];
    redemptions: any[];
}>();

const logColumns = computed(() => [
    { key: 'created_at', label: t('common.date'), sortable: true },
    { key: 'description', label: t('common.description'), sortable: true },
    { key: 'points', label: t('customers.points'), sortable: true, align: 'right' as const }
]);

const orderColumns = computed(() => [
    { key: 'order_number', label: t('customers.order_number'), sortable: true },
    { key: 'created_at', label: t('common.date'), sortable: true },
    { key: 'status', label: t('common.status'), sortable: true },
    { key: 'total', label: t('common.total'), sortable: true, align: 'right' as const }
]);

const route = (window as any).route;
</script>
