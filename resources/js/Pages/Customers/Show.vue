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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.description') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('customers.points') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="log in transactions" :key="log.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ new Date(log.created_at).toLocaleDateString() }}
                                            <span class="text-xs text-gray-400 block">{{ new Date(log.created_at).toLocaleTimeString() }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ log.description }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-medium" :class="log.points > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ log.points > 0 ? '+' : '' }}{{ log.points }}
                                        </td>
                                    </tr>
                                    <tr v-if="transactions.length === 0">
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">{{ $t('customers.no_history') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order History -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">{{ $t('customers.order_history') }}</h2>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('customers.order_number') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.status') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $t('common.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-primary">
                                            {{ order.order_number }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ new Date(order.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize"
                                                :class="{
                                                    'bg-green-100 text-green-800': order.status === 'completed',
                                                    'bg-yellow-100 text-yellow-800': order.status === 'pending' || order.status === 'processing',
                                                    'bg-red-100 text-red-800': order.status === 'cancelled'
                                                }">
                                                {{ order.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                            {{ order.total }} {{ order.currency }}
                                        </td>
                                    </tr>
                                    <tr v-if="orders.data.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ $t('customers.no_orders') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    customer: any;
    orders: any;
    transactions: any[];
    redemptions: any[];
}>();

const route = (window as any).route;
</script>
