<template>
    <MainLayout>
        <div class="flex flex-col md:flex-row h-[calc(100vh-64px)] overflow-hidden bg-gray-50">
            <!-- LEFT COLUMN: CART (Fixed Width on PC) - SWAPPED TO LEFT -->
            <div id="cart-section" class="w-full md:w-[400px] lg:w-[450px] bg-white flex flex-col h-full shadow-xl z-20 overflow-hidden relative border-gray-200 order-2 md:order-1 md:border-r">
                <!-- Customer & Type Header -->
                <div class="p-4 border-b border-gray-100 bg-white space-y-4 shrink-0">
                    <!-- Customer Section -->
                    <div v-if="selectedCustomer" class="bg-gray-50 rounded-xl p-4 border border-gray-200 relative group transition-all hover:border-purple-200">
                        <button @click="selectedCustomer = null; form.customer_phone = ''; form.customer_name = ''; form.customer_id = null;" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
                                {{ selectedCustomer.name?.charAt(0).toUpperCase() || '?' }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ selectedCustomer.name }}</h3>
                                <p class="text-xs text-gray-500">{{ selectedCustomer.phone }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4 mt-2">
                            <div class="text-center bg-white border border-gray-100 rounded-lg p-2 flex-1 shadow-sm">
                                <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $t('loyalty.points') }}</span>
                                <span class="block text-lg font-bold text-purple-600">{{ selectedCustomer.loyalty_points }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="space-y-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide px-1">{{ $t('orders.customer') }}</p>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <PhoneInput 
                                    v-model="form.customer_phone"
                                    :country="currentCountry"
                                    @blur="lookupCustomer"
                                    :placeholder="$t('orders.customer_phone') || 'Phone'"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex-1">
                                <Input 
                                    v-model="form.customer_name"
                                    type="text"
                                    :placeholder="$t('staff.name')"
                                    class="w-full"
                                />
                            </div>
                        </div>

                    </div>
                     <!-- DOB Field -->
                    <div class="animate-fade-in-up">
                         <Input 
                            v-model="form.customer_birth_date"
                            type="date"
                            :label="$t('customers.birth_date') || 'Date of Birth'"
                            :placeholder="$t('customers.birth_date') || 'Date of Birth'"
                            class="w-full"
                        />
                    </div>

                    <!-- Order Type & Delivery Info -->
                    <div class="space-y-4">
                        <div class="flex p-1 bg-gray-100 rounded-lg overflow-x-auto text-[10px] sm:text-xs">
                           <label class="flex-1 cursor-pointer min-w-[70px]">
                                <input type="radio" v-model="form.type" value="dine_in" class="sr-only peer">
                                <div class="text-center py-2 px-1 rounded-md font-bold text-gray-500 peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm transition-all whitespace-nowrap">
                                    {{ $t('kitchen.dine_in') }}
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer min-w-[70px]">
                                <input type="radio" v-model="form.type" value="delivery" class="sr-only peer">
                                <div class="text-center py-2 px-1 rounded-md font-bold text-gray-500 peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm transition-all whitespace-nowrap">
                                    {{ $t('orders.delivery') }}
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer min-w-[70px]">
                                <input type="radio" v-model="form.type" value="takeaway" class="sr-only peer">
                                <div class="text-center py-2 px-1 rounded-md font-bold text-gray-500 peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm transition-all whitespace-nowrap">
                                    {{ $t('kitchen.takeaway') }}
                                </div>
                            </label>
                        </div>

                        <div v-if="form.type === 'dine_in'" class="animate-fade-in-up">
                            <Select
                                v-model="form.table_id"
                                :options="tableOptions"
                                :placeholder="$t('orders.no_table_assigned')"
                                class="w-full"
                            />
                        </div>

                        <div v-if="form.type === 'delivery'" class="space-y-3 animate-fade-in-up">
                            <Select
                                v-model="form.delivery_provider"
                                :label="$t('orders.delivery_provider_label')"
                                :options="deliveryProviderOptions"
                                :placeholder="$t('orders.select_provider') || 'Select Provider'"
                                :error="form.errors.delivery_provider"
                                class="w-full"
                            />
                            <Input
                                v-model="form.delivery_order_id"
                                :label="$t('orders.external_order_id')"
                                placeholder="#12345"
                                :error="form.errors.delivery_order_id"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Cart List -->
                <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
                     <!-- Empty State -->
                     <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center opacity-60">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <p class="text-lg font-medium">{{ $t('orders.no_items') }}</p>
                        <p class="text-sm">{{ $t('orders.select_items_from_menu') }}</p>
                     </div>

                     <div v-else class="p-4 space-y-6">
                        <!-- Items -->
                        <div class="space-y-3">
                            <div 
                                v-for="(item, index) in cart" 
                                :key="index" 
                                class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm relative group hover:border-primary/30 transition-all"
                            >
                                <div class="flex justify-between items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-800 text-sm truncate">{{ item.name }}</span>
                                            <span v-if="selectedReward?.reward_type === 'free_item' && ((freeItems.some(i => i.id === item.id)) || (!freeItems.length && selectedReward.menu_item_id === item.id))" class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded uppercase font-bold tracking-wide">{{ $t('loyalty.free') }}</span>
                                        </div>
                                        
                                        <!-- Extras -->
                                        <div v-if="item.extras && item.extras.length > 0" class="text-xs text-gray-500 space-y-0.5 mb-1.5">
                                            <div v-for="(ex, i) in item.extras" :key="i" class="flex justify-between">
                                                <span>+ {{ ex.name }}</span>
                                                <span>{{ currencyCode }} {{ ex.price.toFixed(2) }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 mt-2">
                                            <div class="flex items-center bg-gray-50 rounded-lg p-0.5 border border-gray-200">
                                                <button @click="item.qty > 1 ? item.qty-- : cart.splice(index, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-red-500 text-lg leading-none pb-0.5">-</button>
                                                <span class="w-6 text-center font-bold text-sm text-gray-800">{{ item.qty }}</span>
                                                <button @click="item.qty++" :disabled="!canAddItem(item.id)" :class="!canAddItem(item.id) ? 'opacity-30 cursor-not-allowed' : 'hover:text-primary'" class="w-6 h-6 flex items-center justify-center text-gray-500 text-lg leading-none pb-0.5">+</button>
                                            </div>
                                            <span class="font-bold text-primary text-sm">{{ currencyCode }} {{ ((item.price + (item.extras?.reduce((sum, e) => sum + e.price, 0) || 0)) * item.qty).toFixed(2) }}</span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col gap-1">
                                        <button @click="cart.splice(index, 1)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button @click="openNotesModal(item)" :class="item.notes ? 'text-amber-500 bg-amber-50' : 'text-gray-300 hover:text-gray-600'" class="p-1.5 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="item.notes" class="mt-2 text-xs text-amber-700 bg-amber-50 px-2 py-1.5 rounded border border-amber-100 italic flex gap-1">
                                    <span>📝</span> {{ item.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Global Notes -->
                        <div>
                             <label class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1.5">{{ $t('orders.order_notes') }}</label>
                             <textarea 
                                v-model="form.notes"
                                rows="2"
                                class="w-full text-sm rounded-xl border-gray-200 bg-white focus:border-primary focus:ring-primary placeholder-gray-400"
                                :placeholder="$t('orders.instructions_placeholder')"
                            ></textarea>
                        </div>

                        <!-- Rewards Section -->
                        <div v-if="availableRewards.length > 0 && selectedCustomer" class="border-t border-dashed border-gray-200 pt-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3 flex justify-between items-center">
                                <span>{{ $t('loyalty.available_rewards') }}</span>
                                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-[10px]">{{ selectedCustomer.loyalty_points }} {{ $t('loyalty.pts') }}</span>
                            </h4>
                            <div class="space-y-2">
                                <div 
                                    v-for="reward in availableRewards" 
                                    :key="reward.id"
                                    @click="toggleReward(reward)"
                                    class="p-3 rounded-xl border transition-all cursor-pointer relative"
                                    :class="selectedReward?.id === reward.id ? 'border-purple-500 bg-purple-50' : (canRedeemReward(reward) ? 'border-gray-200 bg-white hover:border-purple-300' : 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed')"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-sm text-gray-800">{{ getLocaleName(reward.name) }}</p>
                                            <p class="text-xs text-gray-500">{{ reward.points_required }} {{ $t('loyalty.pts') }} • {{ getRewardTypeLabel(reward) }}</p>
                                        </div>
                                        <div v-if="selectedReward?.id === reward.id" class="text-purple-600 bg-white rounded-full p-0.5 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Footer -->
                <div class="bg-white border-t border-gray-100 p-4 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
                     <!-- Totals -->
                     <div class="space-y-1.5 mb-4 text-sm">
                        <div class="flex justify-between text-gray-500">
                             <span>{{ $t('common.subtotal') }}</span>
                             <span class="font-medium text-gray-700">{{ currencyCode }} {{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0" class="flex justify-between text-green-600">
                             <span>{{ $t('loyalty.discount') }}</span>
                             <span class="font-bold">-{{ currencyCode }} {{ discountAmount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                             <span>{{ $t('pos.tax') }} (5%)</span>
                             <span class="font-medium text-gray-700">{{ currencyCode }} {{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-end pt-2 border-t border-dashed border-gray-200 mt-2">
                             <span class="font-bold text-gray-800 text-lg">{{ $t('common.total') }}</span>
                             <span class="font-extrabold text-primary text-3xl lg:text-4xl">{{ currencyCode }} {{ total.toFixed(2) }}</span>
                        </div>
                     </div>

                     <!-- OTP (Compact) -->
                     <div v-if="selectedReward && !otpVerified" class="mb-4">
                          <div class="bg-purple-50 rounded-lg p-2 border border-purple-100 flex gap-2">
                               <input v-model="otpInput" type="text" maxlength="6" :placeholder="$t('orders.otp_placeholder') || 'OTP'" class="w-20 text-center rounded border-gray-200 text-sm p-1 font-mono uppercase focus:ring-purple-500 focus:border-purple-500" :class="{'border-red-500': otpError || form.errors.otp}">
                                <Button v-if="(!otpSent && otpTimer === 0) && otpInput.length < 6" size="sm" @click="requestOtp" class="flex-1 text-xs py-1 h-8">{{ $t('loyalty.send_code') }}</Button>
                                <Button v-else size="sm" @click="verifyOtp" class="flex-1 text-xs py-1 h-8" :disabled="otpInput.length !== 6">{{ $t('common.verify') }}</Button>
                          </div>
                          <p v-if="otpError || form.errors.otp" class="text-red-500 text-xs mt-1 px-1 font-medium">{{ otpError || form.errors.otp }}</p>
                     </div>

                     <!-- Submit -->
                     <div class="flex gap-3">
                         <Link :href="route('orders.index')" class="px-4 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold transition-colors">
                            {{ $t('common.cancel') }}
                         </Link>
                         <button 
                            @click="submitOrder" 
                            :disabled="cart.length === 0 || form.processing" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl py-3 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                        >
                            <span v-if="form.processing" class="animate-spin">⟳</span>
                            {{ $t('nav.orders_create') }}
                         </button>
                     </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: MENU (Flex Grow) - SWAPPED TO RIGHT -->
            <div class="flex-1 flex flex-col min-w-0 border-gray-200 overflow-hidden relative order-1 md:order-2">
                <!-- Top Header: Title & Stock Errors -->
                <div class="bg-white border-b border-gray-200 px-5 py-3 shadow-sm z-10 shrink-0 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <Link :href="route('orders.index')" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </Link>
                            <h1 class="text-xl font-bold text-gray-800">{{ $t('nav.orders_create') }} (POS/Delivery)</h1>
                        </div>
                        
                        <div class="relative w-full max-w-xs">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                             </div>
                             <input 
                                v-model="searchQuery"
                                type="text" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-primary focus:ring-primary sm:text-sm transition-colors" 
                                :placeholder="$t('common.search') + '...'"
                             >
                        </div>
                    </div>

                    <!-- Stock Error Display (Inlined Header) -->
                    <div v-if="form.errors.items" class="bg-red-50 border border-red-200 rounded-lg p-2 flex items-start gap-2 text-sm">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div class="flex-1">
                            <p class="font-bold text-red-800 text-xs">{{ $t('dashboard_page.low_stock') }}</p>
                            <ul class="list-disc list-inside text-xs text-red-700">
                                <li v-for="(error, idx) in (Array.isArray(form.errors.items) ? form.errors.items : [form.errors.items])" :key="idx">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Categories Tabs -->
                <div class="bg-white border-b border-gray-200 px-4 py-2 flex gap-2 overflow-x-auto custom-scrollbar shadow-sm shrink-0">
                    <a 
                        v-for="category in categoriesList" 
                        :key="category.id"
                        :href="'#cat-' + category.id"
                        class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-white hover:shadow-md border border-transparent hover:border-gray-200 text-sm font-bold text-gray-700 whitespace-nowrap transition-all flex-shrink-0"
                    >
                        {{ getLocaleName(category.name) }}
                    </a>
                </div>

                <!-- Menu Grid -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-gray-50/50 scroll-smooth">
                    <div v-for="category in categoriesList" :key="category.id" :id="'cat-' + category.id" class="mb-8 scroll-mt-24">
                        <h3 class="text-lg font-bold text-gray-800 mb-3 px-1 sticky top-0 bg-gray-50/95 backdrop-blur py-2 z-10 rounded-lg">{{ getLocaleName(category.name) }}</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <div 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    class="group flex flex-col bg-white border border-gray-200 hover:border-primary/50 hover:shadow-lg rounded-2xl overflow-hidden transition-all duration-300 relative h-full"
                                    :class="{'opacity-75': (!canAddItem(item.id) && getQty(item.id) === 0) || item.inventory_status?.sold_out}"
                                >
                                    <!-- Free Item Badge -->
                                    <div 
                                        v-if="selectedReward?.reward_type === 'free_item' && (selectedReward.menu_item_ids ? selectedReward.menu_item_ids.includes(item.id) : selectedReward.menu_item_id === item.id)" 
                                        class="absolute top-3 left-3 z-20 bg-green-500 text-white text-[10px] uppercase font-bold px-2 py-1 rounded-full shadow-sm"
                                    >
                                        {{ $t('loyalty.free') }}
                                    </div>

                                    <!-- Stock Warning Overlay -->
                                    <div 
                                        v-if="!canAddItem(item.id) || item.inventory_status?.sold_out" 
                                        class="absolute inset-x-0 top-0 z-10 w-full h-48 bg-gray-900/10 backdrop-blur-[1px] flex items-center justify-center pointer-events-none"
                                    >
                                        <span 
                                            class="text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm"
                                            :class="item.inventory_status?.sold_out ? 'bg-red-600' : 'bg-gray-900/80'"
                                        >
                                            {{ item.inventory_status?.sold_out ? $t('common.sold_out') : getStockMessage(item.id) }}
                                        </span>
                                    </div>

                                    <!-- Image Area -->
                                    <div class="relative w-full aspect-[4/3] bg-gray-50 overflow-hidden">
                                        <Carousel 
                                            v-if="item.images && item.images.length > 0" 
                                            :images="item.images" 
                                            heightClass="h-full" 
                                        />
                                        <div v-else-if="item.image" class="w-full h-full">
                                            <img 
                                                :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image" 
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                            />
                                        </div>
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>

                                    <!-- Content Area -->
                                    <div class="flex-1 p-3 flex flex-col">
                                        <div class="flex-1 mb-2">
                                            <h5 class="font-bold text-gray-900 line-clamp-1 text-sm leading-tight" :title="typeof item.name === 'string' ? item.name : getLocaleName(item.name)">
                                                {{ getLocaleName(item.name) }}
                                            </h5>
                                            <p class="text-sm font-bold text-primary mt-1">
                                                {{ currencyCode }} {{ item.price.toFixed(2) }}
                                            </p>
                                            <p v-if="item.description" class="text-[10px] text-gray-500 mt-1 line-clamp-2 leading-none" :title="item.description">
                                                {{ item.description }}
                                            </p>
                                        </div>

                                        <!-- Controls -->
                                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-dashed border-gray-100 dark:border-gray-700">
                                            <button 
                                                type="button"
                                                @click="removeItem(item)"
                                                :disabled="!getQty(item.id)"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg transition-all"
                                                :class="getQty(item.id) 
                                                    ? 'bg-red-50 text-red-500 hover:bg-red-100 hover:scale-105 active:scale-95' 
                                                    : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                            </button>
                                            
                                            <span class="font-bold text-gray-900 min-w-[1.2rem] text-center text-sm">
                                                {{ getQty(item.id) > 0 ? getQty(item.id) : 0 }}
                                            </span>

                                            <button 
                                                type="button"
                                                @click="addItem(item)"
                                                :disabled="!canAddItem(item.id) || item.inventory_status?.sold_out"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg transition-all relative group/btn"
                                                :class="canAddItem(item.id) && !item.inventory_status?.sold_out
                                                    ? 'bg-primary text-white hover:bg-primary-hover shadow-md shadow-primary/20 hover:scale-105 active:scale-95' 
                                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Notes Modal -->
        <Modal :show="showNotesModal" @close="closeNotesModal" :title="$t('orders.special_instructions')" size="md">
            <div class="space-y-4">
                <div v-if="editingCartItem">
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">{{ $t('common.item') }}:</p>
                        <p class="font-semibold text-gray-900">{{ editingCartItem.name }} × {{ editingCartItem.qty }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('orders.special_instructions') }}</label>
                        <textarea 
                            v-model="tempNotes"
                            rows="4"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3 px-4"
                            :placeholder="$t('orders.instructions_placeholder')"
                            @keydown.enter.meta="saveNotes"
                            @keydown.enter.ctrl="saveNotes"
                        ></textarea>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <Button type="button" variant="secondary" @click="closeNotesModal" class="flex-1">{{ $t('common.cancel') }}</Button>
                    <Button type="button" @click="saveNotes" class="flex-1">{{ $t('orders.save_note') }}</Button>
                </div>
            </div>
        </Modal>

        <!-- Customize Item Modal -->
        <Modal :show="showCustomizeModal" @close="showCustomizeModal = false" :title="customizingItem ? getLocaleName(customizingItem.name) : $t('menu.customize')" size="md">
            <div v-if="customizingItem" class="space-y-6">
                <!-- Meal Contents -->
                <div v-if="customizingItem.type === 'meal'" class="bg-blue-50 p-4 rounded-xl">
                    <h4 class="font-bold text-blue-900 mb-2 text-sm uppercase">{{ $t('menu.meal_includes') }}:</h4>
                    <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                        <li v-for="(bundle, idx) in customizingItem.bundles" :key="idx">
                             {{ bundle.quantity }}x {{ bundle.childItem ? getLocaleName(bundle.childItem.name) : getItemNameById(bundle.child_menu_item_id) }}
                        </li>
                        <li v-if="!customizingItem.bundles?.length">{{ $t('menu.no_options') }}</li>
                    </ul>
                </div>

                <!-- Extras -->
                <div v-if="customizingItem.extras && customizingItem.extras.length > 0">
                    <h4 class="font-bold text-gray-900 mb-3">{{ $t('menu.add_extras') }}</h4>
                    <div class="space-y-2">
                        <div 
                            v-for="extra in customizingItem.extras" 
                            :key="extra.id"
                            @click="toggleExtra(extra)"
                            class="flex items-center justify-between p-3 rounded-lg border-2 cursor-pointer transition-all"
                            :class="selectedExtras.some(e => e.id === extra.id) 
                                ? 'border-primary bg-primary/5' 
                                : 'border-gray-100 hover:border-gray-300'"
                        >
                            <span class="font-medium text-gray-700">
                                {{ getLocaleName(extra.name) }}
                                <span v-if="extra.ingredient_id" class="text-xs text-gray-400 block font-normal">
                                    {{ $t('menu.stock_available', { stock: props.ingredientStocks?.[extra.ingredient_id]?.current_stock || 'N/A' }) }} 
                                    ({{ $t('menu.required', { qty: extra.quantity }) }})
                                </span>
                            </span>
                            <span class="text-primary font-bold">+ {{ currencyCode }} {{ Number(extra.price).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 italic py-4">
                    {{ $t('menu.no_options') }}
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="button" variant="secondary" @click="showCustomizeModal = false" class="flex-1">{{ $t('common.cancel') }}</Button>
                    <Button type="button" @click="addCustomizedItem" class="flex-1" :disabled="selectedExtras.length > 0 && !canAddCustomizedItem">{{ $t('menu.add_to_order') }}</Button>
                </div>
            </div>
        </Modal>

        <!-- Order Success Modal -->
        <Modal :show="showOrderSuccessModal" @close="showOrderSuccessModal = false" :title="$t('common.success') || 'Success'" size="sm">
            <div class="text-center space-y-4 py-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ successMessage }}</h3>
                <p class="text-sm text-gray-500">{{ $t('orders.ready_for_next') || 'System is ready for the next order.' }}</p>
                <div class="pt-4">
                    <Button @click="showOrderSuccessModal = false" class="w-full">{{ $t('common.ok') || 'OK' }}</Button>
                </div>
            </div>
        </Modal>

        <!-- Mobile Cart Summary (Fixed Bottom) -->
        <div v-if="cart.length > 0" class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50 safe-area-bottom">
            <button @click="scrollToCart" class="w-full bg-primary text-white font-bold py-3 rounded-xl flex justify-between px-6 shadow-lg active:scale-95 transition-transform">
                <span>{{ cart.reduce((s,i)=>s+i.qty,0) }} {{ $t('common.items') }}</span>
                <span>{{ $t('orders.view_cart') }}</span>
                <span>{{ currencyCode }} {{ total.toFixed(2) }}</span>
            </button>
        </div>

    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Carousel from '@/Components/Carousel.vue';
import Modal from '@/Components/Modal.vue';
import Select from '@/Components/Select.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import axios from 'axios';

interface MenuItem {
    id: number;
    name: Record<string, string> | string;
    description?: string;
    price: number;
    image?: string;
    images?: string[];
    inventory_status?: { sold_out: boolean; low_stock: boolean; missing_ingredients: string[]; };
    recipe?: { ingredient_id: string; quantity: number }[];
    type?: 'item' | 'meal';
    extras?: { id: number; name: any; price: string; quantity: number; ingredient_id: number }[];
    bundles?: { child_menu_item_id: number; quantity: number; childItem?: MenuItem }[];
}

interface Category {
    id: number;
    name: Record<string, string> | string;
    items: MenuItem[];
}

interface CartItem {
    id: number;
    name: string;
    price: number;
    qty: number;
    notes?: string;
    recipe?: { ingredient_id: string; quantity: number }[];
    type?: 'item' | 'meal';
    extras?: { id?: number; name: string; price: number; ingredient_id: number; quantity: number }[];
}

interface Customer {
    id: number;
    name: string | null;
    phone: string;
    email: string | null;
    loyalty_points: number;
    birth_date?: string | null;
}

interface Reward {
    id: number;
    name: Record<string, string> | string;
    description: string | null;
    points_required: number;
    reward_type: 'discount_percentage' | 'discount_fixed' | 'free_item' | 'cashback';
    discount_value: number | null;
    menu_item_id?: number | null;
    menu_item_ids?: number[];
    min_order_value?: number | null;
}

const props = withDefaults(defineProps<{
    menuCategories?: Category[];
    customers?: Customer[];
    rewards?: Reward[];
    tables?: any[]; // Tables are just kept as optional
    currency?: string;
    stockAvailability?: Record<number, { max_quantity: number; available: boolean; is_tracked?: boolean }>;
    ingredientStocks?: Record<string, { current_stock: number; name: string }>;
    deliveryProviders?: any[];
}>(), {
    menuCategories: () => [],
    customers: () => [],
    rewards: () => [],
    tables: () => [],
    currency: 'AED',
    stockAvailability: () => ({}),
    ingredientStocks: () => ({}),
    deliveryProviders: () => []
});

const { locale, t } = useI18n();
const route = (window as any).route;
const page = usePage();

// Computed
const searchQuery = ref('');

const categoriesList = computed(() => {
    const all = props.menuCategories || [];
    if (!searchQuery.value) return all;

    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return all;

    return all.map(cat => {
        const items = cat.items.filter(item => {
            let name = '';
            if (typeof item.name === 'string') name = item.name;
            else name = item.name[locale.value] || Object.values(item.name)[0] || '';
            return name.toLowerCase().includes(query);
        });
        return { ...cat, items };
    }).filter(cat => cat.items.length > 0);
});
const currencyCode = computed(() => (page.props.current_restaurant as any)?.currency || props.currency || 'AED');
const currentCountry = computed(() => (page.props.current_restaurant as any)?.country || 'United Arab Emirates');
const availableRewards = computed(() => props.rewards || []);
const tablesList = computed(() => props.tables || []);

const tableOptions = computed(() => {
    const opts: { label: string; value: number | null; disabled?: boolean }[] = [
        { label: t('orders.no_table_assigned'), value: null }
    ];
    
    tablesList.value.forEach((table: any) => {
        let label = `${table.name} (${table.capacity} ${t('common.guest')})`;
        if (table.location) label += ` - ${table.location}`;
        if (!table.is_available) label += ` [${t('common.sold_out')}]`; // Assuming is_available handled
        
        opts.push({
            label: label,
            value: table.id,
            disabled: !table.is_available
        });
    });
    
    return opts;
});

const deliveryProviderOptions = computed(() => {
    const opts = [{ label: 'Internal Delivery (توصيل داخلي)', value: 'Internal' }];
    if (props.deliveryProviders && props.deliveryProviders.length > 0) {
        props.deliveryProviders.forEach(p => opts.push({ label: p.name, value: p.name }));
    } else {
        opts.push(
            { label: 'Talabat (طلبات)', value: 'Talabat' },
            { label: 'Uber Eats (أوبر إيتس)', value: 'Uber Eats' },
            { label: 'Deliveroo (ديليفيرو)', value: 'Deliveroo' },
            { label: 'Careem Food (كريم فود)', value: 'Careem Food' },
            { label: 'Noon Food (نون فود)', value: 'Noon Food' },
            { label: 'Zomato (زوماتو)', value: 'Zomato' },
            { label: 'HungerStation (هنقرستيشن)', value: 'HungerStation' },
            { label: 'Jahez (جاهز)', value: 'Jahez' },
            { label: 'Keeta (كيتا)', value: 'Keeta' }
        );
    }
    return opts;
});

// State
const cart = ref<CartItem[]>([]);
const selectedCustomer = ref<Customer | null>(null);
const selectedReward = ref<Reward | null>(null);
const showNotesModal = ref(false);
const editingCartItem = ref<CartItem | null>(null);
const tempNotes = ref('');
const showCustomizeModal = ref(false);
const customizingItem = ref<MenuItem | null>(null);
const selectedExtras = ref<any[]>([]);
const showOrderSuccessModal = ref(false);
const successMessage = ref('');

// Form
const form = useForm({
    customer_phone: '',
    customer_name: '',
    customer_birth_date: '',
    customer_id: null as number | null,
    type: 'delivery',
    table_id: null as number | null,
    delivery_provider: '',
    delivery_order_id: '',
    items: [] as any[],
    subtotal: 0,
    discount_amount: 0,
    tax: 0,
    total: 0,
    notes: '',
    reward_id: null as number | null,
    otp: ''
});

// OTP State
const otpInput = ref('');
const otpSent = ref(false);
const otpTimer = ref(0);
let timerInterval: any = null;
const otpVerified = ref(false);
const otpError = ref('');

const requestOtp = async () => {
    if (!selectedCustomer.value) return;
    try {
        otpError.value = '';
        await axios.post(route('loyalty.customers.request-otp', selectedCustomer.value.id));
        otpSent.value = true;
        startOtpTimer();
    } catch (error: any) {
        otpError.value = error.response?.data?.message || t('loyalty.otp_send_failed') || 'Failed to send OTP';
        otpSent.value = false;
    }
};

const verifyOtp = async () => {
    if (!selectedCustomer.value || otpInput.value.length !== 6) return;
    try {
        otpError.value = '';
        await axios.post(route('loyalty.customers.verify-otp-only', selectedCustomer.value.id), { otp: otpInput.value });
        otpVerified.value = true;
        form.otp = otpInput.value;
    } catch (error: any) {
        otpVerified.value = false;
        otpError.value = error.response?.data?.message || t('loyalty.invalid_otp') || 'Invalid OTP';
    }
};

const startOtpTimer = () => {
    otpTimer.value = 60; 
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        otpTimer.value--;
        if (otpTimer.value <= 0) clearInterval(timerInterval);
    }, 1000);
};

// Customer lookup
const lookupCustomer = () => {
    if (!form.customer_phone) {
        selectedCustomer.value = null;
        return;
    }
    const searchText = form.customer_phone;
    const customer = props.customers?.find(c => (c.phone && c.phone.replace(/\D/g, '') === searchText.replace(/\D/g, '')));
    if (customer) {
        selectedCustomer.value = customer;
        form.customer_name = customer.name || '';
        form.customer_birth_date = customer.birth_date || '';
        form.customer_id = customer.id;
    } else {
        selectedCustomer.value = null;
        form.customer_id = null;
    }
};

watch(() => form.customer_phone, (newVal) => {
    if (newVal && newVal.length >= 7) lookupCustomer();
    else selectedCustomer.value = null;
});

// Helpers
const getLocaleName = (name: Record<string, string> | string): string => {
    if (typeof name === 'string') return name;
    return name[locale.value] || Object.values(name)[0] || '';
};

const getItemNameById = (id: number): string => {
    for (const cat of categoriesList.value) {
        const item = cat.items.find((i: MenuItem) => i.id === id);
        if (item) return getLocaleName(item.name);
    }
    return t('common.unknown_item');
};

const getQty = (itemId: number): number => {
    return cart.value.filter(i => i.id === itemId).reduce((sum, i) => sum + i.qty, 0);
};

const addItem = (item: MenuItem) => {
    if ((item.extras && item.extras.length > 0) || item.type === 'meal') {
        openCustomizeModal(item);
        return;
    }
    const existing = cart.value.find(i => i.id === item.id && (!i.extras || i.extras.length === 0));
    if (existing) existing.qty++;
    else {
        cart.value.push({
            id: item.id,
            name: getLocaleName(item.name),
            price: item.price,
            qty: 1,
            recipe: item.recipe,
            type: item.type || 'item',
            extras: []
        });
    }
};

const openCustomizeModal = (item: MenuItem) => {
    customizingItem.value = item;
    selectedExtras.value = [];
    showCustomizeModal.value = true;
};

const addCustomizedItem = () => {
    if (!customizingItem.value) return;
    const item = customizingItem.value;
    const newExtras = selectedExtras.value.map(e => ({
        id: e.id,
        name: getLocaleName(e.name),
        price: Number(e.price),
        ingredient_id: e.ingredient_id,
        quantity: e.quantity
    }));

    const existingIndex = cart.value.findIndex(cartItem => {
        if (cartItem.id !== item.id) return false;
        const cartExtras = cartItem.extras || [];
        if (cartExtras.length !== newExtras.length) return false;
        const cartIds = cartExtras.map(e => e.id).sort();
        const newIds = newExtras.map(e => e.id).sort();
        return cartIds.every((id, index) => id === newIds[index]);
    });

    if (existingIndex !== -1) cart.value[existingIndex].qty++;
    else {
        cart.value.push({
            id: item.id,
            name: getLocaleName(item.name),
            price: item.price,
            qty: 1,
            recipe: item.recipe,
            type: item.type || 'item',
            extras: newExtras
        });
    }
    showCustomizeModal.value = false;
    customizingItem.value = null;
    selectedExtras.value = [];
};

const toggleExtra = (extra: any) => {
    const idx = selectedExtras.value.findIndex(e => e.id === extra.id);
    if (idx > -1) selectedExtras.value.splice(idx, 1);
    else {
        if (extra.ingredient_id && extra.quantity > 0) {
             const stock = props.ingredientStocks?.[extra.ingredient_id];
             if (stock && stock.current_stock < extra.quantity) {
                 alert(t('common.insufficient_stock', { item: getLocaleName(extra.name), qty: extra.quantity, stock: stock.current_stock }));
                 return;
             }
        }
        selectedExtras.value.push(extra);
    }
};

const canAddCustomizedItem = computed(() => true);

const removeItem = (item: { id: number }) => {
    const idx = cart.value.findIndex(i => i.id === item.id);
    if (idx !== -1) {
        if (cart.value[idx].qty > 1) cart.value[idx].qty--;
        else cart.value.splice(idx, 1);
    }
};

const canAddItem = (itemId: number): boolean => {
    const itemInMenu = categoriesList.value.flatMap(c => c.items).find(i => i.id === itemId);
    if (itemInMenu?.inventory_status?.sold_out) return false;
    if (props.ingredientStocks && Object.keys(props.ingredientStocks).length > 0) {
        const projectedUsage: Record<string, number> = {};
        cart.value.forEach(cartItem => {
            if (cartItem.recipe) {
                cartItem.recipe.forEach(comp => {
                    projectedUsage[comp.ingredient_id] = (projectedUsage[comp.ingredient_id] || 0) + (comp.quantity * cartItem.qty);
                });
            }
        });
        const recipe = itemInMenu?.recipe;
        if (recipe) {
            recipe.forEach(comp => {
                projectedUsage[comp.ingredient_id] = (projectedUsage[comp.ingredient_id] || 0) + comp.quantity;
            });
        }
        for (const [ingId, requiredQty] of Object.entries(projectedUsage)) {
            const stockInfo = props.ingredientStocks[ingId];
            if (stockInfo && requiredQty > stockInfo.current_stock) return false;
        }
    }
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return true;
    const currentQty = getQty(itemId);
    return currentQty < stockInfo.max_quantity;
};

const getStockMessage = (itemId: number): string => {
    if (!canAddItem(itemId)) return t('common.sold_out');
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo || stockInfo.is_tracked === false) return '';
    const currentQty = getQty(itemId);
    const remaining = stockInfo.max_quantity - currentQty;
    if (!stockInfo.available || remaining <= 0) return t('common.sold_out');
    if (remaining <= 3) return t('menu.stock_available', { stock: remaining });
    return t('menu.stock_available', { stock: stockInfo.max_quantity });
};

// Reward helpers
const canRedeemReward = (reward: Reward): boolean => {
    if (!selectedCustomer.value) return false;
    if (selectedCustomer.value.loyalty_points < reward.points_required) return false;
    if (reward.min_order_value && subtotal.value < reward.min_order_value) return false;
    if (reward.menu_item_ids && reward.menu_item_ids.length > 0 && reward.reward_type !== 'free_item') {
        const hasItem = cart.value.some(i => reward.menu_item_ids!.includes(i.id));
        if (!hasItem) return false;
    }
    return true;
};

const toggleReward = (reward: Reward) => {
    if (!canRedeemReward(reward)) return;
    if (selectedReward.value?.id === reward.id) {
        selectedReward.value = null;
    } else {
        selectedReward.value = reward;
        otpVerified.value = false;
        otpSent.value = false;
        otpInput.value = '';
        otpError.value = '';
        if (reward.reward_type === 'free_item') {
             const targetIds = (reward.menu_item_ids && reward.menu_item_ids.length) 
                ? reward.menu_item_ids 
                : (reward.menu_item_id ? [reward.menu_item_id] : []);
             if (targetIds.length > 0) {
                 targetIds.forEach(targetId => {
                     const inCart = cart.value.some(i => i.id === targetId);
                     if (!inCart) {
                         for (const cat of categoriesList.value) {
                             const item = cat.items.find((i: MenuItem) => i.id === targetId);
                             if (item) { addItem(item); break; }
                         }
                     }
                 });
             }
        }
    }
};

const getRewardTypeLabel = (reward: Reward): string => {
    if (reward.description) return reward.description;
    const value = Math.round(reward.discount_value || 0);
    switch (reward.reward_type) {
        case 'discount_percentage': return t('loyalty.discount_percentage_off', { value });
        case 'discount_fixed': return t('loyalty.discount_fixed_off', { amount: currencyCode.value + ' ' + value });
        case 'free_item': return t('loyalty.free_item');
        case 'cashback': return t('loyalty.cashback_back', { value: currencyCode.value + ' ' + value });
        default: return '';
    }
};



// Calculations
const freeItems = computed(() => {
    if (selectedReward.value?.reward_type !== 'free_item') return [];
    const targetIds = (selectedReward.value.menu_item_ids && selectedReward.value.menu_item_ids.length) 
        ? selectedReward.value.menu_item_ids 
        : (selectedReward.value.menu_item_id ? [selectedReward.value.menu_item_id] : []);
    if (!targetIds.length) return [];
    return cart.value.filter(i => targetIds.includes(i.id));
});

const subtotal = computed(() => 
    cart.value.reduce((sum, item) => {
        const itemTotal = (Number(item.price) + (item.extras?.reduce((s, e) => s + Number(e.price), 0) || 0)) * item.qty;
        return sum + itemTotal;
    }, 0)
);

const discountAmount = computed(() => {
    if (!selectedReward.value) return 0;
    switch (selectedReward.value.reward_type) {
        case 'discount_percentage':
            if (selectedReward.value.menu_item_ids?.length) {
                const eligibleTotal = cart.value
                    .filter(i => selectedReward.value!.menu_item_ids!.includes(i.id))
                    .reduce((sum, i) => {
                         const itemTotal = (Number(i.price) + (i.extras?.reduce((s, e) => s + Number(e.price), 0) || 0)) * i.qty;
                         return sum + itemTotal;
                    }, 0);
                return eligibleTotal * ((selectedReward.value.discount_value || 0) / 100);
            }
            return subtotal.value * ((selectedReward.value.discount_value || 0) / 100);
        case 'discount_fixed':
            return Math.min(selectedReward.value.discount_value || 0, subtotal.value);
        case 'free_item':
            return freeItems.value.reduce((sum, item) => sum + item.price, 0);
        default:
            return 0;
    }
});

const afterDiscount = computed(() => Math.max(0, subtotal.value - discountAmount.value));
const tax = computed(() => afterDiscount.value * 0.05);
const total = computed(() => afterDiscount.value + tax.value);

// Notes Modal
const openNotesModal = (item: CartItem) => {
    editingCartItem.value = item;
    tempNotes.value = item.notes || '';
    showNotesModal.value = true;
};
const closeNotesModal = () => {
    showNotesModal.value = false;
    editingCartItem.value = null;
    tempNotes.value = '';
};
const saveNotes = () => {
    if (editingCartItem.value) editingCartItem.value.notes = tempNotes.value.trim() || undefined;
    closeNotesModal();
};

const submitOrder = () => {
    form.customer_id = selectedCustomer.value?.id || null;
    if (selectedReward.value && !otpVerified.value) {
        alert(t('loyalty.verify_otp_required'));
        return;
    }

    form.items = cart.value.map(item => ({
        menu_item_id: item.id,
        quantity: item.qty,
        unit_price: item.price,
        notes: item.notes,
        extras: item.extras
    }));
    form.subtotal = subtotal.value;
    form.discount_amount = discountAmount.value;
    form.tax = tax.value;
    form.total = total.value;
    form.reward_id = selectedReward.value?.id || null;

    form.post(route('pos-orders.store'), {
        onSuccess: () => {
             // Get success message from flash or fallback
            const flash = (page.props as any).flash;
            successMessage.value = flash?.message || t('orders.order_created') || 'Order Created Successfully';
            console.log('Delivery order created successfully, showing modal.');
            showOrderSuccessModal.value = true;

            // Reset state to stay on the same page and allow for the next order
            cart.value = [];
            selectedCustomer.value = null;
            selectedReward.value = null;
            otpVerified.value = false;
            otpSent.value = false;
            otpInput.value = '';
            form.reset();
        },
        onError: (errors) => {
            console.error('Delivery order creation failed with errors:', errors);
            form.processing = false;
            
            const errorMessage = Object.values(errors)[0] || t('common.please_correct') || 'Validation failed. Please check the form.';
            alert(errorMessage);
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};

const scrollToCart = () => {
    document.getElementById('cart-section')?.scrollIntoView({ behavior: 'smooth' });
};
</script>
