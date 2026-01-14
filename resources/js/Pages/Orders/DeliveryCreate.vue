<template>
    <MainLayout>
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4 h-[calc(100vh-5rem)] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-4 flex-shrink-0">
                <Link :href="route('orders.index')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">{{ $t('nav.pos_screen') || 'POS Screen' }}</h1>
            </div>

            <form @submit.prevent="createOrder" class="flex-1 grid grid-cols-12 gap-6 overflow-hidden h-full pb-4">
                
                <!-- Left Column: Menu (Scrollable) -->
                <div class="col-span-12 lg:col-span-8 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                     <!-- Stock Error Display -->
                    <div v-if="form.errors.items" class="glass-card rounded-2xl p-4 border-2 border-red-300 bg-red-50 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-bold text-red-900 mb-1">⚠️ {{ $t('dashboard_page.low_stock') }}</h4>
                                <ul class="space-y-1 text-sm text-red-800">
                                    <li v-for="(error, idx) in (Array.isArray(form.errors.items) ? form.errors.items : [form.errors.items])" :key="idx">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items Card -->
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2 sticky top-0 bg-white z-10 py-2">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            {{ $t('nav.menu') }}
                        </h3>
                        
                        <div class="space-y-8">
                            <div v-for="category in categoriesList" :key="category.id">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3 sticky top-12 bg-white/95 backdrop-blur z-10 py-2 border-b border-gray-100">
                                    {{ getLocaleName(category.name) }}
                                </h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                                    <div 
                                        v-for="item in category.items" 
                                        :key="item.id"
                                        class="group flex flex-col bg-white border border-gray-100 dark:border-gray-700 hover:border-primary/50 hover:shadow-lg rounded-xl overflow-hidden transition-all duration-300 relative h-full"
                                        :class="{'opacity-75': (!canAddItem(item.id) && getQty(item.id) === 0) || item.inventory_status?.sold_out}"
                                    >
                                        <!-- Badges & Overlays (Same as before) -->
                                        <div 
                                            v-if="selectedReward?.reward_type === 'free_item' && (selectedReward.menu_item_ids ? selectedReward.menu_item_ids.includes(item.id) : selectedReward.menu_item_id === item.id)" 
                                            class="absolute top-2 left-2 z-20 bg-green-500 text-white text-[10px] uppercase font-bold px-2 py-1 rounded-full shadow-sm"
                                        >
                                            {{ $t('loyalty.free') }}
                                        </div>

                                        <div 
                                            v-if="!canAddItem(item.id) || item.inventory_status?.sold_out" 
                                            class="absolute inset-x-0 top-0 z-10 w-full h-32 bg-gray-900/10 backdrop-blur-[1px] flex items-center justify-center"
                                        >
                                            <span 
                                                class="text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm"
                                                :class="item.inventory_status?.sold_out ? 'bg-red-600' : 'bg-gray-900/80'"
                                            >
                                                {{ item.inventory_status?.sold_out ? $t('common.sold_out') : getStockMessage(item.id) }}
                                            </span>
                                        </div>

                                        <!-- Compact Image Area -->
                                        <div class="relative w-full aspect-[4/3] bg-gray-50 overflow-hidden h-32">
                                            <div v-if="item.image" class="w-full h-full">
                                                <img 
                                                    :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image" 
                                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                                />
                                            </div>
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        </div>

                                        <!-- Content Area -->
                                        <div class="flex-1 p-3 flex flex-col min-h-0">
                                            <div class="flex-1 mb-2">
                                                <h5 class="font-bold text-gray-900 line-clamp-1 text-sm" :title="typeof item.name === 'string' ? item.name : getLocaleName(item.name)">
                                                    {{ getLocaleName(item.name) }}
                                                </h5>
                                                <p class="text-xs font-medium text-primary mt-0.5">
                                                    {{ currencyCode }} {{ item.price.toFixed(2) }}
                                                </p>
                                            </div>

                                            <!-- Compact Controls -->
                                            <div class="flex items-center justify-between mt-auto pt-2 border-t border-dashed border-gray-100 dark:border-gray-700">
                                                <button 
                                                    type="button"
                                                    @click="removeItem(item)"
                                                    :disabled="!getQty(item.id)"
                                                    class="w-6 h-6 flex items-center justify-center rounded-lg transition-all"
                                                    :class="getQty(item.id) 
                                                        ? 'bg-red-50 text-red-500 hover:bg-red-100 active:scale-95' 
                                                        : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                                                >
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                                </button>
                                                
                                                <span class="font-bold text-gray-900 text-sm">
                                                    {{ getQty(item.id) > 0 ? getQty(item.id) : 0 }}
                                                </span>

                                                <button 
                                                    type="button"
                                                    @click="addItem(item)"
                                                    :disabled="!canAddItem(item.id) || item.inventory_status?.sold_out"
                                                    class="w-6 h-6 flex items-center justify-center rounded-lg transition-all"
                                                    :class="canAddItem(item.id) && !item.inventory_status?.sold_out
                                                        ? 'bg-primary text-white hover:bg-primary-hover shadow-md shadow-primary/20 active:scale-95' 
                                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                                >
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Cart & Details (Fixed Height) -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-4 overflow-hidden h-full">
                    
                    <!-- Customer Section (Collapsible or Compact) -->
                     <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex-shrink-0">
                         <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2 text-sm uppercase tracking-wide">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                             {{ $t('common.customer') }}
                         </h3>
                        <div class="space-y-2">
                             <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 font-medium text-xs">{{ phoneCode }}</span>
                                <input 
                                    v-model="phoneInput"
                                    type="tel"
                                    placeholder="50 123 4567"
                                    class="w-full pl-12 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                    @input="handlePhoneInput"
                                    @blur="lookupCustomer"
                                />
                            </div>
                             <div v-if="selectedCustomer" class="flex justify-between items-center bg-purple-50 p-2 rounded-lg border border-purple-100">
                                <span class="font-bold text-sm text-purple-900">{{ selectedCustomer.name }}</span>
                                <span class="text-xs font-bold text-purple-600 bg-white px-2 py-0.5 rounded-full border border-purple-200">{{ selectedCustomer.loyalty_points }} pts</span>
                            </div>
                             <div v-else class="grid grid-cols-2 gap-2">
                                <input v-model="form.customer_name" :placeholder="$t('staff.name')" class="py-1.5 px-3 text-sm border border-gray-300 rounded-lg w-full" />
                            </div>
                        </div>
                     </div>

                    <!-- Order Type (Compact) -->
                    <!-- Order Type (Compact) -->
                     <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex-shrink-0">
                        <div class="flex gap-2 bg-gray-100 p-1 rounded-xl">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" v-model="form.type" value="dine_in" class="peer sr-only" />
                                <div class="px-2 py-2 rounded-lg text-center text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm text-gray-500 hover:text-gray-700">
                                    {{ $t('orders.dine_in') }}
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" v-model="form.type" value="takeaway" class="peer sr-only" />
                                <div class="px-2 py-2 rounded-lg text-center text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm text-gray-500 hover:text-gray-700">
                                    {{ $t('orders.takeaway') }}
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" v-model="form.type" value="delivery" class="peer sr-only" />
                                <div class="px-2 py-2 rounded-lg text-center text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-primary peer-checked:shadow-sm text-gray-500 hover:text-gray-700">
                                    {{ $t('orders.delivery') }}
                                </div>
                            </label>
                        </div>
                         <!-- Delivery Extras -->
                        <div v-if="form.type === 'delivery'" class="mt-3 pt-3 border-t border-gray-100 space-y-2">
                             <select v-model="form.delivery_provider" class="w-full text-sm py-2 border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="" disabled>{{ $t('orders.delivery_provider') }}</option>
                                <option v-for="p in ['Talabat', 'Deliveroo', 'Noon', 'Careem', 'Smiles', 'Other']" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <input v-if="form.delivery_provider === 'Other'" v-model="form.delivery_order_id" :placeholder="$t('orders.other_delivery_id')" class="w-full text-sm py-2 border-gray-300 rounded-lg focus:ring-primary focus:border-primary" />
                        </div>
                     </div>


                     <!-- Cart Items (Scrollable) -->
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col flex-1 overflow-hidden min-h-0">
                        <div class="p-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                {{ $t('orders.summary') }}
                            </h3>
                            <span class="bg-primary/10 text-primary text-xs font-bold px-2 py-0.5 rounded-full">{{ cart.length }} items</span>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-3 space-y-2 scrollbar-thin scrollbar-thumb-gray-200">
                            <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 py-8">
                                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <p class="text-sm">{{ $t('menu.no_options') }}</p>
                            </div>
                            <div 
                                v-for="(item, index) in cart" 
                                :key="index" 
                                class="bg-gray-50 p-2 rounded-lg border border-gray-100 text-sm"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ item.qty }}x</span>
                                            <span class="font-medium truncate max-w-[120px]" :title="item.name">{{ item.name }}</span>
                                        </div>
                                         <div v-if="item.extras && item.extras.length > 0" class="text-xs text-blue-600 pl-6">
                                            <span v-for="(ex, i) in item.extras" :key="i" class="block">+ {{ ex.name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold text-xs mr-2">{{ currencyCode }} {{ ((item.price + (item.extras?.reduce((sum, e) => sum + e.price, 0) || 0)) * item.qty).toFixed(2) }}</span>
                                         <button 
                                            type="button"
                                            @click="openNotesModal(item)"
                                            class="p-1 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded"
                                            title="Note"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                         <button 
                                            type="button"
                                            @click="cart.splice(index, 1)"
                                            class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Totals & Actions -->
                        <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-3">
                             <!-- Rewards (If ANY available) -->
                            <div v-if="availableRewards.length > 0 && selectedCustomer" class="mb-2">
                                <label class="text-xs font-bold text-purple-700 uppercase mb-1 block">{{ $t('loyalty.redeem_rewards') }}</label>
                                <select 
                                    :value="selectedReward?.id" 
                                    @change="(e) => { const r = availableRewards.find(r => r.id === parseInt((e.target as HTMLSelectElement).value)); if(r) toggleReward(r); else selectedReward = null; }"
                                    class="w-full text-xs py-1.5 border-purple-200 bg-purple-50 rounded-lg text-purple-900 focus:ring-purple-500"
                                >
                                    <option :value="null">-- No Reward --</option>
                                    <option 
                                        v-for="reward in availableRewards" 
                                        :key="reward.id" 
                                        :value="reward.id"
                                        :disabled="!canRedeemReward(reward)"
                                    >
                                        {{ getLocaleName(reward.name) }} ({{ reward.points_required }} pts)
                                    </option>
                                </select>
                                <!-- OTP Input if reward selected -->
                                <div v-if="selectedReward && !otpVerified" class="mt-2 flex gap-1">
                                    <input v-model="otpInput" placeholder="OTP" class="w-20 text-xs py-1 px-2 border-gray-300 rounded" />
                                    <button @click="requestOtp" v-if="!otpSent" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">Send</button>
                                    <button @click="verifyOtp" class="flex-1 text-xs bg-purple-600 text-white px-2 py-1 rounded font-bold">Verify</button>
                                </div>
                            </div>

                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ $t('common.subtotal') }}</span>
                                <span>{{ currencyCode }} {{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-sm text-green-600 font-medium">
                                <span>{{ $t('loyalty.discount') }}</span>
                                <span>-{{ currencyCode }} {{ discountAmount.toFixed(2) }}</span>
                            </div>
                             <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">
                                <span>{{ $t('common.total') }}</span>
                                <span>{{ currencyCode }} {{ total.toFixed(2) }}</span>
                            </div>

                            <Button 
                                type="submit" 
                                block 
                                size="lg"
                                :loading="form.processing"
                                :disabled="cart.length === 0"
                                class="w-full shadow-lg shadow-primary/20"
                            >
                                {{ $t('nav.orders_create') }} ({{ currencyCode }} {{ total.toFixed(2) }})
                            </Button>
                        </div>
                     </div>

                </div>
            </form>
        </div>

        <!-- Item Notes Modal -->
        <Modal :show="showNotesModal" @close="closeNotesModal" :title="$t('receipt.footer_text')" size="md">
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
                        <p class="mt-1 text-xs text-gray-500">{{ $t('orders.save_shortcut') }}</p>
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
                        <!-- Ideally fetch child item names via relations in controller -->
                        <!-- For now, assuming bundle logic backend handles availability checks -->
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

    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

interface MenuItem {
    id: number;
    name: Record<string, string> | string;
    description?: string;
    price: number;
    image?: string;
    images?: string[];
    inventory_status?: {
        sold_out: boolean;
        low_stock: boolean;
        missing_ingredients: string[];
    };
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

interface Table {
    id: number;
    name: string;
    capacity: number;
    status: string;
    location: string | null;
    is_available?: boolean;
}

const props = withDefaults(defineProps<{
    menuCategories?: Category[];
    customers?: Customer[];
    rewards?: Reward[];
    tables?: Table[];
    currency?: string;
    stockAvailability?: Record<number, { max_quantity: number; available: boolean; is_tracked?: boolean }>;
    ingredientStocks?: Record<string, { current_stock: number; name: string }>;
    google_map_location?: string;
}>(), {
    menuCategories: () => [],
    customers: () => [],
    rewards: () => [],
    tables: () => [],
    currency: 'AED',
    stockAvailability: () => ({}),
    ingredientStocks: () => ({})
});

const { locale, t } = useI18n();
const route = (window as any).route;
const page = usePage();

// Computed
const categoriesList = computed(() => props.menuCategories || []);
const currencyCode = computed(() => (page.props.current_restaurant as any)?.currency || props.currency || 'AED');
const phoneCode = computed(() => (page.props.current_restaurant as any)?.phone_code || '+971');
const availableRewards = computed(() => props.rewards || []);

// State
const cart = ref<CartItem[]>([]);
const selectedCustomer = ref<Customer | null>(null);
const selectedReward = ref<Reward | null>(null);
const phoneInput = ref('');
const showNotesModal = ref(false);
const editingCartItem = ref<CartItem | null>(null);
const tempNotes = ref('');
const showCustomizeModal = ref(false);
const customizingItem = ref<MenuItem | null>(null);
const selectedExtras = ref<any[]>([]); // Track selected extras IDs/Objects


// Form
const form = useForm({
    customer_phone: '',
    customer_name: '',
    customer_birth_date: '',
    customer_id: null as number | null,
    type: 'dine_in',
    table_id: null as number | null,
    items: [] as { menu_item_id: number; quantity: number; unit_price: number; notes?: string; extras?: any[] }[],
    subtotal: 0,
    discount_amount: 0,
    tax: 0,
    total: 0,
    notes: '',
    reward_id: null as number | null,
    otp: '',
    delivery_provider: '',
    delivery_order_id: ''
});

// Handle phone input
const handlePhoneInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    // Allow only numbers
    const value = target.value.replace(/\D/g, '');
    
    // Update the display value
    phoneInput.value = value;
    
    // Update the form value with full format based on restaurant country
    if (value) {
        form.customer_phone = phoneCode.value + value;
    } else {
        form.customer_phone = '';
    }
};

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
        await (window as any).axios.post(route('loyalty.customers.request-otp', selectedCustomer.value.id));
        otpSent.value = true;
        startOtpTimer();
    } catch (error: any) {
        console.error('Failed to send OTP', error);
        const msg = error.response?.data?.message || 'Failed to send OTP. Please try again.';
        otpError.value = msg;
        otpSent.value = false;
    }
};

const verifyOtp = async () => {
    if (!selectedCustomer.value || otpInput.value.length !== 6) return;
    
    try {
        otpError.value = '';
        await (window as any).axios.post(route('loyalty.customers.verify-otp-only', selectedCustomer.value.id), {
            otp: otpInput.value
        });
        otpVerified.value = true;
        form.otp = otpInput.value;
    } catch (error: any) {
        console.error('OTP Verification Failed', error);
        otpVerified.value = false;
        otpError.value = error.response?.data?.message || 'Invalid OTP';
    }
};

const startOtpTimer = () => {
    otpTimer.value = 60; 
    if (timerInterval) clearInterval(timerInterval);
    
    timerInterval = setInterval(() => {
        otpTimer.value--;
        if (otpTimer.value <= 0) {
            clearInterval(timerInterval);
        }
    }, 1000);
};



// Customer lookup
const lookupCustomer = () => {
    if (!phoneInput.value) {
        selectedCustomer.value = null;
        return;
    }
    
    // Search using the input value (local number without prefix issues)
    // We compare against the stored phone which likely contains the country code
    const searchText = phoneInput.value;
    
    const customer = props.customers?.find(c => 
        (c.phone && c.phone.includes(searchText))
    );
    
    if (customer) {
        selectedCustomer.value = customer;
        form.customer_name = customer.name || '';
        form.customer_id = customer.id;
        // Ensure form phone matches found customer
        form.customer_phone = customer.phone;
    } else {
        selectedCustomer.value = null;
        form.customer_id = null;
        // Keep the entered phone number in form
        form.customer_phone = phoneCode.value + phoneInput.value;
    }
};

// Watch phone changes for auto-lookup
watch(phoneInput, (newVal) => {
    if (newVal && newVal.length >= 7) {
        lookupCustomer();
    } else {
        selectedCustomer.value = null;
    }
});

// Helpers
const getLocaleName = (name: Record<string, string> | string): string => {
    if (typeof name === 'string') return name;
        if (typeof name === 'string') return name;
    // Return both En/Ar if requested? For now stick to standard locale.
    return name[locale.value] || Object.values(name)[0] || '';
};

const getItemNameById = (id: number): string => {
    for (const cat of categoriesList.value) {
        const item = cat.items.find((i: MenuItem) => i.id === id);
        if (item) return getLocaleName(item.name);
    }
    return t('common.unknown_item') || 'Unknown Item';
};

const getQty = (itemId: number): number => {
    return cart.value
        .filter(i => i.id === itemId)
        .reduce((sum, i) => sum + i.qty, 0);
};

const addItem = (item: MenuItem) => {
    // Check if item has options (extras) OR is a meal that might need review (optional, but good for UX)
    if ((item.extras && item.extras.length > 0) || item.type === 'meal') {
        openCustomizeModal(item);
        return;
    }

    const existing = cart.value.find(i => i.id === item.id && (!i.extras || i.extras.length === 0));
    if (existing) {
        existing.qty++;
    } else {
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
    
    // Prepare extras with ID for comparison
    const newExtras = selectedExtras.value.map(e => ({
        id: e.id,
        name: getLocaleName(e.name),
        price: Number(e.price),
        ingredient_id: e.ingredient_id,
        quantity: e.quantity
    }));

    // Check for existing item with SAME extras
    const existingIndex = cart.value.findIndex(cartItem => {
        if (cartItem.id !== item.id) return false;
        
        const cartExtras = cartItem.extras || [];
        if (cartExtras.length !== newExtras.length) return false;

        // Sort and compare IDs to ensure match regardless of order
        const cartIds = cartExtras.map(e => e.id).sort();
        const newIds = newExtras.map(e => e.id).sort();

        return cartIds.every((id, index) => id === newIds[index]);
    });

    if (existingIndex !== -1) {
        cart.value[existingIndex].qty++;
    } else {
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
    if (idx > -1) {
        selectedExtras.value.splice(idx, 1);
    } else {
        // Validation: Check if extra has enough stock
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

const canAddCustomizedItem = computed(() => {
     // A simple check for disable button state if needed, mostly handled by click logic
     return true; 
});

const removeItem = (item: MenuItem) => {
    const idx = cart.value.findIndex(i => i.id === item.id);
    if (idx !== -1) {
        if (cart.value[idx].qty > 1) {
            cart.value[idx].qty--;
        } else {
            cart.value.splice(idx, 1);
        }
    }
};

// Stock availability helpers
const canAddItem = (itemId: number): boolean => {
    // 1. Check basic Sold Out status
    const itemInMenu = categoriesList.value
        .flatMap(c => c.items)
        .find(i => i.id === itemId);
        
    if (itemInMenu?.inventory_status?.sold_out) return false;

    // 2. Dynamic Ingredient Check
    // If we have detailed ingredient stocks and the item has a recipe
    if (props.ingredientStocks && Object.keys(props.ingredientStocks).length > 0) {
        // Calculate projected usage for ALL ingredients in cart + this new item
        const projectedUsage: Record<string, number> = {};

        // A. Sum up existing cart usage
        cart.value.forEach(cartItem => {
            if (cartItem.recipe) {
                cartItem.recipe.forEach(comp => {
                    const current = projectedUsage[comp.ingredient_id] || 0;
                    projectedUsage[comp.ingredient_id] = current + (comp.quantity * cartItem.qty);
                });
            }
        });

        // B. Add the item we want to add
        // We need to find the recipe for this itemId (either from menu or cart)
        const recipe = itemInMenu?.recipe;
        
        if (recipe) {
            recipe.forEach(comp => {
                const current = projectedUsage[comp.ingredient_id] || 0;
                projectedUsage[comp.ingredient_id] = current + comp.quantity;
            });
        }

        // C. specific check
        // Iterate through all involved ingredients and check against stock
        for (const [ingId, requiredQty] of Object.entries(projectedUsage)) {
            const stockInfo = props.ingredientStocks[ingId];
            if (stockInfo) {
                if (requiredQty > stockInfo.current_stock) {
                    return false; // Exceeds stock!
                }
            }
        }
    }

    // 3. Fallback to Legacy Max Quantity
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return true; 
    
    // Only use legacy check if we didn't fail the dynamic check above
    // (And if legacy check is relevant, e.g., for non-ingredient items)
    const currentQty = getQty(itemId);
    return currentQty < stockInfo.max_quantity;
};

const getStockMessage = (itemId: number): string => {
    // Use dynamic check first
    if (!canAddItem(itemId)) {
         // Check if it's due to Sold Out status
         const itemInMenu = categoriesList.value.flatMap(c => c.items).find(i => i.id === itemId);
         if (itemInMenu?.inventory_status?.sold_out) return t('common.sold_out');
         
         // If not sold out but can't add, it's low stock/max reached
         return t('common.sold_out'); // or specific max_message if we had one
    }

    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return '';
    
    if (stockInfo.is_tracked === false) return '';

    const currentQty = getQty(itemId);
    const remaining = stockInfo.max_quantity - currentQty;
    
    if (!stockInfo.available || stockInfo.max_quantity === 0) {
        return t('common.sold_out');
    }
    
    if (remaining === 0) {
        return t('common.sold_out');
    }
    
    if (remaining <= 3) {
        return t('menu.stock_available', { stock: remaining });
    }
    
    return t('menu.stock_available', { stock: stockInfo.max_quantity });
};




// Reward helpers
const canRedeemReward = (reward: Reward): boolean => {
    if (!selectedCustomer.value) return false;
    if (selectedCustomer.value.loyalty_points < reward.points_required) return false;
    if (reward.min_order_value && subtotal.value < reward.min_order_value) return false;
    
    // Check item eligibility for discounts
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
        
        // Auto-request OTP when reward is selected (optional, or wait for use to click send)
        // Ensure user is ready
        // Removed auto-request to allow user to click Send Code manually


        // Auto-add free item if not in cart
        if (reward.reward_type === 'free_item') {
             const targetIds = (reward.menu_item_ids && reward.menu_item_ids.length) 
                ? reward.menu_item_ids 
                : (reward.menu_item_id ? [reward.menu_item_id] : []);

             if (targetIds.length > 0) {
                 // Check for each target item and add if missing
                 targetIds.forEach(targetId => {
                     const inCart = cart.value.some(i => i.id === targetId);
                     if (!inCart) {
                         // Find item in menu
                         for (const cat of categoriesList.value) {
                             const item = cat.items.find((i: MenuItem) => i.id === targetId);
                             if (item) {
                                 addItem(item);
                                 break; // Item found, stop searching categories for THIS item
                             }
                         }
                     }
                 });
             }
        }
    }
};



// Calculations
const freeItems = computed(() => {
    if (selectedReward.value?.reward_type !== 'free_item') return [];
    const targetIds = (selectedReward.value.menu_item_ids && selectedReward.value.menu_item_ids.length) 
        ? selectedReward.value.menu_item_ids 
        : (selectedReward.value.menu_item_id ? [selectedReward.value.menu_item_id] : []);
    
    if (!targetIds.length) return [];
    
    // Return all eligible items found in cart
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
            // Logic: If specific items, verify presence (already done in canRedeem), deduct from total?
            // Or deduct from eligible total? Fixed amount usually off bill.
            // Requirement was about "setting" reward on specific items.
            // If specific items set, we assume it's a condition.
            return Math.min(selectedReward.value.discount_value || 0, subtotal.value);
        case 'free_item':
            // Sum of 1 unit price for EACH eligible item found
            return freeItems.value.reduce((sum, item) => sum + item.price, 0);
        default:
            return 0;
    }
});

const afterDiscount = computed(() => Math.max(0, subtotal.value - discountAmount.value));

const tax = computed(() => afterDiscount.value * 0.05);

const total = computed(() => afterDiscount.value + tax.value);

// Notes Modal Functions
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
    if (editingCartItem.value) {
        editingCartItem.value.notes = tempNotes.value.trim() || undefined;
    }
    closeNotesModal();
};

const createOrder = () => {
    // Check for Reward OTP requirement
    if (selectedReward.value) {
        if (!otpVerified.value) {
            alert(t('loyalty.verify_otp_required'));
            // Scroll to OTP section?
            return;
        }
    }

    submitOrder();
};

const submitOrder = () => {
    form.items = cart.value.map(item => ({
        menu_item_id: item.id,
        quantity: item.qty,
        unit_price: item.price,
        notes: item.notes,
        extras: item.extras // Include extras in submission
    }));
    form.subtotal = subtotal.value;
    form.discount_amount = discountAmount.value;
    form.tax = tax.value;
    form.total = total.value;
    form.reward_id = selectedReward.value?.id || null;
    if (form.type === 'takeaway' || form.type === 'delivery') {
        form.table_id = null;
    }

    form.post(route('orders.store'), {
        onSuccess: async () => {
             // Retrieve the newly created order from the response (assuming it's passed as flash or prop)
             // Since Inertia reload might clear props, we rely on the server returning the order object via flash session or redirect props.
             // If the server redirects back, we can access the latest order if we modify the controller to pass it.
             // HOWEVER, simpler approach: The server likely redirects to 'back' with a success message.
             // Can we get the Order ID? 
             // Ideally we should print the receipt. 
             // Let's check props.orders? No, that's a list.
             // Modify Controller to return the Order? 
             // For now, let's just clear the cart. Printing usually happens on the order listing or via a separate action.
             // BUT, if the user WANTS it...
             // Let's assume the user might want a print dialog.
            cart.value = [];
            selectedReward.value = null;
            selectedCustomer.value = null;
            phoneInput.value = '';
            otpInput.value = '';
            otpSent.value = false;
            otpVerified.value = false;
            otpError.value = '';
            form.reset();
        }
    });
};
</script>
