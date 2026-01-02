<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans overflow-hidden py-4 px-6 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Order Status</h1>
            <div class="flex items-center gap-4">
                <div class="text-2xl font-mono font-semibold text-gray-600 dark:text-gray-400" id="clock">{{ currentTime }}</div>
                <Link 
                    v-if="canManage"
                    :href="route('orders.status-screen.manage')"
                    class="ml-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold text-sm flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Manage
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
                        Preparing
                    </h2>
                </div>
                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar bg-gray-50/50 dark:bg-transparent">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                         <div v-for="order in localOrders.preparing" :key="order.id" 
                            class="bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600/50 flex flex-col items-center justify-center animate-fade-in aspect-video shadow-sm"
                        >
                            <span class="text-3xl font-bold text-gray-800 dark:text-white tracking-wide break-all text-center">#{{ order.transaction_number || order.order_number }}</span>
                        </div>
                         <div v-if="localOrders.preparing.length === 0" class="col-span-full h-64 flex items-center justify-center text-gray-400 italic text-lg">
                            No orders in preparation
                        </div>
                    </div>
                </div>
            </div>

             <!-- Ready Column -->
             <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="bg-green-50 dark:bg-green-900/20 p-5 border-b border-green-100 dark:border-green-900/30">
                    <h2 class="text-3xl font-bold text-green-600 dark:text-green-500 text-center uppercase tracking-wide flex items-center justify-center gap-3">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Ready for Pickup
                    </h2>
                </div>
                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar bg-gray-50/50 dark:bg-transparent">
                     <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="order in localOrders.ready" :key="order.id" 
                            class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-500/30 flex flex-col items-center justify-center shadow-sm animate-bounce-in aspect-video"
                        >
                            <span class="text-4xl font-bold text-green-700 dark:text-green-400 tracking-wide break-all text-center">#{{ order.transaction_number || order.order_number }}</span>
                        </div>
                         <div v-if="localOrders.ready.length === 0" class="col-span-full h-64 flex items-center justify-center text-gray-400 italic text-lg">
                            No orders ready for pickup
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    orders: {
        preparing: any[];
        ready: any[];
    };
    canManage?: boolean;
}>();

const localOrders = ref(props.orders);
const currentTime = ref('');
const route = (window as any).route;

let pollInterval: any = null;
let clockInterval: any = null;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const pollOrders = async () => {
    try {
        const response = await axios.get(route('orders.status-screen.poll'));
        localOrders.value = response.data.orders;
    } catch (error) {
        console.error('Failed to poll orders', error);
    }
};

onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);
    pollInterval = setInterval(pollOrders, 10000); // Poll every 10 seconds
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
