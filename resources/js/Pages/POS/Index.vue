<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Cash Register Status Bar -->
            <div v-if="currentRegister" class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">{{ $t('pos.register_balance') }}</p>
                        <p class="text-xl font-bold text-gray-900 leading-none mt-0.5">{{ formatCurrency(currentBalance) }}</p>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-gray-200 mx-2"></div>
                    <div class="hidden sm:block">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">{{ $t('pos.opened') }}</p>
                        <p class="text-sm font-medium text-gray-700 leading-none mt-0.5">{{ formatTime(currentRegister.opened_at) }}</p>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="showWithdrawModal = true" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-white hover:shadow-sm transition-all flex items-center justify-center gap-2">
                         <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        {{ $t('cash_register.withdraw') }}
                    </button>
                    <button 
                        v-if="currentRestaurant?.has_cash_drawer"
                        @click="openDrawer" 
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-white hover:shadow-sm transition-all flex items-center justify-center gap-2"
                        title="Open Cash Drawer"
                    >
                         <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" /></svg>
                        Drawer
                    </button>
                    <button @click="showDepositModal = true" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-white hover:shadow-sm transition-all flex items-center justify-center gap-2">
                         <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        {{ $t('cash_register.deposit') }}
                    </button>
                    <button @click="showCloseModal = true" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 shadow-sm hover:shadow transition-all">
                        {{ $t('common.close') }}
                    </button>
                </div>
            </div>
            
            <!-- Open Register Prompt -->
            <div v-else class="mb-6 bg-yellow-50 rounded-2xl p-6 border border-yellow-100 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-yellow-900">{{ $t('pos.register_not_open') }}</h3>
                        <p class="text-sm text-yellow-700">{{ $t('pos.register_prompt') }}</p>
                    </div>
                </div>
                <button @click="showOpenModal = true" class="px-6 py-2.5 text-sm font-bold text-white bg-yellow-500 rounded-xl hover:bg-yellow-600 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    {{ $t('pos.open_register') }}
                </button>
            </div>

            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $t('pos.title') }}</h1>
                    <p class="text-sm text-gray-500 font-medium">{{ $t('pos.subtitle') }}</p>
                </div>
                <Link 
                    v-if="hasPermission('view_cash_register_history')"
                    :href="route('cash-register.history')" 
                    class="text-sm font-semibold text-primary hover:text-primary-dark hover:underline flex items-center gap-1"
                >
                    {{ $t('pos.view_history') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </Link>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 h-auto lg:h-[calc(100vh-14rem)]">
                <!-- Left Column: Unpaid Orders -->
                <div class="w-full lg:w-5/12 flex flex-col gap-3 overflow-y-auto pr-1 custom-scrollbar min-h-[500px] lg:min-h-0">
                    <div class="sticky top-0 bg-gray-50/95 backdrop-blur z-10 pb-2 space-y-3">
                         <div class="relative">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                :placeholder="$t('common.search_orders')"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-none ring-1 ring-gray-200 focus:ring-2 focus:ring-primary shadow-sm text-sm transition-all"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div v-if="filteredOrders.length === 0" class="flex flex-col items-center justify-center p-10 text-center rounded-2xl border-2 border-dashed border-gray-200 bg-white/50">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-300 mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <p class="text-gray-500 font-medium">{{ $t('pos.no_active_orders') }}</p>
                    </div>

                    <div 
                        v-for="order in filteredOrders" 
                        :key="order.id" 
                        @click="selectOrder(order)"
                        :class="['cursor-pointer p-4 rounded-xl border transition-all duration-200 relative overflow-hidden group shrink-0',
                            selectedOrder?.id === order.id 
                                ? 'bg-primary text-white border-primary shadow-primary/30 shadow-lg scale-[1.01]' 
                                : 'bg-white border-transparent shadow-sm hover:shadow-md hover:border-gray-200'
                        ]"
                    >
                        <div class="flex justify-between items-start z-10 relative">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold opacity-75 uppercase tracking-wide mb-0.5">Order</span>
                                <h3 class="font-bold text-xl leading-none">#{{ order.order_number }}</h3>
                            </div>
                             <div class="text-right">
                                <span class="text-lg font-bold block">{{ order.currency || currentCurrency }} {{ order.total }}</span>
                                <span class="text-xs opacity-75">{{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-white/20 flex justify-between items-end z-10 relative">
                            <div class="flex items-center gap-2">
                                <span v-if="order.table" class="px-2 py-1 rounded-md text-xs font-bold bg-white/20 backdrop-blur-sm">
                                    {{ order.table.name }}
                                </span>
                                <span v-else class="px-2 py-1 rounded-md text-xs font-bold bg-white/20 backdrop-blur-sm">
                                    {{ $t('pos.takeaway') }}
                                </span>
                                <span class="text-xs opacity-80 pl-1">{{ order.customer_name || 'Guest' }}</span>
                            </div>
                            <span class="text-xs font-medium opacity-75">{{ order.items.length }} items</span>
                        </div>
                        
                        <!-- Decorative background circle for selected state -->
                        <div v-if="selectedOrder?.id === order.id" class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl z-0 pointer-events-none"></div>
                    </div>
                </div>


                <!-- Right Column: Bill Details & Payment -->
                <div class="w-full lg:w-7/12 flex flex-col h-full lg:h-auto">
                    <div v-if="selectedOrder" class="bg-white flex flex-col h-full rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <!-- Bill Header -->
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-gray-900 leading-none">{{ selectedOrder.table ? selectedOrder.table.name : $t('pos.takeaway') }}</h2>
                                    <p class="text-xs text-gray-500 mt-1">{{ selectedOrder.customer_name || $t('common.guest') }}</p>
                                </div>
                            </div>
                            <button 
                                @click="showUpdateOrderModal = true"
                                class="px-4 py-2 bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-blue-100 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                {{ $t('common.edit') }}
                            </button>
                        </div>

                        <!-- Bill Items -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            <div v-if="editableItems.length === 0" class="flex flex-col items-center justify-center h-48 text-gray-400">
                                <p class="text-sm">{{ $t('orders.no_items') }}</p>
                            </div>
                            
                            <div v-for="item in editableItems" :key="item.id" class="flex text-sm group border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                <div class="w-8 pt-1">
                                    <span class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 text-xs font-bold rounded-full border border-gray-200 shadow-sm">{{ item.quantity }}</span>
                                </div>
                                <div class="flex-1 px-2">
                                    <p class="font-medium text-gray-900">
                                        {{ item.menu_item?.name?.en || item.menu_item?.name || item.name || $t('common.item') }}
                                        <span v-if="item.menu_item?.deleted_at" class="text-red-500 text-xs">({{ $t('common.deleted') }})</span>
                                    </p>
                                    <p v-if="item.notes" class="text-xs text-amber-600 mt-0.5 italic flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        {{ item.notes }}
                                    </p>
                                    <div v-if="item.extras && item.extras.length > 0" class="mt-1 space-y-0.5">
                                        <p v-for="(extra, i) in item.extras" :key="i" class="text-xs text-gray-500 flex justify-between w-full max-w-[200px]">
                                            <span>+ {{ extra.name?.en || extra.name || 'Extra' }}</span>
                                            <span>{{ selectedOrder.currency || currentCurrency }} {{ Number(extra.price).toFixed(2) }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right font-bold text-gray-900">
                                    {{ selectedOrder.currency || currentCurrency }} {{ ((Number(item.unit_price) + (item.extras?.reduce((acc: number, extra: any) => acc + Number(extra.price), 0) || 0)) * item.quantity).toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Section: Adjustments, Totals, Payment -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 space-y-4 shrink-0">
                            
                            <!-- Adjustments Toggle -->
                            <div v-if="adjustmentMode === 'none'" class="flex gap-2">
                                <button @click="adjustmentMode = 'discount'" class="flex-1 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $t('pos.add_discount') }}
                                </button>
                                <button @click="adjustmentMode = 'extra'" class="flex-1 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                    {{ $t('pos.add_extra_charge') }}
                                </button>
                            </div>

                            <!-- Active Adjustment Inputs -->
                            <div v-else class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm animate-fade-in-up">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ adjustmentMode === 'discount' ? $t('pos.apply_discount') : $t('pos.apply_extra_charge') }}
                                    </span>
                                    <button @click="adjustmentMode = 'none'" class="text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="flex gap-2">
                                    <div class="flex bg-white rounded-lg p-1 border border-gray-200 shadow-sm">
                                        <button @click="activeType = 'fixed'" :class="['px-3 py-1 rounded-md text-xs font-bold transition-all', activeType === 'fixed' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900']">{{ $t('pos.fixed') }}</button>
                                        <button @click="activeType = 'percent'" :class="['px-3 py-1 rounded-md text-xs font-bold transition-all', activeType === 'percent' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900']">%</button>
                                    </div>
                                    <input 
                                        v-model.number="activeValue" 
                                        type="number" min="0" step="0.01"
                                        class="flex-1 rounded-lg border-gray-200 text-sm focus:ring-2 focus:ring-primary focus:border-transparent py-1.5 px-3 shadow-sm"
                                        placeholder="0.00"
                                    >
                                    <button @click="applyAdjustment" class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-black transition-colors">Apply</button>
                                </div>
                            </div>

                            <!-- Calculations Table -->
                            <div class="space-y-2 py-3 border-t border-gray-100/50">
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>{{ $t('pos.subtotal') }}</span>
                                    <span class="font-medium text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.subtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>{{ $t('pos.tax') }}</span>
                                    <span class="font-medium text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.tax.toFixed(2) }}</span>
                                </div>
                                <div v-if="totals.discountAmount > 0" class="flex justify-between text-sm text-red-600 bg-red-50 p-2 rounded-lg">
                                    <span>{{ $t('pos.discount') }} <span v-if="discountType === 'percent'" class="text-xs opacity-75">({{ discountValue }}%)</span></span>
                                    <span class="font-bold">- {{ selectedOrder.currency || currentCurrency }} {{ totals.discountAmount.toFixed(2) }}</span>
                                </div>
                                <div v-if="totals.additionalChargeAmount > 0" class="flex justify-between text-sm text-blue-600 bg-blue-50 p-2 rounded-lg">
                                    <span>{{ $t('pos.extra_charge') }} <span v-if="additionalChargeType === 'percent'" class="text-xs opacity-75">({{ additionalChargeValue }}%)</span></span>
                                    <span class="font-bold">+ {{ selectedOrder.currency || currentCurrency }} {{ totals.additionalChargeAmount.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Payment Area -->
                        <div class="bg-gray-50 p-6 border-t border-gray-200">
                            <!-- Total Display -->
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">{{ $t('pos.total_amount') }}</p>
                                    <div v-if="hasUnsavedChanges" class="flex items-center gap-2 mt-1 animate-pulse text-yellow-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span class="text-xs font-bold">{{ $t('pos.changes_detected') }}</span>
                                    </div>
                                </div>
                                <p class="text-3xl font-black text-gray-900">{{ selectedOrder.currency || currentCurrency }} {{ totals.total.toFixed(2) }}</p>
                            </div>

                            <!-- Payment Methods -->
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <button 
                                    v-for="method in ['cash', 'card', 'online']" 
                                    :key="method"
                                    @click="selectPaymentMethod(method)"
                                    :disabled="method === 'cash' && !currentRegister"
                                    :class="['py-3 rounded-xl border-2 flex flex-col items-center justify-center gap-1 transition-all',
                                        paymentMethod === method 
                                            ? 'border-primary bg-primary/5 text-primary shadow-sm' 
                                            : 'border-white bg-white text-gray-400 hover:border-gray-200 hover:text-gray-600 shadow-sm'
                                    ]"
                                >
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ $t('pos.' + method) }}</span>
                                    <svg v-if="method === 'cash' && !currentRegister" class="w-3 h-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </button>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                <button 
                                    @click="printCurrentReceipt" 
                                    :disabled="hasUnsavedChanges"
                                    class="w-full py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 rounded-xl font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" /></svg>
                                    {{ hasUnsavedChanges ? $t('pos.save_to_print') : $t('pos.print_receipt') }}
                                </button>

                                <div class="flex items-center justify-between gap-3 text-sm text-gray-600 px-1">
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" v-model="printBill" class="rounded border-gray-300 text-primary focus:ring-primary shadow-sm">
                                        <span class="font-medium">{{ $t('pos.print_bill_pos') }}</span>
                                    </label>
                                    <span v-if="hasUnsavedChanges" class="text-xs text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full font-bold">Unsaved Changes</span>
                                </div>

                                <button 
                                    v-if="hasUnsavedChanges"
                                    @click="updateOrder" 
                                    :disabled="processing"
                                    class="w-full py-4 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2"
                                >
                                    {{ processing ? $t('common.saving') : $t('orders.save_changes') }}
                                </button>
                                <button 
                                    v-else
                                    @click="settleBill" 
                                    :disabled="processing || !paymentMethod"
                                    class="w-full py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 transform active:scale-[0.99]"
                                >
                                    <span v-if="processing" class="animate-spin mr-2">⟳</span>
                                    {{ $t('pos.settle') }} {{ selectedOrder.currency || currentCurrency }} {{ totals.total.toFixed(2) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Right Column -->
                    <div v-else class="h-full bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center p-12 text-center">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $t('pos.select_order_hint') }}</h3>
                        <p class="text-gray-500 max-w-xs">{{ $t('pos.no_order_selected') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Register Modals -->
        <!-- Open Register Modal -->
        <div v-if="showOpenModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $t('cash_register.open_cash_register') }}</h3>
                <form @submit.prevent="submitOpen">
                    <div class="space-y-4">
                    <div class="space-y-4">
                        <div>
                            <Input
                                v-model="openForm.opening_balance"
                                :label="$t('cash_register.opening_balance')"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                             <Input
                                v-model="openForm.opening_notes"
                                :label="$t('cash_register.notes_optional')"
                                type="textarea"
                                rows="3"
                                :placeholder="$t('cash_register.notes_placeholder')"
                            />
                        </div>
                    </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeOpenModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            {{ $t('cash_register.cancel') }}
                        </button>
                        <button type="submit" :disabled="openForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-hover disabled:opacity-50">
                            {{ openForm.processing ? 'Opening...' : $t('cash_register.open_register') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Close Register Modal -->
        <div v-if="showCloseModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $t('cash_register.close_register') }}</h3>
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ $t('cash_register.expected_balance') }}</p>
                            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(currentBalance) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ $t('cash_register.difference') }}</p>
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
                    <div class="space-y-4">
                        <div>
                            <Input
                                v-model="closeForm.closing_balance"
                                :label="$t('cash_register.actual_balance')"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                :placeholder="$t('cash_register.count_placeholder')"
                            />
                        </div>
                        <div>
                            <Input
                                v-model="closeForm.closing_notes"
                                :label="$t('cash_register.closing_notes')"
                                type="textarea"
                                rows="3"
                                :placeholder="$t('cash_register.closing_notes_placeholder')"
                            />
                        </div>
                    </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeCloseModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $t('common.cancel') }}</button>
                        <button type="submit" :disabled="closeForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                            {{ closeForm.processing ? $t('cash_register.closing') : $t('cash_register.close_action') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $t('cash_register.withdraw_cash') }}</h3>
                <div class="bg-blue-50 rounded-xl p-4 mb-4">
                    <p class="text-sm text-blue-700">{{ $t('cash_register.current_balance') }}: <span class="font-bold">{{ formatCurrency(currentBalance) }}</span></p>
                </div>
                <form @submit.prevent="submitWithdraw">
                    <div class="space-y-4">
                        <div>
                            <Input
                                v-model="withdrawForm.amount"
                                :label="$t('cash_register.withdraw_amount')"
                                type="number"
                                step="0.01"
                                min="0.01"
                                :max="currentBalance"
                                required
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <Input
                                v-model="withdrawForm.notes"
                                :label="$t('cash_register.withdraw_reason')"
                                type="textarea"
                                required
                                rows="3"
                                :placeholder="$t('cash_register.withdraw_reason_placeholder')"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeWithdrawModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $t('common.cancel') }}</button>
                        <button type="submit" :disabled="withdrawForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                            {{ withdrawForm.processing ? $t('common.processing') : $t('cash_register.withdraw') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="showDepositModal && currentRegister" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $t('cash_register.add_cash') }}</h3>
                <form @submit.prevent="submitDeposit">
                    <div class="space-y-4">
                        <div>
                            <Input
                                v-model="depositForm.amount"
                                :label="$t('cash_register.add_amount')"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <Input
                                v-model="depositForm.notes"
                                :label="$t('cash_register.deposit_reason')"
                                type="textarea"
                                required
                                rows="3"
                                :placeholder="$t('cash_register.deposit_reason_placeholder')"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeDepositModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $t('common.cancel') }}</button>
                        <button type="submit" :disabled="depositForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            {{ depositForm.processing ? $t('common.processing') : $t('cash_register.add_cash') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Update Order Modal -->
        <div v-if="showUpdateOrderModal && selectedOrder" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full my-8">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-blue-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $t('orders.update_order') }} #{{ selectedOrder.order_number }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $t('orders.modify_details') }}</p>
                        </div>
                        <button @click="closeUpdateOrderModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-6 max-h-[75vh] overflow-y-auto custom-scrollbar space-y-6">
                    <!-- Customer Details Section -->
                    <div class="glass-card rounded-xl p-5 border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-1.5 bg-primary/10 rounded-lg">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            {{ $t('customers.details') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('customers.customer_name') }}</label>
                                <input 
                                    v-model="updateForm.customer_name"
                                    type="text"
                                    :placeholder="$t('common.optional')"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('customers.phone_number') }}</label>
                                <PhoneInput 
                                    v-model="updateForm.customer_phone"
                                    :country="currentRestaurant?.country"
                                    :placeholder="$t('common.optional')"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Order Type & Table Section -->
                    <div v-if="currentRestaurant?.service_type !== 'self_service'" class="glass-card rounded-xl p-5 border border-gray-200 overflow-visible">
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            {{ $t('orders.type_table') }}
                        </h4>
                        <div class="flex gap-3 mb-4">
                            <label class="flex-1 cursor-pointer group">
                                <input type="radio" v-model="updateForm.type" value="dine_in" class="peer sr-only">
                                <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 peer-checked:hover:border-primary transition-all text-center flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400 peer-checked:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span class="font-semibold text-sm text-gray-700 peer-checked:text-primary">{{ $t('kitchen.dine_in') }}</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer group">
                                <input type="radio" v-model="updateForm.type" value="takeaway" class="peer sr-only">
                                <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 peer-checked:hover:border-primary transition-all text-center flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400 peer-checked:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span class="font-semibold text-sm text-gray-700 peer-checked:text-primary">{{ $t('kitchen.takeaway') }}</span>
                                </div>
                            </label>
                        </div>
                        <div v-if="updateForm.type === 'dine_in'" class="animate-fade-in-up relative" style="z-index: 9999;">
                            <Select
                                v-model="updateForm.table_id"
                                :label="$t('common.select') + ' ' + $t('common.table')"
                                :options="tableOptions"
                                :placeholder="$t('common.no_table')"
                            />
                        </div>
                    </div>

                    <!-- Current Order Items -->
                    <div class="glass-card rounded-xl p-5 border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded-lg">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            {{ $t('orders.items') }}
                            <span class="ml-auto text-xs bg-primary/10 text-primary px-2 py-1 rounded-full font-semibold">{{ editableItems.length }} {{ $t('common.items') }}</span>
                        </h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                            <div v-for="(item, index) in editableItems" :key="item.id" class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-100 hover:border-primary/30 transition-all">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ item.menu_item?.name?.en || item.menu_item?.name || item.name || 'Item' }}
                                            <span v-if="item.menu_item?.deleted_at" class="text-red-500 text-xs">({{ $t('common.deleted') }})</span>
                                        </span>
                                        <div v-if="item.extras && item.extras.length > 0" class="text-xs text-blue-600 flex flex-wrap gap-1">
                                            <span v-for="(extra, i) in item.extras" :key="i">
                                                + {{ extra.name?.en || extra.name }} <span v-if="Number(i) < (item.extras.length - 1)">,</span>
                                            </span>
                                        </div>
                                    </div>
                                    <span v-if="item.notes" class="text-xs text-gray-500 italic bg-amber-50 px-2 py-0.5 rounded">📝 {{ item.notes }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center bg-white rounded-lg border border-gray-200 shadow-sm">
                                        <button @click="updateItemQuantity(index, -1)" class="px-3 py-1.5 text-gray-600 hover:text-red-500 hover:bg-red-50 rounded-l-lg transition-colors font-bold">−</button>
                                        <span class="w-10 text-center text-sm font-bold text-gray-900">{{ item.quantity }}</span>
                                        <button @click="updateItemQuantity(index, 1)" class="px-3 py-1.5 text-gray-600 hover:text-green-500 hover:bg-green-50 rounded-r-lg transition-colors font-bold">+</button>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 min-w-[5rem] text-right">
                                        {{ selectedOrder.currency || currentCurrency }} {{ ((Number(item.unit_price) + (item.extras?.reduce((acc: number, extra: any) => acc + Number(extra.price), 0) || 0)) * item.quantity).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="editableItems.length === 0" class="text-center text-gray-400 text-sm py-8 border-2 border-dashed border-gray-200 rounded-lg">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                {{ $t('orders.no_items_add_below') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Menu Items - Organized by Category -->
                    <div class="glass-card rounded-xl p-5 border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded-lg">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            {{ $t('menu.add_item') }}
                        </h4>
                        
                        <!-- Category Tabs -->
                        <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                            <button 
                                v-for="category in menuCategories" 
                                :key="category.id"
                                @click="selectedCategory = category.id"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all',
                                    selectedCategory === category.id 
                                        ? 'bg-primary text-white shadow-md' 
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                ]"
                            >
                                {{ category.name?.en || category.name }}
                                <span class="ml-1.5 text-xs opacity-75">({{ category.items?.length || 0 }})</span>
                            </button>
                        </div>

                        <!-- Menu Items Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-96 overflow-y-auto custom-scrollbar">
                            <button 
                                v-for="menuItem in filteredMenuItems" 
                                :key="menuItem.id"
                                @click="addItemToOrder(menuItem)"
                                class="p-3 text-left bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:shadow-lg transition-all group relative overflow-hidden"
                            >
                                <!-- Item Image (if available) -->
                                <div v-if="menuItem.image" class="w-full h-20 mb-2 rounded-lg overflow-hidden bg-gray-100">
                                    <img 
                                        :src="menuItem.image.startsWith('http') ? menuItem.image : '/storage/' + menuItem.image" 
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                        :alt="menuItem.name?.en || menuItem.name"
                                    />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-primary line-clamp-2 leading-tight">
                                        {{ menuItem.name?.en || menuItem.name }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs font-bold text-primary">{{ currentCurrency }} {{ menuItem.price }}</span>
                                        <svg class="w-5 h-5 text-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">{{ $t('orders.updated_total') }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $t('orders.tax_hint') }}</span>
                        </div>
                        <span class="text-2xl font-bold text-primary">{{ selectedOrder.currency || currentCurrency }} {{ calculateModalTotal().toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeUpdateOrderModal" class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all">{{ $t('common.cancel') }}</button>
                        <button 
                            @click="saveOrderUpdates" 
                            :disabled="processing"
                            class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-primary to-primary-hover rounded-xl hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                        >
                            <svg v-if="processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ processing ? $t('common.saving') : $t('common.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <Teleport to="body">
            <div id="pos-receipt-preview" class="print-overlay">
                <ReceiptPreview 
                    v-if="receiptPreviewOrder"
                    :template="currentRestaurant?.receipt_template || {}" 
                    :order="receiptPreviewOrder" 
                    :logo="currentRestaurant?.logo" 
                    :restaurant-name="currentRestaurant?.name"
                    :google-map-location="currentRestaurant?.google_map_location"
                />
            </div>
            <div id="drawer-kick-preview" class="print-overlay p-4 text-center text-xs opacity-0">
                .
            </div>
        </Teleport>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import { usePermissions } from '@/Composables/usePermissions';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import { printReceiptPreview } from '@/Utils/printReceipt';
import PhoneInput from '@/Components/PhoneInput.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';

const { t } = useI18n();

const page = usePage();
const currentCurrency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const currentRestaurant = computed(() => (page.props.current_restaurant as any));
const { hasPermission } = usePermissions();

const props = defineProps<{
    orders: any[];
    tables: any[];
    menuItems: any[];
    currentRegister: any;
    currentBalance: number;
    google_map_location?: string;
    receipt_template?: any;
    restaurant_logo?: string;
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
const printBill = ref(true); // Default to yes
const showUpdateOrderModal = ref(false);

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

// Update Form State
const updateForm = ref({
    customer_name: '',
    customer_phone: '',
    type: currentRestaurant.value?.service_type === 'self_service' ? 'takeaway' : 'dine_in',
    table_id: null as number | null
});

// Menu Category Selection
const selectedCategory = ref<number | null>(null);

// Organize menu items by category
const menuCategories = computed(() => {
    const categories: any[] = [];
    const categoryMap = new Map();
    
    props.menuItems.forEach((item: any) => {
        const categoryId = item.category_id || item.category?.id || 0;
        const categoryName = item.category?.name || { en: 'Uncategorized', ar: 'غير مصنف' };
        
        if (!categoryMap.has(categoryId)) {
            categoryMap.set(categoryId, {
                id: categoryId,
                name: categoryName,
                items: []
            });
            categories.push(categoryMap.get(categoryId));
        }
        
        categoryMap.get(categoryId).items.push(item);
    });
    
    return categories;
});

// Filtered menu items based on selected category
const filteredMenuItems = computed(() => {
    if (!selectedCategory.value) {
        // If no category selected, show first category or all items
        return menuCategories.value[0]?.items || props.menuItems;
    }
    
    const category = menuCategories.value.find(c => c.id === selectedCategory.value);
    return category?.items || [];
});

// Local orders state for real-time updates
const localOrders = ref<any[]>([]);

// Initialize localOrders
watch(() => props.orders, (newOrders) => {
    localOrders.value = [...newOrders];
}, { immediate: true });

// Echo Listener
onMounted(() => {
    if (window.Echo && currentRestaurant.value) {
        window.Echo.channel(`restaurant.${currentRestaurant.value.id}.orders`)
            .listen('.order.created', (e: any) => {
                if (e.order) {
                    // Check if exists (shouldn't, but safe check)
                    const index = localOrders.value.findIndex(o => o.id === e.order.id);
                    if (index === -1) {
                         // Add to top
                         localOrders.value.unshift(e.order);
                    }
                }
            })
            .listen('.order.updated', (e: any) => {
                 if (e.order) {
                    const index = localOrders.value.findIndex(o => o.id === e.order.id);
                    if (index !== -1) {
                        localOrders.value[index] = e.order;
                         // If selected, update it
                        if (selectedOrder.value?.id === e.order.id && !hasUnsavedChanges.value) {
                             selectedOrder.value = e.order;
                        }
                    }
                 }
            })
            .listen('.order.status_changed', (e: any) => {
                if (e.order) {
                     const index = localOrders.value.findIndex(o => o.id === e.order.id);
                     // If status is still one of 'pending', etc., update
                     // If finalized/paid depending on logic, maybe remove?
                     // POS shows "active" orders. If status becomes 'completed' and 'paid', standard query excludes it.
                     // But here we might just update it.
                     // Let's assume we keep it until refresh or let user handle it.
                     if (index !== -1) {
                         localOrders.value[index] = e.order;
                          if (selectedOrder.value?.id === e.order.id && !hasUnsavedChanges.value) {
                             selectedOrder.value = e.order;
                        }
                     }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo && currentRestaurant.value) {
        window.Echo.leave(`restaurant.${currentRestaurant.value.id}.orders`);
    }
});

watch(selectedOrder, (newOrder) => {
    if (newOrder) {
        editableItems.value = JSON.parse(JSON.stringify(newOrder.items)).map((item: any) => {
            // Safely parse extras if string
            let parsedExtras = item.extras;
            if (typeof parsedExtras === 'string') {
                try {
                    parsedExtras = JSON.parse(parsedExtras);
                } catch (e) {
                    parsedExtras = [];
                }
            }
            return {
                ...item,
                extras: Array.isArray(parsedExtras) ? parsedExtras : [],
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price),
                total_price: Number(item.total_price)
            };
        });
        
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
        
        // Populate update form with order details
        updateForm.value = {
            customer_name: newOrder.customer_name || '',
            customer_phone: newOrder.customer_phone || '',
            type: newOrder.type || 'dine_in',
            table_id: newOrder.table_id || null
        };
        
        // Initialize selected category to first category
        if (menuCategories.value.length > 0) {
            selectedCategory.value = menuCategories.value[0].id;
        }
    } else {
        discountValue.value = 0;
        additionalChargeValue.value = 0;
        editableItems.value = [];
    }
});

// Sync selected order when localOrders update
watch(localOrders, (newOrders) => {
    if (selectedOrder.value) {
        const freshOrder = newOrders.find(o => o.id === selectedOrder.value.id);
        if (freshOrder && !hasUnsavedChanges.value) {
            // Update selected order with fresh data from server IF no unsaved edits
            selectedOrder.value = freshOrder; 
        }
    }
},{ deep: true });

const filteredOrders = computed(() => {
    if (!searchQuery.value) return localOrders.value;
    const query = searchQuery.value.toLowerCase();
    return localOrders.value.filter(order => 
        order.order_number.toLowerCase().includes(query) ||
        (order.customer_name && order.customer_name.toLowerCase().includes(query)) ||
        (order.table && order.table.name.toLowerCase().includes(query))
    );
});

// Live Calculation of Totals based on editableItems and current adjustment settings
const totals = computed(() => {
    if (!selectedOrder.value) return { subtotal: 0, tax: 0, discountAmount: 0, additionalChargeAmount: 0, total: 0 };

    const subtotal = editableItems.value.reduce((sum, item) => {
        const itemTotal = Number(item.unit_price) * item.quantity;
        const extrasTotal = (item.extras || []).reduce((acc: number, extra: any) => acc + Number(extra.price), 0) * item.quantity;
        return sum + itemTotal + extrasTotal;
    }, 0);

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
     // Relax precision check to handle potential floating point differences between frontend/backend
     return Math.abs(currentTotal - savedTotal) > 0.1; 
});

const selectOrder = (order: any) => {
    selectedOrder.value = order;
};

const closeUpdateOrderModal = () => {
    showUpdateOrderModal.value = false;
};

// Modal functions for updating order items
const updateItemQuantity = (index: number, delta: number) => {
    const item = editableItems.value[index];
    if (item) {
        item.quantity = Math.max(0, item.quantity + delta);
        // Remove item if quantity is 0
        if (item.quantity === 0) {
            editableItems.value.splice(index, 1);
        }
    }
};

const addItemToOrder = (menuItem: any) => {
    // Check if item already exists in order
    const existingItem = editableItems.value.find(item => item.menu_item_id === menuItem.id);
    
    if (existingItem) {
        // Increment quantity if already exists
        existingItem.quantity += 1;
        existingItem.total_price = existingItem.quantity * Number(existingItem.unit_price);
    } else {
        // Add new item to order
        editableItems.value.push({
            id: `new_${Date.now()}`, // Temporary ID for new items
            menu_item_id: menuItem.id,
            menu_item: menuItem,
            quantity: 1,
            unit_price: Number(menuItem.price),
            total_price: Number(menuItem.price),
            notes: ''
        });
    }
};

const calculateModalTotal = () => {
    const subtotal = editableItems.value.reduce((sum, item) => {
        const itemTotal = Number(item.unit_price) * item.quantity;
        const extrasTotal = (item.extras || []).reduce((acc: number, extra: any) => acc + Number(extra.price), 0) * item.quantity;
        return sum + itemTotal + extrasTotal;
    }, 0);

    const tax = subtotal * 0.05;
    
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

    return Math.max(0, subtotal + tax + additionalChargeAmount - discountAmount);
};

const saveOrderUpdates = () => {
    if (!selectedOrder.value) return;
    
    processing.value = true;
    
    // Prepare items data - separate existing and new items
    const itemsData = editableItems.value.map(item => {
        if (item.id.toString().startsWith('new_')) {
            // New item
            return {
                menu_item_id: item.menu_item_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                notes: item.notes || '',
                extras: item.extras || []
            };
        } else {
            // Existing item
            return {
                id: item._id || item.id,
                quantity: item.quantity
            };
        }
    });
    
    router.put(route('pos.update', selectedOrder.value.id), {
        // Order items
        items: itemsData,
        // Adjustments
        discount_type: discountType.value,
        discount_value: discountValue.value,
        additional_charge_type: additionalChargeType.value,
        additional_charge_value: additionalChargeValue.value,
        // Customer details
        customer_name: updateForm.value.customer_name,
        customer_phone: updateForm.value.customer_phone,
        // Order type and table
        type: updateForm.value.type,
        table_id: updateForm.value.table_id
    }, {
        onSuccess: () => {
            processing.value = false;
            closeUpdateOrderModal();
        },
        onError: () => {
            processing.value = false;
        }
    });
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
    const itemsData = editableItems.value.map(item => {
        if (item.id.toString().startsWith('new_')) {
            // New item
            return {
                menu_item_id: item.menu_item_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                notes: item.notes || ''
            };
        } else {
            // Existing item
            return {
                id: item._id || item.id,
                quantity: item.quantity
            };
        }
    });

    router.put(route('pos.update', selectedOrder.value.id), {
        items: itemsData,
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
    router.post(route('pos.settle', selectedOrder.value.id), {
        payment_method: paymentMethod.value,
    }, {
        onSuccess: () => {
            try {
                // Print receipt if checked OR if cash drawer needs to be opened (cash payment + setting enabled)
                if (printBill.value || (currentRestaurant.value.has_cash_drawer && paymentMethod.value === 'cash')) {
                    printReceipt(selectedOrder.value);
                }
            } catch (e) {
                console.error('Print error in onSuccess:', e);
            } finally {
                selectedOrder.value = null; // Clear selection
                processing.value = false;
            }
        },
        onError: () => {
            processing.value = false;
        }
    });
};



// Enhanced Thermal Printer Receipt Function
// Supports multiple printer types: 80mm, 58mm thermal printers, and standard A4 printers
const receiptPreviewOrder = ref<any>(null);

// Enhanced Receipt Function using Component
const printReceipt = async (order: any) => {
    try {
        receiptPreviewOrder.value = order;
        await nextTick();
        
        // Allow time for QR code generation (async in component)
        const template = currentRestaurant.value?.receipt_template || {};
        const paperWidth = template.paper_width || '80';

        if (template.show_qr_code && currentRestaurant.value?.google_map_location) {
             await new Promise(resolve => setTimeout(resolve, 800)); // Wait for QR generation
        } else {
             await new Promise(resolve => setTimeout(resolve, 200)); // Wait for render
        }

        // Use the snapshot printing method
        printReceiptPreview('pos-receipt-preview', paperWidth);
        
    } catch (error) {
        console.error('Print Error:', error);
        alert('Failed to print receipt.');
    }
};

const printCurrentReceipt = async () => {
    if (!selectedOrder.value || hasUnsavedChanges.value) return;

    // Use the existing printReceipt function for iframe printing
    await printReceipt(selectedOrder.value);
};

const openDrawer = async () => {
    try {
        await nextTick();
        printReceiptPreview('drawer-kick-preview', '80');
    } catch (e) {
        console.error('Failed to open drawer', e);
    }
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

const tableOptions = computed(() => {
    const list: { label: string; value: number | null; disabled?: boolean }[] = [
        { label: t('common.no_table'), value: null }
    ];
    
    props.tables.forEach((table: any) => {
        let label = `${table.name} (${table.capacity} seats)`;
        if (table.location) label += ` - ${table.location}`;
        
        const isOccupied = !table.is_available && (selectedOrder.value ? table.id !== selectedOrder.value.table_id : true);
        
        if (isOccupied) label += ` [${t('tables.occupied') || 'Occupied'}]`;
        
        list.push({
            label: label,
            value: table.id,
            disabled: isOccupied
        });
    });
    
    return list;
});

// Auto-refresh every 2 seconds to sync with kitchen
let refreshInterval: any;

onMounted(() => {
    // Refresh POS data every 2 seconds
    refreshInterval = setInterval(() => {
        // Don't refresh if any confirmed modal is open to avoid disrupting user
        if (!showUpdateOrderModal.value && !showOpenModal.value && !showCloseModal.value && 
            !showWithdrawModal.value && !showDepositModal.value && adjustmentMode.value === 'none') {
            
            // Also block refresh if user is typing in any input field (including inline edits)
            const activeTag = document.activeElement?.tagName;
            if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT') {
                return;
            }

            // Include 'flash' to ensure we clear any stale success messages from session
            router.reload({ only: ['orders', 'currentRegister', 'currentBalance', 'flash'] });
        }
    }, 2000); // 2 seconds
    
    console.log('✅ POS auto-refresh enabled (2 seconds)');
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        console.log('🛑 POS auto-refresh stopped');
    }
});
</script>

<style>
@media screen {
    .print-overlay {
        display: none;
    }
}

@media print {
    /* Hide everything in body except the print overlay */
    body > *:not(.print-overlay) {
        display: none !important;
    }
    
    /* Ensure print overlay is visible */
    .print-overlay {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        background: white;
        z-index: 9999;
    }

    /* Reset page margins */
    @page {
        margin: 0;
        size: auto;
    }
}
</style>
