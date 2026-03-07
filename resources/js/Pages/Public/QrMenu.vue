<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header -->
        <div class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ restaurant.name }}</h1>
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $t('common.table') }}: {{ table.name }}
                        </p>
                    </div>
                    <button 
                        v-if="cart.length > 0"
                        @click="showCart = true" 
                        class="relative bg-primary text-white px-5 py-3 rounded-xl hover:bg-primary-hover transition-all shadow-lg shadow-primary/30 hover:scale-105"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-semibold">{{ cartItemCount }}</span>
                        </div>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                            {{ cartItemCount }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
            <div v-if="categories.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-gray-500 text-lg">No menu items available at the moment.</p>
            </div>

            <div v-for="category in categories" :key="category.id" class="mb-8">
                <h2 class="text-lg font-bold text-gray-500 uppercase tracking-wide mb-4 sticky top-20 bg-gradient-to-br from-gray-50 to-gray-100 py-2 z-10">
                    {{ getTranslatedName(category.name) }}
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in category.items" 
                        :key="item.id"
                        class="group flex flex-col bg-white border border-gray-100 hover:border-primary/50 hover:shadow-lg rounded-2xl overflow-hidden transition-all duration-300 relative"
                    >
                        <!-- Item Image -->
                        <!-- Item Image -->
                        <div class="relative w-full aspect-[4/3] bg-gray-50 overflow-hidden">
                            <Carousel 
                                v-if="item.images && item.images.length > 0" 
                                :images="item.images" 
                                heightClass="h-full" 
                            />
                            <div v-else-if="item.image" class="w-full h-full">
                                <img 
                                    :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image" 
                                    :alt="getTranslatedName(item.name)"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                            </div>
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content Area -->
                        <div class="flex-1 p-4 flex flex-col">
                            <div class="flex-1 mb-2">
                                <h5 class="font-bold text-gray-900 line-clamp-1" :title="getTranslatedName(item.name)">
                                    {{ getTranslatedName(item.name) }}
                                </h5>
                                <p class="text-sm font-medium text-primary mt-1">
                                    {{ restaurant.currency }} {{ item.price.toFixed(2) }}
                                </p>
                                <p v-if="getStockLabel(item.id)" class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full w-fit mt-1 uppercase tracking-wider">
                                    {{ getStockLabel(item.id) }}
                                </p>
                                <p v-if="item.description" class="text-xs text-gray-500 mt-1 line-clamp-2" :title="item.description">
                                    {{ item.description }}
                                </p>
                            </div>

                            <!-- Controls -->
                            <div class="flex items-center justify-between mt-auto pt-3 border-t border-dashed border-gray-100">
                                <template v-if="isItemAvailable(item.id)">
                                    <button 
                                        type="button"
                                        @click="removeItem(item)"
                                        :disabled="!getQty(item.id)"
                                        class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                        :class="getQty(item.id) 
                                            ? 'bg-red-50 text-red-500 hover:bg-red-100 hover:scale-105 active:scale-95' 
                                            : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    
                                    <span class="font-bold text-gray-900 min-w-[1.5rem] text-center">
                                        {{ getQty(item.id) > 0 ? getQty(item.id) : 0 }}
                                    </span>

                                    <button 
                                        type="button"
                                        @click="addItem(item)"
                                        class="w-8 h-8 flex items-center justify-center rounded-xl transition-all bg-primary text-white hover:bg-primary-hover shadow-md shadow-primary/20 hover:scale-105 active:scale-95"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </template>
                                <div v-else class="flex-1 text-center py-2 bg-gray-100 rounded-xl text-gray-400 font-bold text-sm">
                                    {{ $t('common.sold_out') || 'Sold Out' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Cart Button (Mobile) -->
        <div v-if="cart.length > 0 && !showCart" class="fixed bottom-6 right-6 z-40 md:hidden">
            <button 
                @click="showCart = true"
                class="bg-primary text-white p-4 rounded-full shadow-2xl hover:bg-primary-hover transition-all hover:scale-110 relative"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                    {{ cartItemCount }}
                </span>
            </button>
        </div>

        <!-- Cart Modal -->
        <div v-if="showCart" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center transition-all duration-300">
            <div 
                class="bg-white w-full sm:max-w-xl sm:rounded-3xl rounded-t-3xl max-h-[90vh] flex flex-col shadow-2xl transform transition-transform duration-300"
                :class="{'translate-y-0': showCart, 'translate-y-full': !showCart}"
            >
                <!-- Cart Header -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white rounded-t-3xl sticky top-0 z-10">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 p-2.5 rounded-xl">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $t('qr_menu.current_order') }}</h2>
                            <p class="text-sm text-gray-500">{{ $t('qr_menu.items_count', { count: cartItemCount }) }}</p>
                        </div>
                    </div>
                    <button 
                        @click="showCart = false" 
                        class="p-2.5 bg-gray-50 hover:bg-gray-100 rounded-full text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 bg-gray-50/50">
                    <div v-if="cart.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $t('qr_menu.your_cart_is_empty') }}</h3>
                        <p class="text-gray-500 max-w-xs">{{ $t('qr_menu.looks_like_you_havent') }}</p>
                        <button 
                            @click="showCart = false"
                            class="mt-6 px-6 py-2.5 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            {{ $t('qr_menu.browse_menu') }}
                        </button>
                    </div>

                    <div v-else class="space-y-4">
                        <div 
                            v-for="(item, index) in cart" 
                            :key="index"
                            class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
                        >
                            <div class="flex justify-between items-start gap-4 mb-4">
                                <div>
                                    <div class="flex flex-col">
                                        <h3 class="font-bold text-gray-900 text-lg mb-0.5">{{ item.name }}</h3>
                                        <!-- Show extras if any -->
                                        <div v-if="item.extras && item.extras.length > 0" class="text-xs text-blue-600 mb-1">
                                             <span v-for="(ex, idx) in item.extras" :key="idx">
                                                 + {{ ex.name }} ({{ restaurant.currency }} {{ Number(ex.price).toFixed(2) }})<span v-if="(idx as any) < (item.extras.length - 1)">, </span>
                                             </span>
                                        </div>
                                        <p class="text-primary font-bold">{{ restaurant.currency }} {{ ((Number(item.price) + (item.extras || []).reduce((s: number, e: any) => s + Number(e.price), 0)) * item.quantity).toFixed(2) }}</p>
                                    </div>
                                </div>
                                <button 
                                    @click="removeFromCart(index)"
                                    class="text-gray-400 hover:text-red-500 p-1.5 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Remove item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-end justify-between gap-4">
                                <!-- Notes Input -->
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <input 
                                            v-model="item.notes"
                                            type="text"
                                            :placeholder="$t('qr_menu.add_notes')"
                                            class="w-full pl-9 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all placeholder-gray-400"
                                        />
                                    </div>
                                </div>

                                <!-- Qty Controls -->
                                <div class="flex items-center bg-gray-50 rounded-xl p-1 border border-gray-100">
                                    <button 
                                        @click="decrementQuantity(index)"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-gray-600 shadow-sm hover:text-primary active:scale-95 transition-all disabled:opacity-50"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="w-10 text-center font-bold text-gray-900">{{ item.quantity }}</span>
                                    <button 
                                        @click="incrementQuantity(index)"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-white shadow-sm shadow-primary/30 hover:bg-primary-hover active:scale-95 transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info Card -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $t('qr_menu.your_details') }}
                                <span class="text-xs font-normal text-gray-400 ms-auto">({{ $t('common.optional') }})</span>
                            </h3>

                            <!-- Name field — full width -->
                            <input
                                v-model="customerName"
                                type="text"
                                :placeholder="$t('qr_menu.name')"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                            />

                            <!-- Phone + Check Loyalty — full width -->
                            <div class="flex gap-2">
                                <PhoneInput
                                    v-model="customerPhone"
                                    :country="restaurant.country || 'United Arab Emirates'"
                                    placeholder="Phone"
                                    class="flex-1 min-w-0"
                                    @input="resetLoyalty"
                                />
                                <button
                                    @click="checkLoyalty"
                                    :disabled="!customerPhone || checkingLoyalty"
                                    type="button"
                                    class="shrink-0 bg-primary/10 text-primary px-4 py-3 rounded-xl font-bold hover:bg-primary/20 transition-all disabled:opacity-50 flex items-center justify-center whitespace-nowrap text-sm"
                                >
                                    <svg v-if="checkingLoyalty" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-else>{{ $t('qr_menu.check_loyalty') }}</span>
                                </button>
                            </div>

                            <!-- No loyalty found message -->
                            <div v-if="loyaltyChecked && !loyaltyFound" class="text-sm text-gray-500 italic flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $t('qr_menu.no_loyalty_found') }}
                            </div>

                            <!-- Loyalty Data -->
                            <div v-if="loyaltyFound && customerLoyaltyData" class="p-4 bg-purple-50 rounded-xl border border-purple-100 transition-all">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-bold text-purple-900">{{ $t('qr_menu.welcome_user', { name: customerLoyaltyData.name }) }}</span>
                                    <span class="text-sm font-semibold text-purple-700 bg-purple-200 px-3 py-1 rounded-lg">{{ customerLoyaltyData.points }} pts</span>
                                </div>

                                <div v-if="availableRewards.length > 0" class="space-y-2">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('qr_menu.available_rewards') }}</p>
                                    <div v-for="reward in availableRewards" :key="reward.id"
                                        @click="selectedReward = selectedReward?.id === reward.id ? null : reward"
                                        class="flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="selectedReward?.id === reward.id ? 'border-primary bg-primary/5 shadow-inner' : 'border-purple-200/50 hover:border-primary/30 bg-white shadow-sm'">
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">{{ getTranslatedName(reward.name) }}</p>
                                            <p class="text-xs text-gray-500 font-medium">{{ $t('qr_menu.pts_required', { pts: reward.points_required }) }}</p>
                                        </div>
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                            :class="selectedReward?.id === reward.id ? 'border-primary bg-primary' : 'border-gray-300'">
                                            <svg v-if="selectedReward?.id === reward.id" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500 italic text-center py-3 bg-white/50 rounded-lg">
                                    {{ $t('qr_menu.keep_ordering') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div v-if="cart.length > 0" class="bg-white border-t border-gray-100 p-6 rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.05)] z-20">
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center text-gray-500 text-sm">
                            <span>{{ $t('qr_menu.subtotal') }}</span>
                            <span>{{ restaurant.currency }} {{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-500 text-sm">
                            <span>{{ $t('qr_menu.tax_5') }}</span>
                            <span>{{ restaurant.currency }} {{ tax.toFixed(2) }}</span>
                        </div>
                        <div v-if="selectedReward" class="flex justify-between items-center text-primary text-sm font-bold">
                            <span>{{ $t('qr_menu.reward_discount') }}</span>
                            <span>- {{ restaurant.currency }} {{ calculateDiscount().toFixed(2) }}</span>
                        </div>
                        <div class="pt-3 flex justify-between items-center border-t border-dashed border-gray-200">
                            <span class="font-bold text-gray-900 text-lg">{{ $t('common.total') }}</span>
                            <span class="font-bold text-primary text-2xl">{{ restaurant.currency }} {{ finalTotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <button 
                        @click="initiateOrder"
                        :disabled="placing || sendingOtp"
                        class="w-full bg-primary hover:bg-primary-hover disabled:opacity-75 disabled:cursor-not-allowed text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-primary/30 flex items-center justify-center gap-3 transition-all active:scale-[0.98]"
                    >
                        <svg v-if="placing || sendingOtp" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="sendingOtp">{{ $t('qr_menu.preparing_discount') }}</span>
                        <span v-else-if="placing">{{ $t('qr_menu.placing_order') }}</span>
                        <div v-else class="flex items-center gap-2">
                            <span>{{ $t('qr_menu.place_order') }}</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </div>
                    </button>

                </div>
            </div>
        </div>

        <!-- Order Confirmation Modal -->
        <div v-if="showConfirmation" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('orders.order_placed') }}</h2>
                <p class="text-gray-600 mb-6">{{ $t('orders.order_sent_to_kitchen') }}</p>
                <div class="bg-gradient-to-r from-primary/10 to-purple-100 rounded-xl p-6 mb-6">
                    <p class="text-sm text-gray-600 mb-1">{{ $t('qr_menu.order_number') }}</p>
                    <p class="text-3xl font-bold text-primary">{{ orderNumber }}</p>
                </div>
                <button 
                    @click="closeConfirmation"
                    class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-hover transition-colors shadow-lg shadow-primary/30"
                >
                    {{ $t('orders.continue_browsing') }}
                </button>
            </div>
        </div>
    </div>

        <!-- Loyalty OTP Verification Modal -->
        <Modal :show="showOtpModal" @close="closeOtpModal" title="Verify Loyalty Redemption" size="sm">
            <div class="space-y-6 text-center">
                <div class="mx-auto w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Enter Verification Code</h3>
                    <p class="text-sm text-gray-500">We've sent a 6-digit OTP to <span class="font-bold">{{ customerPhone }}</span></p>
                </div>
                
                <div class="px-4">
                    <input 
                        v-model="otpValue"
                        type="text"
                        maxlength="6"
                        placeholder="••••••"
                        class="w-full text-center text-3xl tracking-widest font-mono py-4 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    />
                    <p v-if="otpError" class="text-red-500 text-sm mt-2 font-medium">{{ otpError }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="closeOtpModal" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="button" @click="confirmOtpAndOrder" :disabled="placing || otpValue.length !== 6" class="flex-1 bg-primary disabled:opacity-50 text-white py-3 rounded-xl font-bold hover:bg-primary-hover transition-colors shadow-lg shadow-primary/30 flex justify-center items-center">
                        <svg v-if="placing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Verify & Place Order</span>
                    </button>
                </div>
            </div>
        </Modal>
        <!-- Customize Item Modal -->
        <Modal :show="showCustomizeModal" @close="showCustomizeModal = false" :title="customizingItem ? getTranslatedName(customizingItem.name) : 'Customize'" size="md">
            <div v-if="customizingItem" class="space-y-6">
                 <!-- Meal Contents -->
                 <div v-if="customizingItem.type === 'meal'" class="bg-blue-50 p-4 rounded-xl">
                    <h4 class="font-bold text-blue-900 mb-2 text-sm uppercase">Meal Includes:</h4>
                    <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                        <li v-for="(bundle, idx) in customizingItem.bundles" :key="idx">
                             {{ bundle.quantity }}x {{ bundle.childItem ? getTranslatedName(bundle.childItem.name) : 'Item #'+bundle.child_menu_item_id }}
                        </li>
                    </ul>
                </div>

                <!-- Extras -->
                <div v-if="customizingItem.extras && customizingItem.extras.length > 0">
                    <h4 class="font-bold text-gray-900 mb-3">Add Extras</h4>
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
                                {{ getTranslatedName(extra.name) }}
                            </span>
                            <span class="text-primary font-bold">+ {{ restaurant.currency }} {{ Number(extra.price).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 italic py-4">
                    No options available
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCustomizeModal = false" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="button" @click="addCustomizedItem" class="flex-1 bg-primary text-white py-3 rounded-xl font-bold hover:bg-primary-hover transition-colors shadow-lg shadow-primary/30">Add to Order</button>
                </div>
            </div>
        </Modal>


</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import Carousel from '@/Components/Carousel.vue';
import Modal from '@/Components/Modal.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps<{
    table: {
        id: string;
        name: string;
        token: string;
    };
    restaurant: {
        name: string;
        currency: string;
        locale: string;
        country?: string;
    };
    categories: any[];
    stockAvailability?: Record<number, { max_quantity: number; available: boolean; is_tracked?: boolean }>;
    ingredientStocks?: Record<string, { current_stock: number; name: string }>;
}>();

const cart = ref<any[]>([]);
const showCart = ref(false);
const showConfirmation = ref(false);
const placing = ref(false);
const customerName = ref('');
const customerPhone = ref('');
const orderNumber = ref('');
const showCustomizeModal = ref(false);
const customizingItem = ref<any>(null);
const selectedExtras = ref<any[]>([]);

// Loyalty State
const checkingLoyalty = ref(false);
const loyaltyChecked = ref(false);
const loyaltyFound = ref(false);
const customerLoyaltyData = ref<any>(null);
const availableRewards = ref<any[]>([]);
const selectedReward = ref<any>(null);

const showOtpModal = ref(false);
const otpValue = ref('');
const otpError = ref('');
const sendingOtp = ref(false);

const cartItemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => {
        const itemPrice = Number(item.price);
        const quantity = item.quantity;
        const extrasTotal = (item.extras || []).reduce((acc: number, extra: any) => acc + Number(extra.price), 0);
        
        // Total for this line item = (Base Price + Extras Total) * Quantity
        const lineTotal = (itemPrice + extrasTotal) * quantity;
        
        return sum + lineTotal;
    }, 0);
});

const tax = computed(() => {
    return subtotal.value * 0.05; // 5% tax
});

const calculateDiscount = () => {
    if (!selectedReward.value) return 0;
    
    const sType = selectedReward.value.reward_type;
    const sVal = Number(selectedReward.value.discount_value) || 0;
    
    if (sType === 'percentage') {
        return subtotal.value * (sVal / 100);
    } else if (sType === 'fixed') {
        return Math.min(subtotal.value, sVal); 
    }
    return 0; // cashback doesn't alter subtotal on checkout directly
};

const total = computed(() => {
    return subtotal.value + tax.value;
});

const finalTotal = computed(() => {
    return Math.max(0, total.value - calculateDiscount());
});

const getTranslatedName = (name: any) => {
    if (typeof name === 'string') return name;
    // @ts-ignore
    return name[props.restaurant.locale] || name['en'] || Object.values(name)[0] || '';
};

const getQty = (itemId: string) => {
    // Sum quantites of all cart items with this base ID (ignoring variations)
    return cart.value
        .filter(i => i.id === itemId)
        .reduce((sum, i) => sum + i.quantity, 0);
};

const isItemAvailable = (itemId: number) => {
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return true;
    return stockInfo.available;
};

const getStockLabel = (itemId: number) => {
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo || !stockInfo.is_tracked) return null;
    if (!stockInfo.available) return 'Sold Out';
    if (stockInfo.max_quantity < 10) return `Only ${stockInfo.max_quantity} left`;
    return null;
};

const addItem = (item: any) => {
    // If item has extras or is a meal, trigger modal
    if ((item.extras && item.extras.length > 0) || item.type === 'meal') {
        customizingItem.value = item;
        selectedExtras.value = [];
        showCustomizeModal.value = true;
        return;
    }

    addToCart(item, []);
};

const addToCart = (item: any, extras: any[]) => {
    // Check for identical item (same ID AND same extras)
    const existingIndex = cart.value.findIndex(cartItem => {
        if (cartItem.id !== item.id) return false;
        
        const cartExtras = cartItem.extras || [];
        if (cartExtras.length !== extras.length) return false;

        const cartIds = cartExtras.map((e:any) => e.id).sort();
        const newIds = extras.map((e:any) => e.id).sort();

        return cartIds.every((id:any, index:number) => id === newIds[index]);
    });

    if (existingIndex !== -1) {
        cart.value[existingIndex].quantity++;
    } else {
        cart.value.push({
            id: item.id,
            name: getTranslatedName(item.name),
            price: Number(item.price), // ensure number
            quantity: 1,
            notes: '',
            extras: extras,
            type: item.type || 'item'
        });
    }
};

const toggleExtra = (extra: any) => {
    const idx = selectedExtras.value.findIndex(e => e.id === extra.id);
    if (idx > -1) {
        selectedExtras.value.splice(idx, 1);
    } else {
        selectedExtras.value.push(extra);
    }
};

const addCustomizedItem = () => {
    if (!customizingItem.value) return;

    const extrasToAdd = selectedExtras.value.map(e => ({
        id: e.id,
        name: getTranslatedName(e.name),
        price: Number(e.price),
        quantity: e.quantity,
        ingredient_id: e.ingredient_id
    }));

    addToCart(customizingItem.value, extrasToAdd);

    showCustomizeModal.value = false;
    customizingItem.value = null;
    selectedExtras.value = [];
};

const removeItem = (item: any) => {
    const existingItem = cart.value.find(cartItem => cartItem.id === item.id);
    
    if (existingItem) {
        if (existingItem.quantity > 1) {
            existingItem.quantity--;
        } else {
            const index = cart.value.findIndex(cartItem => cartItem.id === item.id);
            cart.value.splice(index, 1);
        }
    }
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const incrementQuantity = (index: number) => {
    cart.value[index].quantity++;
};

const decrementQuantity = (index: number) => {
    if (cart.value[index].quantity > 1) {
        cart.value[index].quantity--;
    } else {
        removeFromCart(index);
    }
};

const initiateOrder = async () => {
    if (cart.value.length === 0) return;
    
    if (selectedReward.value) {
        // Request OTP First
        sendingOtp.value = true;
        
        try {
            const response = await fetch((window as any).route('qr.loyalty.request-otp', props.table.token), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    phone: customerPhone.value,
                    reward_id: selectedReward.value.id
                })
            });
            const data = await response.json();
            
            if (data.success) {
                showOtpModal.value = true;
                otpValue.value = '';
                otpError.value = '';
            } else {
                alert(data.message || 'Failed to send OTP. Please try again.');
            }
        } catch (e) {
            console.error(e);
            alert('Error connecting to the server while requesting OTP.');
        } finally {
            sendingOtp.value = false;
        }
    } else {
        // Proceed normally without reward
        placeOrder();
    }
};

const placeOrder = async () => {
    if (cart.value.length === 0) return;

    placing.value = true;
    otpError.value = '';

    try {
        const payload: any = {
            items: cart.value.map(item => ({
                id: item.id,
                quantity: item.quantity,
                notes: item.notes || null,
                extras: item.extras ? item.extras.map((e:any) => ({ id: e.id, quantity: e.quantity || 1 })) : []
            })),
            customer_name: customerName.value || null,
            customer_phone: customerPhone.value || null,
        };

        if (selectedReward.value && otpValue.value) {
            payload.reward_id = selectedReward.value.id;
            payload.otp = otpValue.value;
        }

        const response = await fetch((window as any).route('qr.order', props.table.token), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (data.success) {
            orderNumber.value = data.order.order_number;
            showCart.value = false;
            showConfirmation.value = true;
            cart.value = [];
            customerName.value = '';
            customerPhone.value = '';
            resetLoyalty();
            showOtpModal.value = false;
        } else {
            if (payload.otp) otpError.value = data.message || 'Invalid OTP';
            else alert(data.message || 'Failed to place order. Please try again.');
        }
    } catch (error) {
        console.error('Error placing order:', error);
        alert('An error occurred. Please try again.');
    } finally {
        placing.value = false;
    }
};

const confirmOtpAndOrder = () => {
    if (otpValue.value.length === 6) {
        placeOrder();
    }
};

const closeOtpModal = () => {
    showOtpModal.value = false;
    otpValue.value = '';
    otpError.value = '';
};

const resetLoyalty = () => {
    loyaltyChecked.value = false;
    loyaltyFound.value = false;
    customerLoyaltyData.value = null;
    availableRewards.value = [];
    selectedReward.value = null;
};

const checkLoyalty = async () => {
    if (!customerPhone.value) return;
    
    checkingLoyalty.value = true;
    loyaltyChecked.value = true;
    
    try {
        const response = await fetch((window as any).route('qr.loyalty.check', props.table.token), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                phone: customerPhone.value,
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.found) {
            loyaltyFound.value = true;
            customerLoyaltyData.value = data.customer;
            availableRewards.value = data.rewards || [];
        } else {
            loyaltyFound.value = false;
            customerLoyaltyData.value = null;
            availableRewards.value = [];
        }
    } catch (e) {
        console.error('Error checking loyalty', e);
    } finally {
        checkingLoyalty.value = false;
    }
};

const closeConfirmation = () => {
    showConfirmation.value = false;
};
</script>
