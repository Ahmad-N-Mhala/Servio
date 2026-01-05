<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans overflow-hidden py-4 px-6 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $t('charts.order_status') }} {{ $t('common.manager') || 'Manager' }} <span class="text-sm font-normal text-gray-500 ml-2">({{ $t('orders.click_to_advance') || 'Click cards to advance status' }})</span></h1>
            <div class="flex items-center gap-4">
                <div class="text-2xl font-mono font-semibold text-gray-600 dark:text-gray-400" id="clock">{{ currentTime }}</div>
                <Link 
                    :href="route('orders.status-screen')"
                    class="ml-4 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-semibold text-sm flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    {{ $t('common.view') }} {{ $t('common.screen') || 'Screen' }}
                </Link>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="flex-1 grid grid-cols-2 gap-8 h-full">
            
            <!-- Preparing Column -->
            <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="bg-amber-50 dark:bg-amber-900/20 p-5 border-b border-amber-100 dark:border-amber-900/30">
                    <h2 class="text-3xl font-bold text-amber-600 dark:text-amber-500 text-center uppercase tracking-wide flex items-center justify-center gap-3">
                        <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $t('kitchen.preparing') || 'Preparing' }}
                    </h2>
                </div>
                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar bg-gray-50/50 dark:bg-transparent">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                         <div v-for="order in localOrders.preparing" :key="order.id" 
                            class="bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600/50 flex flex-col items-center justify-center animate-fade-in aspect-video shadow-sm cursor-pointer hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors relative group"
                            @click="updateStatus(order.id, 'mark_ready')"
                        >
                            <div v-if="processingId === order.id" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 flex items-center justify-center z-10">
                                <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            <span class="text-3xl font-bold text-gray-800 dark:text-white tracking-wide break-all text-center">#{{ order.transaction_number || order.order_number }}</span>
                            <span class="text-xs text-gray-500 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $t('orders.click_mark_ready') || 'Click to Mark Ready' }}</span>
                        </div>
                         <div v-if="localOrders.preparing.length === 0" class="col-span-full h-64 flex items-center justify-center text-gray-400 italic text-lg">
                            {{ $t('orders.no_orders_preparing') || 'No orders in preparation' }}
                        </div>
                    </div>
                </div>
            </div>

             <!-- Ready Column -->
             <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="bg-green-50 dark:bg-green-900/20 p-5 border-b border-green-100 dark:border-green-900/30">
                    <h2 class="text-3xl font-bold text-green-600 dark:text-green-500 text-center uppercase tracking-wide flex items-center justify-center gap-3">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $t('orders.ready_for_pickup') || 'Ready for Pickup' }}
                    </h2>
                </div>
                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar bg-gray-50/50 dark:bg-transparent">
                     <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="order in localOrders.ready" :key="order.id" 
                            class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-500/30 flex flex-col items-center justify-center shadow-sm animate-bounce-in aspect-video cursor-pointer hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors relative group"
                            @click="updateStatus(order.id, 'mark_completed')"
                        >
                            <div v-if="processingId === order.id" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 flex items-center justify-center z-10">
                                <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            <span class="text-4xl font-bold text-green-700 dark:text-green-400 tracking-wide break-all text-center">#{{ order.transaction_number || order.order_number }}</span>
                            <span class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $t('orders.click_mark_completed') || 'Click to Complete (Pickup)' }}</span>
                        </div>
                         <div v-if="localOrders.ready.length === 0" class="col-span-full h-64 flex items-center justify-center text-gray-400 italic text-lg">
                            {{ $t('orders.no_orders_ready') || 'No orders ready for pickup' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

useI18n();

const props = defineProps<{
    orders: {
        preparing: any[];
        ready: any[];
    }
}>();

const localOrders = ref(JSON.parse(JSON.stringify(props.orders)));
const currentTime = ref('');
const processingId = ref<string | null>(null);
const route = (window as any).route;

let pollInterval: any = null;
let clockInterval: any = null;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const pollOrders = async () => {
    if (processingId.value) return;
    try {
        const response = await axios.get(route('orders.status-screen.poll'));
        localOrders.value = response.data.orders;
    } catch (error) {
        console.error('Failed to poll orders', error);
    }
};

const updateStatus = async (orderId: string, action: 'mark_ready' | 'mark_completed') => {
    if (processingId.value) return;
    processingId.value = orderId;

    // Optimistic Update
    const originalOrders = JSON.parse(JSON.stringify(localOrders.value)); // Deep copy for revert
    
    if (action === 'mark_completed') {
        localOrders.value.ready = localOrders.value.ready.filter((o: any) => o.id !== orderId);
    } else if (action === 'mark_ready') {
        const order = localOrders.value.preparing.find((o: any) => o.id === orderId);
        if (order) {
            localOrders.value.preparing = localOrders.value.preparing.filter((o: any) => o.id !== orderId);
            localOrders.value.ready.unshift(order); // Add to top of ready list
        }
    }

    try {
        await axios.post(route('orders.status-screen.update-state'), {
            order_id: orderId,
            action: action
        });
        
        pollOrders();
        processingId.value = null;
    } catch (error) {
        // Revert changes on error
        localOrders.value = originalOrders;
        processingId.value = null;
        console.error('Failed to update status, reverting...', error);
    }
};

onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);
    pollInterval = setInterval(pollOrders, 5000);
});

onUnmounted(() => {
    clearInterval(clockInterval);
    clearInterval(pollInterval);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(31, 41, 55, 0.5); 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.8); 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(107, 114, 128, 1); 
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.05; }
    50% { opacity: 0.15; }
}
.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

/* Custom bounce-in for new ready items could be added here if we track diffs */
</style>
