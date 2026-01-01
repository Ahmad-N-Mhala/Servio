<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Cash Register Status Bar -->
            <div v-if="currentRegister" class="mb-6 glass-card rounded-xl p-4 border border-primary/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ $t('pos.register_balance') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(currentBalance) }}</p>
                        </div>
                        <div class="ml-4 pl-4 border-l border-gray-200">
                            <p class="text-xs text-gray-500">{{ $t('pos.opened') }}</p>
                            <p class="text-sm font-medium text-gray-700">{{ formatTime(currentRegister.opened_at) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showWithdrawModal = true" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Withdraw
                        </button>
                        <button @click="showDepositModal = true" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Deposit
                        </button>
                        <button @click="showCloseModal = true" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            {{ $t('common.close') }} Register
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Open Register Prompt -->
            <div v-else class="mb-6 glass-card rounded-xl p-4 border-2 border-dashed border-yellow-300 bg-yellow-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-yellow-900">{{ $t('pos.register_not_open') }}</p>
                            <p class="text-xs text-yellow-700">{{ $t('pos.register_prompt') }}</p>
                        </div>
                    </div>
                    <button @click="showOpenModal = true" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                        {{ $t('pos.open_register') }}
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('pos.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('pos.subtitle') }}</p>
                </div>
                <Link 
                    v-if="hasPermission('view_cash_register_history')"
                    :href="route('cash-register.history')" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $t('pos.view_history') }}
                </Link>
            </div>

            <div class="flex gap-8 h-[calc(100vh-12rem)]">
                <!-- Left Column: Unpaid Orders -->
                <div class="w-1/2 flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar">
                    <h2 class="text-lg font-bold text-gray-900 sticky top-0 bg-gray-50 pb-2 z-10 flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            {{ $t('pos.active_orders') }}
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
                        <p class="text-gray-500">{{ $t('pos.no_active_orders') }}</p>
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
                                        {{ order.table ? order.table.name : $t('pos.takeaway') }}
                                    </span>
                                    <span v-if="order.type === 'dine_in'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/20">
                                        {{ $t('pos.dine_in') }}
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/20">
                                        {{ $t('pos.takeaway') }}
                                    </span>
                                </div>
                                <span class="text-xs" :class="selectedOrder?.id === order.id ? 'text-white/80' : 'text-gray-400'">
                                    {{ order.items.length }} {{ $t('common.items') }} • {{ order.customer_name || $t('common.guest') }}
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
                            <div class="flex items-center gap-4">
                                <!-- Restaurant Logo -->
                                <div v-if="currentRestaurant?.logo" class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200 bg-white">
                                    <img :src="`/storage/${currentRestaurant.logo}`" class="w-full h-full object-contain" alt="Restaurant Logo" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">{{ currentRestaurant?.name || $t('pos.bill_details') }}</h2>
                                    <p class="text-xs opacity-75">{{ new Date().toLocaleDateString() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ selectedOrder.table ? $t('pos.table') + ': ' + selectedOrder.table.name : $t('pos.takeaway') }}</p>
                                <p class="text-xs text-gray-500">{{ selectedOrder.customer_name || $t('common.guest') }}</p>
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
                                        @click="selectPaymentMethod(method)"
                                        :disabled="method === 'cash' && !currentRegister"
                                        :class="['py-2 px-2 rounded-lg flex flex-col items-center gap-0.5 border transition-all relative',
                                            paymentMethod === method 
                                                ? 'bg-primary/5 border-primary text-primary ring-1 ring-primary' 
                                                : method === 'cash' && !currentRegister
                                                ? 'bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed'
                                                : 'bg-white border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'
                                        ]"
                                        :title="method === 'cash' && !currentRegister ? 'Cash register must be open to accept cash payments' : ''"
                                    >
                                        <span class="text-[10px] font-bold uppercase">{{ method }}</span>
                                        <svg v-if="method === 'cash' && !currentRegister" class="w-3 h-3 text-red-500 absolute top-1 right-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                                        </svg>
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

        <!-- Cash Register Modals -->
        <!-- Open Register Modal -->
        <div v-if="showOpenModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Open Cash Register</h3>
                <form @submit.prevent="submitOpen">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Opening Balance</label>
                            <input
                                v-model="openForm.opening_balance"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea
                                v-model="openForm.opening_notes"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Any notes about the opening balance..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeOpenModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="openForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-hover disabled:opacity-50">
                            {{ openForm.processing ? 'Opening...' : 'Open Register' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Close Register Modal -->
        <div v-if="showCloseModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Close Cash Register</h3>
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Expected Balance</p>
                            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(currentBalance) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Difference</p>
                            <p class="text-xl font-bold" :class="{
                                'text-green-600': calculateDifference() > 0,
                                'text-red-600': calculateDifference() < 0,
                                'text-gray-600': calculateDifference() === 0
                            }">
                                {{ calculateDifference() > 0 ? '+' : '' }}{{ formatCurrency(calculateDifference()) }}
                            </p>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="submitClose">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Actual Closing Balance</label>
                            <input
                                v-model="closeForm.closing_balance"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Count the cash in the register..."
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Closing Notes (Optional)</label>
                            <textarea
                                v-model="closeForm.closing_notes"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Any notes about the closing..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeCloseModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="closeForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                            {{ closeForm.processing ? 'Closing...' : 'Close Register' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Withdraw Cash</h3>
                <div class="bg-blue-50 rounded-xl p-4 mb-4">
                    <p class="text-sm text-blue-700">Current Balance: <span class="font-bold">{{ formatCurrency(currentBalance) }}</span></p>
                </div>
                <form @submit.prevent="submitWithdraw">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Withdraw</label>
                            <input
                                v-model="withdrawForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                :max="currentBalance"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Withdrawal <span class="text-red-500">*</span></label>
                            <textarea
                                v-model="withdrawForm.notes"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="e.g., Bank deposit, petty cash, etc..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeWithdrawModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="withdrawForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                            {{ withdrawForm.processing ? 'Processing...' : 'Withdraw' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="showDepositModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Add Cash</h3>
                <form @submit.prevent="submitDeposit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Add</label>
                            <input
                                v-model="depositForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Deposit <span class="text-red-500">*</span></label>
                            <textarea
                                v-model="depositForm.notes"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="e.g., Change from bank, returned cash, etc..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeDepositModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="depositForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            {{ depositForm.processing ? 'Processing...' : 'Add Cash' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { usePermissions } from '@/Composables/usePermissions';

const page = usePage();
const currentCurrency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const currentRestaurant = computed(() => (page.props.current_restaurant as any));
const { hasPermission } = usePermissions();

const props = defineProps<{
    orders: any[];
    tables: any[];
    currentRegister: any;
    currentBalance: number;
}>();

// Cash Register Modals
const showOpenModal = ref(false);
const showCloseModal = ref(false);
const showWithdrawModal = ref(false);
const showDepositModal = ref(false);

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

const selectPaymentMethod = (method: string) => {
    if (method === 'cash' && !props.currentRegister) {
        // Don't allow selecting cash if register is not open
        return;
    }
    paymentMethod.value = method;
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

// Helper functions for cash register
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-AE', { 
        style: 'currency', 
        currency: currentCurrency.value 
    }).format(value || 0);
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Cash Register Forms
const openForm = ref({
    opening_balance: '',
    opening_notes: '',
    processing: false,
});

const closeForm = ref({
    closing_balance: '',
    closing_notes: '',
    processing: false,
});

const withdrawForm = ref({
    amount: '',
    notes: '',
    processing: false,
});

const depositForm = ref({
    amount: '',
    notes: '',
    processing: false,
});

// Cash Register Functions
const submitOpen = () => {
    openForm.value.processing = true;
    router.post(route('cash-register.open'), {
        opening_balance: openForm.value.opening_balance,
        opening_notes: openForm.value.opening_notes,
    }, {
        onSuccess: () => {
            closeOpenModal();
        },
        onFinish: () => {
            openForm.value.processing = false;
        },
    });
};

const submitClose = () => {
    if (!props.currentRegister) return;
    closeForm.value.processing = true;
    router.post(route('cash-register.close', props.currentRegister.id), {
        closing_balance: closeForm.value.closing_balance,
        closing_notes: closeForm.value.closing_notes,
    }, {
        onSuccess: () => {
            closeCloseModal();
        },
        onFinish: () => {
            closeForm.value.processing = false;
        },
    });
};

const submitWithdraw = () => {
    if (!props.currentRegister) return;
    withdrawForm.value.processing = true;
    router.post(route('cash-register.withdraw', props.currentRegister.id), {
        amount: withdrawForm.value.amount,
        notes: withdrawForm.value.notes,
    }, {
        onSuccess: () => {
            closeWithdrawModal();
        },
        onFinish: () => {
            withdrawForm.value.processing = false;
        },
    });
};

const submitDeposit = () => {
    if (!props.currentRegister) return;
    depositForm.value.processing = true;
    router.post(route('cash-register.deposit', props.currentRegister.id), {
        amount: depositForm.value.amount,
        notes: depositForm.value.notes,
    }, {
        onSuccess: () => {
            closeDepositModal();
        },
        onFinish: () => {
            depositForm.value.processing = false;
        },
    });
};

const closeOpenModal = () => {
    showOpenModal.value = false;
    openForm.value = { opening_balance: '', opening_notes: '', processing: false };
};

const closeCloseModal = () => {
    showCloseModal.value = false;
    closeForm.value = { closing_balance: '', closing_notes: '', processing: false };
};

const closeWithdrawModal = () => {
    showWithdrawModal.value = false;
    withdrawForm.value = { amount: '', notes: '', processing: false };
};

const closeDepositModal = () => {
    showDepositModal.value = false;
    depositForm.value = { amount: '', notes: '', processing: false };
};

const calculateDifference = () => {
    if (!closeForm.value.closing_balance) return 0;
    return parseFloat(closeForm.value.closing_balance) - props.currentBalance;
};

// Add route helper
const route = (window as any).route;
</script>
