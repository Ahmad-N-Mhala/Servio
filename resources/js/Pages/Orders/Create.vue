<template>
    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('orders.index')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h1 class="text-3xl font-bold text-gray-900">New Order</h1>
            </div>

            <form @submit.prevent="createOrder" class="space-y-8">
                <!-- Stock Error Display -->
                <div v-if="form.errors.items" class="glass-card rounded-2xl p-6 border-2 border-red-300 bg-red-50">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-bold text-red-900 mb-2">⚠️ Insufficient Stock</h4>
                            <ul class="space-y-1 text-sm text-red-800">
                                <li v-for="(error, idx) in (Array.isArray(form.errors.items) ? form.errors.items : [form.errors.items])" :key="idx">
                                    {{ error }}
                                </li>
                            </ul>
                            <p class="mt-3 text-sm text-red-700 font-medium">
                                Please remove these items from your order or reduce the quantity to proceed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Card -->
                <div class="glass-card rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        Customer Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500 font-medium">+971</span>
                                <input 
                                    v-model="phoneInput"
                                    type="tel"
                                    placeholder="50 123 4567"
                                    maxlength="9"
                                    @input="handlePhoneInput"
                                    @blur="lookupCustomer"
                                    class="w-full pl-14 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                                />
                            </div>
                        </div>
                        <Input 
                            v-model="form.customer_name"
                            label="Customer Name"
                            type="text"
                            placeholder="Optional"
                            :error="form.errors.customer_name"
                        />
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <Input 
                            v-model="form.customer_birth_date"
                            label="Birth Date (Optional)"
                            type="date"
                            placeholder="YYYY-MM-DD"
                            :error="form.errors.customer_birth_date"
                        />
                        <div></div> <!-- Spacer -->
                    </div>
                    
                    <!-- Customer Loyalty Points Display -->
                    <div v-if="selectedCustomer" class="mt-4 p-4 bg-gradient-to-r from-primary/10 to-purple-100 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Loyalty Member</p>
                                    <p class="font-bold text-gray-900">{{ selectedCustomer.name || 'Customer' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Available Points</p>
                                <p class="text-2xl font-bold text-primary">{{ selectedCustomer.loyalty_points }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Type Selection -->
                <div class="glass-card rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        Order Type
                    </h3>
                    <div class="flex gap-4 mb-6">
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" v-model="form.type" value="dine_in" class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 peer-checked:hover:border-primary transition-all text-center h-full flex flex-col items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary group-hover:text-gray-600 peer-checked:group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.704 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                                </svg>
                                <span class="font-semibold text-gray-700 peer-checked:text-primary">Dine In</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" v-model="form.type" value="takeaway" class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 peer-checked:hover:border-primary transition-all text-center h-full flex flex-col items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary group-hover:text-gray-600 peer-checked:group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="font-semibold text-gray-700 peer-checked:text-primary">Takeaway</span>
                            </div>
                        </label>
                    </div>

                    <div v-if="form.type === 'dine_in'" class="animate-fade-in-up">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Table (Optional)</label>
                        <select 
                            v-model="form.table_id" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-primary focus:border-primary transition-colors"
                        >
                            <option :value="null">No table assigned</option>
                            <option v-for="table in tablesList" :key="table.id" :value="table.id" :disabled="!table.is_available">
                                {{ table.name }} ({{ table.capacity }} seats) - {{ table.location || 'Main' }}{{ !table.is_available ? ' [OCCUPIED]' : '' }}
                            </option>
                        </select>
                        <p v-if="form.errors.table_id" class="mt-1 text-sm text-red-600">{{ form.errors.table_id }}</p>
                    </div>
                </div>

                <!-- Menu Items Card -->
                <div class="glass-card rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        Menu Items
                    </h3>
                    
                    <div class="space-y-6">
                        <div v-for="category in categoriesList" :key="category.id">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                {{ getLocaleName(category.name) }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors"
                                    :class="{'opacity-60': !canAddItem(item.id) && getQty(item.id) === 0}"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">
                                            {{ getLocaleName(item.name) }}
                                            <span v-if="selectedReward?.reward_type === 'free_item' && (selectedReward.menu_item_ids ? selectedReward.menu_item_ids.includes(item.id) : selectedReward.menu_item_id === item.id)" class="ml-2 text-[10px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-bold uppercase">Free</span>
                                        </p>
                                        <p class="text-sm font-semibold text-primary">{{ currencyCode }} {{ item.price.toFixed(2) }}</p>
                                        <p 
                                            v-if="getStockMessage(item.id)" 
                                            class="text-xs font-medium mt-1"
                                            :class="{
                                                'text-red-600': getMaxAvailable(item.id) === 0,
                                                'text-amber-600': getMaxAvailable(item.id) > 0 && getMaxAvailable(item.id) <= 3,
                                                'text-gray-500': getMaxAvailable(item.id) > 3
                                            }"
                                        >
                                            {{ getStockMessage(item.id) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 ml-4">
                                        <button 
                                            type="button"
                                            @click="removeItem(item)"
                                            :disabled="!getQty(item.id)"
                                            class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center font-bold text-lg">{{ getQty(item.id) }}</span>
                                        <button 
                                            type="button"
                                            @click="addItem(item)"
                                            :disabled="!canAddItem(item.id)"
                                            :title="!canAddItem(item.id) ? getStockMessage(item.id) : ''"
                                            class="w-9 h-9 flex items-center justify-center rounded-full transition-colors relative group"
                                            :class="canAddItem(item.id) 
                                                ? 'bg-primary text-white hover:bg-primary-hover' 
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <!-- Tooltip -->
                                            <span 
                                                v-if="!canAddItem(item.id)" 
                                                class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap z-10"
                                            >
                                                <span class="block">{{ getStockMessage(item.id) }}</span>
                                                <svg class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 w-3 h-2 text-gray-900" viewBox="0 0 12 6">
                                                    <path fill="currentColor" d="M0 0l6 6 6-6z"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Rewards Redemption Card -->
                <div v-if="availableRewards.length > 0" class="glass-card rounded-2xl p-6 border-2 border-purple-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </div>
                        Redeem Loyalty Rewards
                        <span v-if="selectedCustomer" class="ml-auto text-sm font-normal text-gray-500">
                            {{ selectedCustomer.loyalty_points }} points available
                        </span>
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div 
                            v-for="reward in availableRewards" 
                            :key="reward.id"
                            @click="toggleReward(reward)"
                            :class="[
                                'p-4 rounded-xl border-2 cursor-pointer transition-all',
                                selectedReward?.id === reward.id 
                                    ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200' 
                                    : canRedeemReward(reward) 
                                        ? 'border-gray-200 bg-white hover:border-purple-300 hover:bg-purple-50/50' 
                                        : 'border-gray-200 bg-gray-100 opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ getLocaleName(reward.name) }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ reward.description || getRewardTypeLabel(reward) }}</p>
                                </div>
                                <div v-if="selectedReward?.id === reward.id" class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', 
                                    canRedeemReward(reward) ? 'bg-purple-100 text-purple-700' : 'bg-gray-200 text-gray-600']">
                                    {{ reward.points_required }} points
                                </span>
                                <span class="text-sm font-semibold text-green-600">
                                    {{ getRewardValue(reward) }}
                                </span>
                            </div>
                             <p v-if="reward.min_order_value && subtotal < reward.min_order_value" class="text-xs text-red-500 mt-1">
                                Min Order: {{ currencyCode }} {{ reward.min_order_value }}
                            </p>
                        </div>
                    </div>
                    
                    <p v-if="!selectedCustomer" class="mt-4 text-sm text-amber-600 bg-amber-50 p-3 rounded-xl">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Enter customer phone to check loyalty points and redeem rewards
                    </p>
                </div>

                <!-- Order Summary Card -->
                <div v-if="cart.length > 0" class="glass-card rounded-2xl p-6 border-2 border-primary/20">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        Order Summary
                    </h3>
                    
                    <div class="space-y-3 mb-4">
                        <div 
                            v-for="item in cart" 
                            :key="item.id" 
                            class="flex justify-between items-center py-2 border-b border-gray-100"
                        >
                            <div>
                                <span class="font-medium">{{ item.name }}</span>
                                <span class="font-medium">{{ item.name }}</span>
                                <span v-if="selectedReward?.reward_type === 'free_item' && ((freeItems.some(i => i.id === item.id)) || (!freeItems.length && selectedReward.menu_item_id === item.id))" class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-bold">1 FREE</span>
                                <span class="text-gray-500 ml-2">× {{ item.qty }}</span>
                            </div>
                            <span class="font-semibold">{{ currencyCode }} {{ (item.price * item.qty).toFixed(2) }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 pt-3 border-t-2 border-gray-200">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>{{ currencyCode }} {{ subtotal.toFixed(2) }}</span>
                        </div>
                        
                        <!-- Reward Discount -->
                        <div v-if="selectedReward && discountAmount > 0" class="flex justify-between text-green-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                                Reward Discount
                            </span>
                            <span>-{{ currencyCode }} {{ discountAmount.toFixed(2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-500">
                            <span>Tax (5%)</span>
                            <span>{{ currencyCode }} {{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-primary pt-2">
                            <span>Total</span>
                            <span>{{ currencyCode }} {{ total.toFixed(2) }}</span>
                        </div>
                        
                        <!-- Points to be used -->
                        <div v-if="selectedReward" class="mt-3 p-3 bg-purple-50 rounded-xl">
                            <div class="flex justify-between text-sm">
                                <span class="text-purple-700">Points to redeem:</span>
                                <span class="font-bold text-purple-700">{{ selectedReward.points_required }} points</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="glass-card rounded-2xl p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Special Instructions (optional)</label>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3 px-4"
                        placeholder="Any special requests or instructions..."
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <Link :href="route('orders.index')" class="flex-1 block">
                        <Button type="button" variant="secondary" block size="lg">
                            Cancel
                        </Button>
                    </Link>
                    <div class="flex-1">
                        <Button 
                            type="submit" 
                            block 
                            :loading="form.processing"
                            :disabled="cart.length === 0"
                        >
                            Create Order
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

interface MenuItem {
    id: number;
    name: Record<string, string> | string;
    price: number;
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
    stockAvailability?: Record<number, { max_quantity: number; available: boolean }>;
}>(), {
    menuCategories: () => [],
    customers: () => [],
    rewards: () => [],
    tables: () => [],
    currency: 'AED',
    stockAvailability: () => ({})
});

const { locale } = useI18n();
const route = (window as any).route;

// Computed
const categoriesList = computed(() => props.menuCategories || []);
const currencyCode = computed(() => props.currency || 'AED');
const availableRewards = computed(() => props.rewards || []);
const tablesList = computed(() => props.tables || []);

// State
const cart = ref<CartItem[]>([]);
const selectedCustomer = ref<Customer | null>(null);
const selectedReward = ref<Reward | null>(null);
const phoneInput = ref('');

// Form
const form = useForm({
    customer_phone: '',
    customer_name: '',
    customer_birth_date: '',
    customer_id: null as number | null,
    type: 'dine_in',
    table_id: null as number | null,
    items: [] as { menu_item_id: number; quantity: number; unit_price: number }[],
    subtotal: 0,
    discount_amount: 0,
    tax: 0,
    total: 0,
    notes: '',
    reward_id: null as number | null
});

// Handle phone input
const handlePhoneInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    // Allow only numbers
    const value = target.value.replace(/\D/g, '');
    
    // Update the display value
    phoneInput.value = value;
    
    // Update the form value with full format (+971...)
    if (value) {
        form.customer_phone = '+971' + value;
    } else {
        form.customer_phone = '';
    }
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
        form.customer_phone = '+971' + phoneInput.value;
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
    return name[locale.value] || name['en'] || 'Unknown';
};

const getQty = (itemId: number): number => {
    const item = cart.value.find(i => i.id === itemId);
    return item?.qty || 0;
};

const addItem = (item: MenuItem) => {
    const existing = cart.value.find(i => i.id === item.id);
    if (existing) {
        existing.qty++;
    } else {
        cart.value.push({
            id: item.id,
            name: getLocaleName(item.name),
            price: item.price,
            qty: 1
        });
    }
};

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
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return true; // If no stock info, allow (item has no ingredients)
    
    const currentQty = getQty(itemId);
    return currentQty < stockInfo.max_quantity;
};

const getStockMessage = (itemId: number): string => {
    const stockInfo = props.stockAvailability?.[itemId];
    if (!stockInfo) return '';
    
    const currentQty = getQty(itemId);
    const remaining = stockInfo.max_quantity - currentQty;
    
    if (!stockInfo.available || stockInfo.max_quantity === 0) {
        return 'Out of stock';
    }
    
    if (remaining === 0) {
        return 'Maximum quantity reached';
    }
    
    if (remaining <= 3) {
        return `Only ${remaining} left`;
    }
    
    return `${stockInfo.max_quantity} available`;
};

const getMaxAvailable = (itemId: number): number => {
    return props.stockAvailability?.[itemId]?.max_quantity ?? 999;
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

const getRewardTypeLabel = (reward: Reward): string => {
    switch (reward.reward_type) {
        case 'discount_percentage':
            return `${reward.discount_value}% off your order`;
        case 'discount_fixed':
            return `${currencyCode.value} ${reward.discount_value} off`;
        case 'free_item':
            return 'Free item';
        case 'cashback':
            return `${reward.discount_value}% cashback`;
        default:
            return '';
    }
};

const getRewardValue = (reward: Reward): string => {
    switch (reward.reward_type) {
        case 'discount_percentage':
            return `-${reward.discount_value}%`;
        case 'discount_fixed':
            return `-${currencyCode.value} ${reward.discount_value}`;
        case 'free_item':
            return 'FREE';
        case 'cashback':
            return `${reward.discount_value}% back`;
        default:
            return '';
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
    cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0)
);

const discountAmount = computed(() => {
    if (!selectedReward.value) return 0;
    
    switch (selectedReward.value.reward_type) {
        case 'discount_percentage':
            if (selectedReward.value.menu_item_ids?.length) {
                const eligibleTotal = cart.value
                    .filter(i => selectedReward.value!.menu_item_ids!.includes(i.id))
                    .reduce((sum, i) => sum + (i.price * i.qty), 0);
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

const createOrder = () => {
    form.items = cart.value.map(item => ({
        menu_item_id: item.id,
        quantity: item.qty,
        unit_price: item.price
    }));
    form.subtotal = subtotal.value;
    form.discount_amount = discountAmount.value;
    form.tax = tax.value;
    form.total = total.value;
    form.reward_id = selectedReward.value?.id || null;
    if (form.type === 'takeaway') {
        form.table_id = null;
    }

    form.post(route('orders.store'), {
        onSuccess: () => {
            cart.value = [];
            selectedReward.value = null;
            selectedCustomer.value = null;
            phoneInput.value = '';
            form.reset();
        }
    });
};
</script>
