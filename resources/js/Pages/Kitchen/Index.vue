<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kitchen Display</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage active orders</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search orders..." 
                            class="pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-500">Auto-refresh active</span>
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Pending Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                            Pending
                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ pendingOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="pendingOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">No pending orders</p>
                    </div>

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="order in pendingOrders" :key="order.id" class="glass-card p-6 rounded-2xl border-l-4 border-yellow-400 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ order.order_number }}</h3>
                                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-medium text-gray-900">{{ order.customer_name || 'Guest' }}</span>
                                        <span class="text-xs text-gray-500 mb-1">{{ order.items.length }} Items</span>
                                        <div class="flex gap-1">
                                            <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider', 
                                                order.type === 'dine_in' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700']">
                                                {{ order.type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                                            </span>
                                            <span v-if="order.table" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                                {{ order.table.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                            {{ item.quantity }}x
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ item.menu_item?.name?.en || 'Unknown Item' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="order.notes" class="mb-4 p-3 bg-red-50 rounded-xl border border-red-100">
                                <p class="text-xs font-bold text-red-600 uppercase mb-1">Notes</p>
                                <p class="text-sm text-red-700">{{ order.notes }}</p>
                            </div>

                            <button 
                                @click="updateStatus(order, 'processing')"
                                :disabled="processingId === order.id"
                                class="w-full py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-hover transition-colors flex items-center justify-center gap-2"
                            >
                                <svg v-if="processingId === order.id" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Start Cooking</span>
                            </button>
                        </div>
                    </transition-group>
                </div>

                <!-- Processing Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                            Processing
                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ processingOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="processingOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">No orders in progress</p>
                    </div>

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="order in processingOrders" :key="order.id" class="glass-card p-6 rounded-2xl border-l-4 border-blue-500 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ order.order_number }}</h3>
                                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-medium text-gray-900">{{ order.customer_name || 'Guest' }}</span>
                                        <span class="text-xs text-gray-500 mb-1">{{ order.items.length }} Items</span>
                                        <div class="flex gap-1">
                                            <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider', 
                                                order.type === 'dine_in' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700']">
                                                {{ order.type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                                            </span>
                                            <span v-if="order.table" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                                {{ order.table.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                            {{ item.quantity }}x
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ item.menu_item?.name?.en || 'Unknown Item' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="order.notes" class="mb-4 p-3 bg-red-50 rounded-xl border border-red-100">
                                <p class="text-xs font-bold text-red-600 uppercase mb-1">Notes</p>
                                <p class="text-sm text-red-700">{{ order.notes }}</p>
                            </div>

                            <button 
                                @click="updateStatus(order, 'served')"
                                :disabled="processingId === order.id"
                                class="w-full py-3 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg v-if="processingId === order.id" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>Serve Order</span>
                            </button>
                        </div>
                    </transition-group>
                </div>

                <!-- Served Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            Served
                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ servedOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="servedOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">No served orders</p>
                    </div>

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="order in servedOrders" :key="order.id" class="glass-card p-6 rounded-2xl border-l-4 border-green-500 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ order.order_number }}</h3>
                                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700">
                                        Served
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                            {{ item.quantity }}x
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ item.menu_item?.name?.en || 'Unknown Item' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition-group>
                </div>
            </div>

            <!-- Recently Completed -->
            <div class="mt-12">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    Recently Completed
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="order in completedOrders" :key="order.id" class="glass-card p-4 rounded-xl opacity-75 hover:opacity-100 transition-opacity">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-900">#{{ order.order_number }}</span>
                            <span class="text-xs text-gray-500">{{ new Date(order.completed_at).toLocaleTimeString() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ order.items.map((i: any) => i.quantity + 'x ' + (i.menu_item?.name?.en || 'Item')).join(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const route = (window as any).route;

const props = defineProps<{
    orders: any[];
    completedOrders: any[];
}>();

const processingId = ref<number | null>(null);
const searchQuery = ref('');

const filterOrders = (orders: any[]) => {
    if (!searchQuery.value) return orders;
    const query = searchQuery.value.toLowerCase();
    return orders.filter(o => 
        o.order_number.toLowerCase().includes(query) ||
        (o.customer_name && o.customer_name.toLowerCase().includes(query)) ||
        o.items.some((i: any) => i.menu_item?.name?.en?.toLowerCase()?.includes(query))
    );
};

const pendingOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'pending')));
const processingOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'processing')));
const servedOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'served')));

const updateStatus = (order: any, status: string) => {
    processingId.value = order.id;

    router.put(route('kitchen.status.update', order.id), {
        status: status,
    }, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null;
        }
    });
};

// Auto-refresh every 30 seconds
let refreshInterval: any;

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['orders', 'completedOrders'] });
    }, 30000);
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
  transform: translateX(30px);
}

.list-leave-active {
  position: absolute;
}
</style>
