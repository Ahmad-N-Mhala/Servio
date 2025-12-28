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
                            <span class="font-bold text-xl" :class="{ 'text-white': selectedOrder?.id === order.id }">
                                {{ order.currency || currentCurrency }} {{ order.total }}
                            </span>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Right Column: Bill Details & Payment -->
                <div class="w-1/2 flex flex-col">
                    <div v-if="selectedOrder" class="glass-card flex flex-col h-full rounded-2xl overflow-hidden border border-gray-200 shadow-xl">
                        <!-- Bill Header -->
                        <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center shrink-0">
                            <div>
                                <h3 class="font-bold text-xl text-gray-900">Bill Details</h3>
                                <p class="text-sm text-gray-500">Order #{{ selectedOrder.order_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ selectedOrder.table ? 'Table: ' + selectedOrder.table.name : 'Takeaway' }}</p>
                                <p class="text-xs text-gray-500">{{ selectedOrder.customer_name || 'Guest' }}</p>
                            </div>
                        </div>

                        <!-- Bill Items (Editable) -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-white">
                            <div v-for="(item, index) in editableItems" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0 group">
                                <div class="flex items-center gap-3 flex-1">
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200">
                                        <button @click="updateQuantity(index, -1)" class="px-2 py-1 text-gray-600 hover:text-red-500 hover:bg-red-50 rounded-l-lg transition-colors font-bold">-</button>
                                        <span class="w-6 text-center text-sm font-bold text-gray-900">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(index, 1)" class="px-2 py-1 text-gray-600 hover:text-green-500 hover:bg-green-50 rounded-r-lg transition-colors font-bold">+</button>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ item.menu_item?.name?.en || 'Item' }}</p>
                                        <p v-if="item.notes" class="text-xs text-gray-500 italic">{{ item.notes }}</p>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900 min-w-[4rem] text-right">
                                    {{ selectedOrder.currency || currentCurrency }} {{ (item.quantity * item.unit_price).toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="editableItems.length === 0" class="text-center text-gray-400 text-sm py-4">
                                No items in order.
                            </div>
                        </div>

                        <!-- Bottom Section: Adjustments, Totals, Payment -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 space-y-4 shrink-0">
                            
                            <!-- Adjustments Toggle -->
                            <div v-if="adjustmentMode === 'none'" class="flex gap-2">
                                <button @click="adjustmentMode = 'discount'" class="flex-1 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                    + Add Discount
                                </button>
                                <button @click="adjustmentMode = 'extra'" class="flex-1 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                    + Add Extra Charge
                                </button>
                            </div>

                            <!-- Active Adjustment Inputs -->
                            <div v-else class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm animate-fade-in-up">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ adjustmentMode === 'discount' ? 'Apply Discount' : 'Add Extra Charge' }}
                                    </h4>
                                    <button @click="adjustmentMode = 'none'" class="text-gray-400 hover:text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="flex gap-2 mb-2">
                                    <!-- Type Toggle -->
                                    <div class="flex rounded-lg border border-gray-200 overflow-hidden w-1/3">
                                        <button 
                                            @click="activeType = 'fixed'"
                                            :class="['flex-1 text-xs font-bold transition-colors', activeType === 'fixed' ? 'bg-gray-100 text-gray-900' : 'bg-white text-gray-500 hover:bg-gray-50']"
                                        >Fixed</button>
                                        <div class="w-px bg-gray-200"></div>
                                        <button 
                                            @click="activeType = 'percent'"
                                            :class="['flex-1 text-xs font-bold transition-colors', activeType === 'percent' ? 'bg-gray-100 text-gray-900' : 'bg-white text-gray-500 hover:bg-gray-50']"
                                        >%</button>
                                    </div>
                                    <!-- Value Input -->
                                    <div class="relative flex-1">
                                        <input 
                                            v-model.number="activeValue" 
                                            type="number" 
                                            min="0" 
                                            step="0.01"
                                            class="w-full pl-3 pr-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                            placeholder="0.00"
                                        >
                                    </div>
                                </div>
                                <button @click="applyAdjustment" class="w-full py-1.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-lg transition-colors">
                                    Apply {{ adjustmentMode === 'discount' ? 'Discount' : 'Charge' }}
                                </button>
                            </div>

                            <!-- Totals Display (Live Preview) -->
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Subtotal</span>
                                    <span class="text-xs font-medium text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.subtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Tax</span>
                                    <span class="text-xs font-medium text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.tax.toFixed(2) }}</span>
                                </div>
                                <div v-if="totals.discountAmount > 0" class="flex justify-between items-center text-red-600">
                                    <span class="text-xs">
                                        Discount 
                                        <span v-if="discountType === 'percent'" class="ml-1 text-[10px] bg-red-50 px-1 rounded">({{ discountValue }}%)</span>
                                        <button v-if="adjustmentMode === 'none'" @click="adjustmentMode = 'discount'" class="ml-2 text-gray-400 hover:text-gray-600 hover:underline text-[10px]">Edit</button>
                                    </span>
                                    <span class="text-xs font-bold">- {{ selectedOrder.currency || currentCurrency }} {{ totals.discountAmount.toFixed(2) }}</span>
                                </div>
                                <div v-if="totals.additionalChargeAmount > 0" class="flex justify-between items-center text-blue-600">
                                    <span class="text-xs">
                                        Extra Charge
                                        <span v-if="additionalChargeType === 'percent'" class="ml-1 text-[10px] bg-blue-50 px-1 rounded">({{ additionalChargeValue }}%)</span>
                                         <button v-if="adjustmentMode === 'none'" @click="adjustmentMode = 'extra'" class="ml-2 text-gray-400 hover:text-gray-600 hover:underline text-[10px]">Edit</button>
                                    </span>
                                    <span class="text-xs font-bold">+ {{ selectedOrder.currency || currentCurrency }} {{ totals.additionalChargeAmount.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 mt-2">
                                    <span class="text-base font-bold text-gray-900">Total Amount</span>
                                    <span class="text-xl font-bold text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.total.toFixed(2) }}</span>
                                </div>
                            </div>
                            
                            <!-- Sync Button if items changed -->
                            <div v-if="hasUnsavedChanges" class="animate-pulse">
                                <button @click="updateOrder" :disabled="processing" class="w-full py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold rounded-lg shadow-sm">
                                    Changes Detected - Update Bill
                                </button>
                            </div>

                            <!-- Payment Section -->
                            <div class="pt-2">
                                <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wide">Payment Method</label>
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <button 
                                        v-for="method in ['cash', 'card', 'online']" 
                                        :key="method"
                                        @click="paymentMethod = method"
                                        :class="['py-2 px-2 rounded-lg flex flex-col items-center gap-0.5 border transition-all',
                                            paymentMethod === method 
                                                ? 'bg-primary/5 border-primary text-primary ring-1 ring-primary' 
                                                : 'bg-white border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'
                                        ]"
                                    >
                                        <span class="text-[10px] font-bold uppercase">{{ method }}</span>
                                    </button>
                                </div>
                                <button 
                                    @click="settleBill" 
                                    :disabled="processing || !paymentMethod || hasUnsavedChanges"
                                    :class="['w-full py-3 rounded-xl font-bold text-base text-white flex items-center justify-center gap-2 transition-all shadow-lg',
                                        processing || !paymentMethod || hasUnsavedChanges
                                            ? 'bg-gray-300 cursor-not-allowed text-gray-500' 
                                            : 'bg-gradient-to-r from-primary to-primary-hover hover:shadow-xl transform hover:-translate-y-0.5'
                                    ]"
                                >
                                    <svg v-if="processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-else>
                                        {{ paymentMethod ? `Settle ${selectedOrder.currency || currentCurrency} ${totals.total.toFixed(2)}` : 'Select Payment Method' }}
                                    </span>
                                </button>
                            </div>
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
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const currentCurrency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    orders: any[];
    tables: any[];
    // ...
}>();

const searchQuery = ref('');
const selectedOrder = ref<any>(null);
const paymentMethod = ref<string>('cash');
const processing = ref(false);

// Formatting state
const discountType = ref('fixed');
const discountValue = ref(0);
const additionalChargeType = ref('fixed');
const additionalChargeValue = ref(0);
const editableItems = ref<any[]>([]);

// Adjustment Mode State
const adjustmentMode = ref<'none' | 'discount' | 'extra'>('none');
// Temporary state for the active adjustment input
const activeType = ref('fixed');
const activeValue = ref(0);

watch(selectedOrder, (newOrder) => {
    if (newOrder) {
        editableItems.value = JSON.parse(JSON.stringify(newOrder.items));
        
        discountType.value = newOrder.discount_type || 'fixed';
        discountValue.value = parseFloat(newOrder.discount_value) || 0;
        additionalChargeType.value = newOrder.additional_charge_type || 'fixed';
        additionalChargeValue.value = parseFloat(newOrder.additional_charge_value) || 0;
        
        // Legacy fallback
        if (!newOrder.discount_type && newOrder.discount_amount > 0) {
            discountType.value = 'fixed';
            discountValue.value = newOrder.discount_amount;
        }
        if (!newOrder.additional_charge_type && newOrder.additional_charge > 0) {
            additionalChargeType.value = 'fixed';
            additionalChargeValue.value = newOrder.additional_charge;
        }

        paymentMethod.value = newOrder.payment_method || 'cash';
        adjustmentMode.value = 'none'; // reset mode
    } else {
        discountValue.value = 0;
        additionalChargeValue.value = 0;
        editableItems.value = [];
    }
});

// Sync selected order when props update
watch(() => props.orders, (newOrders) => {
    if (selectedOrder.value) {
        const freshOrder = newOrders.find(o => o.id === selectedOrder.value.id);
        if (freshOrder) {
            // Update selected order with fresh data from server
            selectedOrder.value = freshOrder; 
            // Note: We don't overwrite editableItems here to avoid interrupting user editing
            // But if we just settled/saved, we should.
            // Simplified: if processing was true (we saved), then overwrite.
        }
    }
});

const filteredOrders = computed(() => {
    if (!searchQuery.value) return props.orders;
    const query = searchQuery.value.toLowerCase();
    return props.orders.filter(order => 
        order.order_number.toLowerCase().includes(query) ||
        (order.customer_name && order.customer_name.toLowerCase().includes(query)) ||
        (order.table && order.table.name.toLowerCase().includes(query))
    );
});

// Live Calculation of Totals based on editableItems and current adjustment settings
const totals = computed(() => {
    if (!selectedOrder.value) return { subtotal: 0, tax: 0, discountAmount: 0, additionalChargeAmount: 0, total: 0 };

    const subtotal = editableItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
    const tax = subtotal * 0.05; // Assuming 5%
    
    let discountAmount = 0;
    if (discountType.value === 'percent') {
        discountAmount = subtotal * (discountValue.value / 100);
    } else {
        discountAmount = discountValue.value;
    }

    let additionalChargeAmount = 0;
    if (additionalChargeType.value === 'percent') {
        additionalChargeAmount = subtotal * (additionalChargeValue.value / 100);
    } else {
        additionalChargeAmount = additionalChargeValue.value;
    }

    const total = Math.max(0, subtotal + tax + additionalChargeAmount - discountAmount);

    return { subtotal, tax, discountAmount, additionalChargeAmount, total };
});

const hasUnsavedChanges = computed(() => {
     if (!selectedOrder.value) return false;
     const currentTotal = totals.value.total;
     const savedTotal = Number(selectedOrder.value.total);
     return Math.abs(currentTotal - savedTotal) > 0.01; 
});

const selectOrder = (order: any) => {
    selectedOrder.value = order;
};

const updateQuantity = (index: number, delta: number) => {
    const item = editableItems.value[index];
    if (item) {
        item.quantity = Math.max(0, item.quantity + delta);
    }
    // Auto-update order on quantity change? 
    // User requested "price updated based on updated".
    // I will trigger updateOrder debounced? Or just show the "Update Bill" button strongly.
    // I'll show the "Changes Detected" button.
};

// When user clicks 'Apply' in adjustment box
const applyAdjustment = () => {
    if (adjustmentMode.value === 'discount') {
        discountType.value = activeType.value;
        discountValue.value = activeValue.value;
        // If applying discount, ensure charge is not conflicting if logic demands? No, both allowed.
    } else if (adjustmentMode.value === 'extra') {
        additionalChargeType.value = activeType.value;
        additionalChargeValue.value = activeValue.value;
    }
    adjustmentMode.value = 'none'; // Close mode
    updateOrder(); // Save immediately
};

// When entering adjustment mode, load current values
watch(adjustmentMode, (mode) => {
    if (mode === 'discount') {
        activeType.value = discountType.value;
        activeValue.value = discountValue.value;
    } else if (mode === 'extra') {
        activeType.value = additionalChargeType.value;
        activeValue.value = additionalChargeValue.value;
    }
});

const updateOrder = () => {
    if (!selectedOrder.value) return;
    processing.value = true;
    router.put(`/pos/${selectedOrder.value.id}`, {
        items: editableItems.value.map(item => ({ id: item._id || item.id, quantity: item.quantity })),
        discount_type: discountType.value,
        discount_value: discountValue.value,
        additional_charge_type: additionalChargeType.value,
        additional_charge_value: additionalChargeValue.value
    }, {
        onSuccess: () => processing.value = false,
        onError: () => processing.value = false
    });
};

const settleBill = () => {
    if (!selectedOrder.value || !paymentMethod.value) return;
    
    // If unsaved changes, force update first?
    if (hasUnsavedChanges.value) {
        // optionally alert user?
        // updateOrder(); // and then settle?
        // simple: disable settle if changes detected. (Already done in template)
        return;
    }

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
