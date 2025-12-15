<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Point of Sale</h1>
                    <p class="mt-1 text-sm text-gray-500">Settle bills and clear tables</p>
                </div>
            </div>

            <div class="flex gap-8 h-[calc(100vh-12rem)]">
                <!-- Left Column: Unpaid Orders -->
                <div class="w-1/2 flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar">
                    <h2 class="text-lg font-bold text-gray-900 sticky top-0 bg-gray-50 pb-2 z-10 flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            Active Orders
                            <span class="bg-primary text-white text-xs px-2 py-1 rounded-full">{{ filteredOrders.length }}</span>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search active orders..."
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                        >
                    </h2>
                    
                    <div v-if="filteredOrders.length === 0" class="glass-card p-8 text-center rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">No unpaid orders found</p>
                    </div>

                    <div 
                        v-for="order in filteredOrders" 
                        :key="order.id" 
                        @click="selectOrder(order)"
                        :class="['cursor-pointer p-6 rounded-2xl border transition-all duration-200',
                            selectedOrder?.id === order.id 
                                ? 'bg-primary text-white border-primary shadow-lg transform scale-[1.02]' 
                                : 'bg-white border-gray-100 hover:border-primary/50 hover:shadow-md'
                        ]"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg" :class="{ 'text-white': selectedOrder?.id === order.id }">
                                #{{ order.order_number }}
                            </h3>
                            <span class="text-sm font-medium" :class="selectedOrder?.id === order.id ? 'text-white/90' : 'text-gray-500'">
                                {{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-end">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm" :class="selectedOrder?.id === order.id ? 'text-white/90' : 'text-gray-600'">
                                        {{ order.table ? order.table.name : 'Takeaway' }}
                                    </span>
                                    <span v-if="order.type === 'dine_in'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/20">
                                        Dine In
                                    </span>
                                </div>
                                <span class="text-xs" :class="selectedOrder?.id === order.id ? 'text-white/80' : 'text-gray-400'">
                                    {{ order.items.length }} Items • {{ order.customer_name || 'Guest' }}
                                </span>
                            </div>
                            <span class="font-bold text-xl" :class="{ 'text-white': selectedOrder?.id === order.id }">
                                {{ order.currency }} {{ order.total }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Bill Details & Payment -->
                <div class="w-1/2 flex flex-col">
                    <div v-if="selectedOrder" class="glass-card flex flex-col h-full rounded-2xl overflow-hidden border border-gray-200 shadow-xl">
                        <!-- Bill Header -->
                        <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-xl text-gray-900">Bill Details</h3>
                                <p class="text-sm text-gray-500">Order #{{ selectedOrder.order_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ selectedOrder.table ? 'Table: ' + selectedOrder.table.name : 'Takeaway' }}</p>
                                <p class="text-xs text-gray-500">{{ selectedOrder.customer_name || 'Guest' }}</p>
                            </div>
                        </div>

                        <!-- Bill Items -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-white">
                            <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center gap-3">
                                    <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600">
                                        {{ item.quantity }}x
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ item.menu_item?.name?.en || 'Item' }}</p>
                                        <p v-if="item.notes" class="text-xs text-gray-500 italic">{{ item.notes }}</p>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">{{ selectedOrder.currency }} {{ item.total_price }}</span>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="p-6 bg-gray-50 border-t border-gray-200 space-y-4">
                            <!-- Totals -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-gray-600 text-sm">
                                    <span>Subtotal</span>
                                    <span>{{ selectedOrder.currency }} {{ selectedOrder.subtotal }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600 text-sm">
                                    <span>Tax</span>
                                    <span>{{ selectedOrder.currency }} {{ selectedOrder.tax }}</span>
                                </div>
                                <div class="flex justify-between text-gray-900 font-bold text-xl pt-2 border-t border-gray-200">
                                    <span>Total Amount</span>
                                    <span>{{ selectedOrder.currency }} {{ selectedOrder.total }}</span>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button 
                                        @click="paymentMethod = 'cash'"
                                        :class="['py-3 px-4 rounded-xl flex flex-col items-center gap-1 border transition-all',
                                            paymentMethod === 'cash' 
                                                ? 'bg-green-50 border-green-500 text-green-700 ring-1 ring-green-500' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'
                                        ]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-xs font-bold">CASH</span>
                                    </button>
                                    <button 
                                        @click="paymentMethod = 'card'"
                                        :class="['py-3 px-4 rounded-xl flex flex-col items-center gap-1 border transition-all',
                                            paymentMethod === 'card' 
                                                ? 'bg-blue-50 border-blue-500 text-blue-700 ring-1 ring-blue-500' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'
                                        ]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <span class="text-xs font-bold">CARD</span>
                                    </button>
                                    <button 
                                        @click="paymentMethod = 'online'"
                                        :class="['py-3 px-4 rounded-xl flex flex-col items-center gap-1 border transition-all',
                                            paymentMethod === 'online' 
                                                ? 'bg-purple-50 border-purple-500 text-purple-700 ring-1 ring-purple-500' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'
                                        ]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        <span class="text-xs font-bold">ONLINE</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button 
                                @click="settleBill" 
                                :disabled="processing || !paymentMethod"
                                :class="['w-full py-4 rounded-xl font-bold text-lg text-white flex items-center justify-center gap-2 transition-all shadow-lg',
                                    processing || !paymentMethod 
                                        ? 'bg-gray-400 cursor-not-allowed' 
                                        : 'bg-gradient-to-r from-primary to-primary-hover hover:shadow-xl transform hover:-translate-y-0.5'
                                ]"
                            >
                                <svg v-if="processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>
                                    {{ paymentMethod ? `Settle ${selectedOrder.currency} ${selectedOrder.total}` : 'Select Payment Method' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State for Right Column -->
                    <div v-else class="h-full glass-card rounded-2xl flex flex-col items-center justify-center text-gray-400 p-8 border border-gray-100">
                        <div class="h-24 w-24 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 36v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-xl font-medium text-gray-900">No Order Selected</p>
                        <p class="mt-2 text-center text-sm">Select an active order from the list<br>to view bill details and process payment.</p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps<{
    orders: any[];
    tables: any[];
}>();

const searchQuery = ref('');
const selectedOrder = ref<any>(null);
const paymentMethod = ref<string>('cash');
const processing = ref(false);

const filteredOrders = computed(() => {
    if (!searchQuery.value) return props.orders;
    const query = searchQuery.value.toLowerCase();
    return props.orders.filter(order => 
        order.order_number.toLowerCase().includes(query) ||
        (order.customer_name && order.customer_name.toLowerCase().includes(query)) ||
        (order.table && order.table.name.toLowerCase().includes(query))
    );
});

const selectOrder = (order: any) => {
    selectedOrder.value = order;
    paymentMethod.value = 'cash'; // Reset to default
};

const settleBill = () => {
    if (!selectedOrder.value || !paymentMethod.value) return;

    processing.value = true;
    router.post(`/pos/${selectedOrder.value.id}/settle`, {
        payment_method: paymentMethod.value,
    }, {
        onSuccess: () => {
            selectedOrder.value = null; // Clear selection
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 20px;
}
</style>
