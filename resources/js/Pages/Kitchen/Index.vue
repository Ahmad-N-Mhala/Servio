<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('kitchen.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('kitchen.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            :placeholder="$t('kitchen.search_orders')" 
                            class="pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-500">{{ $t('kitchen.auto_refresh') }}</span>
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Pending Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-yellow-400"></span>{{ $t('kitchen.pending') }}<span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ pendingOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="pendingOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">{{ $t('kitchen.no_pending') }}</p>
                    </div>

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="order in pendingOrders" :key="order.id" class="glass-card p-6 rounded-2xl border-l-4 border-yellow-400 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ order.order_number }}</h3>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</p>
                                        <span 
                                            :class="[
                                                'text-xs font-bold px-2 py-0.5 rounded-full',
                                                getOrderAge(order.created_at) > 30 ? 'bg-red-100 text-red-700 animate-pulse' :
                                                getOrderAge(order.created_at) > 15 ? 'bg-orange-100 text-orange-700' :
                                                'bg-green-100 text-green-700'
                                            ]"
                                        >
                                            {{ getOrderAge(order.created_at) }} {{ $t('common.minutes') || 'min' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-medium text-gray-900">{{ order.customer_name || $t('common.guest') }}</span>
                                        <span class="text-xs text-gray-500 mb-1">{{ order.items.length }} {{ $t('common.items') }}</span>
                                        <div class="flex gap-1">
                                            <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider', 
                                                order.type === 'dine_in' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700']">
                                                {{ order.type === 'dine_in' ? $t('kitchen.dine_in') : $t('kitchen.takeaway') }}
                                            </span>
                                            <div v-if="order.table" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700">{{ order.table.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div v-for="item in order.items" :key="item.id" class="py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ item.quantity }}x
                                            </span>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-gray-900">{{ getLocalizedName(item.menu_item?.name, 'en') || 'Item' }}</span>
                                                <span class="text-xs text-gray-500 font-arabic" v-if="getLocalizedName(item.menu_item?.name, 'ar')">{{ getLocalizedName(item.menu_item?.name, 'ar') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="item.notes" class="mt-1 ml-9 text-xs text-amber-700 italic bg-amber-50 px-2 py-1 rounded">
                                        📝 {{ item.notes }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="order.notes" class="mb-4 p-3 bg-red-50 rounded-xl border border-red-100">
                                <p class="text-xs font-bold text-red-600 uppercase mb-1">{{ $t('kitchen.notes') }}</p>
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
                                <span v-else>{{ $t('kitchen.start_cooking') }}</span>
                            </button>
                            <button 
                                @click="promptCancel(order)"
                                :disabled="processingId === order.id"
                                class="w-full py-3 bg-red-50 text-red-600 rounded-xl font-bold hover:bg-red-100 transition-colors flex items-center justify-center gap-2 mt-2"
                            >
                                {{ $t('orders.cancel_order') }}
                            </button>
                        </div>
                    </transition-group>
                </div>

                <!-- Processing Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-blue-500"></span>{{ $t('kitchen.processing') }}<span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ processingOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="processingOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">{{ $t('kitchen.no_processing') }}</p>
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
                                        <span class="text-sm font-medium text-gray-900">{{ order.customer_name || $t('common.guest') }}</span>
                                        <span class="text-xs text-gray-500 mb-1">{{ order.items.length }} {{ $t('common.items') }}</span>
                                        <div class="flex gap-1">
                                            <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider', 
                                                order.type === 'dine_in' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700']">
                                                {{ order.type === 'dine_in' ? $t('kitchen.dine_in') : $t('kitchen.takeaway') }}
                                            </span>
                                            <div v-if="order.table" class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700">{{ order.table.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div v-for="item in order.items" :key="item.id" class="py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ item.quantity }}x
                                            </span>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-gray-900">{{ getLocalizedName(item.menu_item?.name, 'en') || 'Item' }}</span>
                                                <span class="text-xs text-gray-500 font-arabic" v-if="getLocalizedName(item.menu_item?.name, 'ar')">{{ getLocalizedName(item.menu_item?.name, 'ar') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="item.notes" class="mt-1 ml-9 text-xs text-amber-700 italic bg-amber-50 px-2 py-1 rounded">
                                        📝 {{ item.notes }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="order.notes" class="mb-4 p-3 bg-red-50 rounded-xl border border-red-100">
                                <p class="text-xs font-bold text-red-600 uppercase mb-1">{{ $t('kitchen.notes') }}</p>
                                <p class="text-sm text-red-700">{{ order.notes }}</p>
                            </div>

                            <button 
                                @click="updateStatus(order, 'ready')"
                                :disabled="processingId === order.id"
                                class="w-full py-3 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg v-if="processingId === order.id" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>{{ $t('kitchen.order_ready') }}</span>
                            </button>
                        </div>
                    </transition-group>
                </div>

                <!-- Ready / Served Orders -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            Ready / Served
                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ servedOrders.length }}
                            </span>
                        </h2>
                    </div>

                    <div v-if="servedOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">{{ $t('kitchen.no_completed') }}</p>
                    </div>

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="order in servedOrders" :key="order.id" class="glass-card p-6 rounded-2xl border-l-4 border-green-500 hover:shadow-lg transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ order.order_number }}</h3>
                                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</p>
                                </div>
                                <div class="text-right">
                                    <span v-if="order.status === 'ready'" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-yellow-100 text-yellow-700 animate-pulse">
                                        {{ $t('kitchen.ready_for_pickup') }}
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700">
                                        {{ $t('kitchen.served') }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div v-for="item in order.items" :key="item.id" class="py-2 border-b border-gray-50 last:border-0">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ item.quantity }}x
                                            </span>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-gray-900">{{ getLocalizedName(item.menu_item?.name, 'en') || 'Item' }}</span>
                                                <span class="text-xs text-gray-500 font-arabic" v-if="getLocalizedName(item.menu_item?.name, 'ar')">{{ getLocalizedName(item.menu_item?.name, 'ar') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="item.notes" class="mt-1 ml-9 text-xs text-amber-700 italic bg-amber-50 px-2 py-1 rounded">
                                        📝 {{ item.notes }}
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
                    {{ $t('kitchen.recently_completed') || 'Recently Completed' }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="order in completedOrders" :key="order.id" class="glass-card p-4 rounded-xl opacity-75 hover:opacity-100 transition-opacity">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-900">#{{ order.order_number }}</span>
                            <span class="text-xs text-gray-500">{{ new Date(order.completed_at).toLocaleTimeString() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ order.items.map((i: any) => i.quantity + 'x ' + (getLocaleName(i.menu_item?.name) || $t('common.item'))).join(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <Modal :show="!!cancellingOrder" @close="closeCancelModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $t('kitchen.cancel_title') }} #{{ cancellingOrder?.order_number }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $t('kitchen.cancel_reason_prompt') }}</p>
                
                <form @submit.prevent="submitCancel">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('kitchen.reason_for_cancellation') }}</label>
                        <textarea 
                            v-model="cancelForm.cancellation_reason"
                            rows="3"
                            required
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            :placeholder="$t('kitchen.reason_placeholder')"
                        ></textarea>
                        <p v-if="cancelForm.errors.cancellation_reason" class="text-sm text-red-600 mt-1">{{ cancelForm.errors.cancellation_reason }}</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="secondary" @click="closeCancelModal">{{ $t('kitchen.keep_order') }}</Button>
                        <Button type="submit" variant="danger" :loading="cancelForm.processing">{{ $t('kitchen.confirm_cancellation') }}</Button>
                    </div>
                </form>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';

const { locale } = useI18n();

const route = (window as any).route;

const props = defineProps<{
    orders: any[];
    completedOrders: any[];
    restaurant_id: string;
}>();

const processingId = ref<number | null>(null);
const searchQuery = ref('');

const getLocaleName = (name: any) => {
    if (typeof name === 'object' && name !== null) {
        return name[locale.value] || Object.values(name)[0] || '';
    }
    return name;
};

const getLocalizedName = (name: any, lang: 'en' | 'ar') => {
    if (!name) return '';
    if (typeof name === 'string') return lang === 'en' ? name : '';
    return name[lang] || '';
};

const filterOrders = (orders: any[]) => {
    if (!searchQuery.value) return orders;
    const query = searchQuery.value.toLowerCase();
    return orders.filter(o => 
        o.order_number.toLowerCase().includes(query) ||
        (o.customer_name && o.customer_name.toLowerCase().includes(query)) ||
        o.items.some((i: any) => getLocaleName(i.menu_item?.name)?.toLowerCase()?.includes(query))
    );
};

const pendingOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'pending')));
const processingOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'processing')));
const servedOrders = computed(() => filterOrders(props.orders.filter(o => o.status === 'served' || o.status === 'ready')));

const cancellingOrder = ref<any>(null);
const cancelForm = useForm({
    status: 'cancelled',
    cancellation_reason: ''
});

const promptCancel = (order: any) => {
    cancellingOrder.value = order;
    cancelForm.cancellation_reason = '';
};

const closeCancelModal = () => {
    cancellingOrder.value = null;
    cancelForm.reset();
};

// Calculate order age in minutes
const getOrderAge = (createdAt: string): number => {
    const now = new Date().getTime();
    const orderTime = new Date(createdAt).getTime();
    return Math.floor((now - orderTime) / 1000 / 60);
};

const submitCancel = () => {
    if (!cancellingOrder.value) return;

    cancelForm.put(route('kitchen.status.update', cancellingOrder.value.id), {
        onSuccess: () => closeCancelModal()
    });
};

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

// Real-time updates with Laravel Echo + Fallback polling
let refreshInterval: any;
let echoChannel: any;

onMounted(() => {
    if (window.Echo && props.restaurant_id) {
        // Subscribe to restaurant-specific order channel
        echoChannel = window.Echo.channel(`restaurant.${props.restaurant_id}.orders`);
        
        // Listen for order created events
        echoChannel.listen('.order.created', (e: any) => {
            console.log('Order created:', e);
            router.reload({ only: ['orders', 'completedOrders'] });
        });
        
        // Listen for order status changed events
        echoChannel.listen('.order.status_changed', (e: any) => {
            console.log('Order status changed:', e);
            router.reload({ only: ['orders', 'completedOrders'] });
        });
        
        // Listen for order updated events
        echoChannel.listen('.order.updated', (e: any) => {
            console.log('Order updated:', e);
            router.reload({ only: ['orders', 'completedOrders'] });
        });
        
        console.log('✅ Real-time updates enabled via Laravel Echo');
    } else {
        console.warn('⚠️ Laravel Echo not available, using fallback polling');
    }
    
    // Fallback: Polling every 2 seconds as backup
    refreshInterval = setInterval(() => {
        if (!cancellingOrder.value) { // Don't refresh if modal is open
             // Include 'flash' to ensure we clear any stale success messages from the session
             router.reload({ only: ['orders', 'completedOrders', 'flash'] });
        }
    }, 2000); // 2 seconds for real-time sync with POS
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    if (echoChannel) {
        echoChannel.stopListening('.order.created');
        echoChannel.stopListening('.order.status_changed');
        echoChannel.stopListening('.order.updated');
        window.Echo.leave(`restaurant.${props.restaurant_id}.orders`);
    }
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
