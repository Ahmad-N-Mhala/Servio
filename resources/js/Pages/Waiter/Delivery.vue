<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('nav.order_delivery') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('delivery.manage_ready_orders') || 'Manage orders ready to be served to tables' }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500 bg-white dark:bg-gray-800 px-3 py-1 rounded-full shadow-sm border border-gray-100 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        {{ $t('common.live_updates') || 'Live Updates' }}
                    </span>
                </div>
            </div>

            <!-- Ready Orders Grid -->
            <div v-if="orders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <transition-group name="list">
                    <div v-for="order in orders" :key="order.id" class="glass-card bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border-l-4 border-green-500 shadow-md hover:shadow-xl transition-all duration-300">
                        <!-- Card Header: Table & Time -->
                        <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white">
                                    {{ order.table?.name || (order.type === 'dine_in' ? ($t('pos.dine_in') + ' (' + $t('common.no_table') + ')') : (order.type ? $t('pos.' + order.type) : $t('common.no_table'))) }}
                                </h3>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-1">
                                    #{{ order.order_number }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold">{{ $t('kitchen.ready') || 'READY' }}</span>
                                <p class="text-xs text-gray-500 mt-1">{{ formatTime(order.updated_at) }}</p>
                            </div>
                        </div>

                        <!-- Card Body: Items -->
                        <div class="p-5">
                            <ul class="space-y-3 mb-6">
                                <li v-for="item in order.items" :key="item.id" class="flex items-start gap-3 text-sm">
                                    <span class="font-bold text-gray-900 dark:text-white min-w-[1.5rem]">{{ item.quantity }}x</span>
                                    <span class="text-gray-600 dark:text-gray-300 leading-tight">{{ item.menu_item?.name?.en || item.menu_item?.name || $t('common.unknown_item') }}</span>
                                </li>
                            </ul>

                            <button 
                                @click="markAsServed(order)"
                                :disabled="processingId === order.id"
                                class="w-full py-3 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-xl font-bold shadow-lg shadow-green-200 dark:shadow-none transition-all flex items-center justify-center gap-2 transform active:scale-95"
                            >
                                <svg v-if="processingId === order.id" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $t('delivery.mark_as_served') || 'Mark as Served' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </transition-group>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center h-96 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $t('delivery.all_caught_up') || 'All Caught Up!' }}</h3>
                <p class="text-gray-500 max-w-sm">{{ $t('delivery.no_ready_orders') || 'There are no orders ready to be served at the moment. Good job!' }}</p>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps<{
    orders: any[];
}>();

const processingId = ref<number | null>(null);
const route = (window as any).route;

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const markAsServed = (order: any) => {
    processingId.value = order.id;
    router.post(route('service.serve', order.id), {}, {
        onFinish: () => {
            processingId.value = null;
        },
        preserveScroll: true
    });
};

// Auto-refresh logic (UI only)
let refreshInterval: any;

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['orders'] });
    }, 2000); // Check every 2 seconds for UI updates
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.list-leave-active {
  position: absolute;
}
</style>
