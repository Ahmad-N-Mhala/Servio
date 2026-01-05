<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('loyalty.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('loyalty.manage_description') }}</p>
                </div>
                <Button v-if="hasPermission('manage_rewards')" @click="showRewardModal = true" variant="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ $t('loyalty.add_reward') }}
                </Button>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'overview'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        {{ $t('loyalty.overview_rewards') }}
                    </button>

                    <button 
                         @click="activeTab = 'members'"
                         class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                         :class="activeTab === 'members' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                     >
                         {{ $t('loyalty.members') }}
                    </button>
                </nav>
            </div>

            <!-- Overview Content -->
            <div v-if="activeTab === 'overview'" class="space-y-12">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- ... existing stats ... -->
                    <div class="glass-card p-6 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-100 rounded-xl">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('loyalty.total_members') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ customers.total }}</p>
                            </div>
                        </div>
                    </div>
                
                    <div class="glass-card p-6 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-100 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('loyalty.active_rewards') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ rewards.length }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-100 rounded-xl">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $t('loyalty.redemptions') }}</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Active Rewards List -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="flex justify-between items-center mb-6">
                         <h2 class="text-lg font-bold text-gray-900">{{ $t('loyalty.active_rewards') }}</h2>
                         <Button v-if="hasPermission('manage_rewards')" @click="showRewardModal = true" variant="secondary" size="sm">
                            {{ $t('loyalty.add_new_reward') }}
                        </Button>
                    </div>
                   
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="reward in rewards" :key="reward.id" class="glass-card p-6 rounded-2xl border border-gray-100 hover:border-primary/30 transition-all group relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                <button v-if="hasPermission('manage_rewards')" @click="openDesignModal(reward)" class="text-purple-500 hover:text-purple-700 bg-white/80 p-1 rounded-full shadow-sm backdrop-blur-sm transition-colors" :title="$t('loyalty.design_card')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                </button>
                                <button v-if="hasPermission('manage_rewards')" @click="openEditModal(reward)" class="text-blue-500 hover:text-blue-700 bg-white/80 p-1 rounded-full shadow-sm backdrop-blur-sm transition-colors" :title="$t('loyalty.edit_reward')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button v-if="hasPermission('manage_rewards')" @click="deleteReward(reward)" class="text-red-500 hover:text-red-700 bg-white/80 p-1 rounded-full shadow-sm backdrop-blur-sm transition-colors" :title="$t('loyalty.delete_reward')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="flex items-start justify-between mb-4">
                                <div class="p-3 rounded-xl bg-gradient-to-br from-primary/10 to-purple-100">
                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                    </svg>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold text-primary bg-primary/10 rounded-full">
                                    {{ reward.points_required }} {{ $t('loyalty.points') }}
                                </span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ getLocaleName(reward.name) }}</h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ reward.description || $t('common.no_description') }}</p>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ $t('loyalty.type') }}: {{ formatRewardType(reward.reward_type) }}
                                </span>
                                <span class="text-sm font-bold text-gray-900">
                                    {{ formatRewardValue(reward) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers Table -->
             <div v-show="activeTab === 'members'">

            <!-- Customers Table -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900">{{ $t('loyalty.members') }}</h2>
                    <div class="relative">
                        <input 
                            v-model="params.search"
                            type="text" 
                            :placeholder="$t('loyalty.search_members')" 
                            class="pl-10 pr-4 py-2 rounded-xl border-gray-200 focus:border-primary focus:ring-primary text-sm"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('name')"
                                >
                                    <div class="flex items-center gap-1">
                                        {{ $t('orders.customer') }}
                                        <span v-if="params.sort_field === 'name'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('phone')"
                                >
                                    <div class="flex items-center gap-1">
                                        {{ $t('orders.customer_phone') }}
                                        <span v-if="params.sort_field === 'phone'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('points_balance')"
                                >
                                    <div class="flex items-center gap-1">
                                        {{ $t('loyalty.points_balance') }}
                                        <span v-if="params.sort_field === 'points_balance'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('total_spent')"
                                >
                                    <div class="flex items-center gap-1">
                                        {{ $t('loyalty.total_spent') }}
                                        <span v-if="params.sort_field === 'total_spent'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-bold">
                                            {{ customer.name ? customer.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ customer.name || $t('common.unknown') }}</div>
                                            <div class="text-xs text-gray-500">{{ $t('loyalty.member_since') }} {{ new Date(customer.created_at).toLocaleDateString() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ customer.phone }}</div>
                                    <div class="text-xs text-gray-500">{{ customer.email || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ customer.loyalty_points?.balance || 0 }} {{ $t('loyalty.points') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ currency }} {{ customer.total_spent || '0.00' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('loyalty.customers.show', customer.id)" class="text-primary hover:text-primary-hover font-bold">{{ $t('loyalty.view_details') }}</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <Pagination :meta="paginationMeta" />
            </div>

            <!-- Add Reward Modal -->
            <Modal :show="showRewardModal" @close="closeRewardModal" :title="editingRewardId ? $t('loyalty.edit_reward') : $t('loyalty.add_new_reward')" size="lg">
                <form @submit.prevent="submitReward" class="space-y-6">
                    <!-- Reward Name (Multilingual) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input 
                            v-model="rewardForm.name.en"
                            :label="$t('common.name_en')"
                            placeholder="e.g. Free Coffee"
                            required
                            :error="(rewardForm.errors as any)['name.en']"
                        />
                        <Input 
                            v-model="rewardForm.name.ar"
                            :label="$t('common.name_ar')"
                            placeholder="e.g. قهوة مجانية"
                            class="text-right"
                            dir="rtl"
                            :error="(rewardForm.errors as any)['name.ar']"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('common.description') }}</label>
                        <textarea 
                            v-model="rewardForm.description"
                            rows="2"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            :placeholder="$t('common.description') + '...'"
                        ></textarea>
                    </div>

                    <!-- Points & Min Value -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input 
                            v-model="rewardForm.points_required"
                            :label="$t('loyalty.points_required')"
                            type="number"
                            min="1"
                            placeholder="e.g. 100"
                            required
                            :error="rewardForm.errors.points_required"
                        />
                        <Input 
                            v-model="rewardForm.min_order_value"
                            :label="$t('loyalty.min_order_value') + ' (' + currency + ')'"
                            type="number"
                            min="0"
                            step="0.01"
                            :placeholder="$t('common.optional')"
                            :error="rewardForm.errors.min_order_value"
                        />
                    </div>

                    <!-- Reward Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('loyalty.reward_type') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label 
                                v-for="type in rewardTypes" 
                                :key="type.value"
                                class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-all"
                                :class="rewardForm.reward_type === type.value ? 'border-primary ring-1 ring-primary bg-primary/5' : 'border-gray-200'"
                            >
                                <input 
                                    type="radio" 
                                    name="reward_type"
                                    :value="type.value"
                                    v-model="rewardForm.reward_type"
                                    class="h-4 w-4 text-primary border-gray-300 focus:ring-primary"
                                >
                                <span class="ml-3 block text-sm font-medium text-gray-900">{{ type.label }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Dynamic Fields based on Type -->
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                        
                        <!-- Scope Selection -->
                        <div v-if="['discount_percentage', 'discount_fixed', 'free_item'].includes(rewardForm.reward_type)">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('loyalty.apply_on') }}</label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" v-model="rewardForm.apply_on" value="all" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">{{ $t('loyalty.whole_menu') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" v-model="rewardForm.apply_on" value="specific" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">{{ $t('loyalty.specific_items') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Item Selection Trigger -->
                        <div v-if="rewardForm.apply_on === 'specific' && ['discount_percentage', 'discount_fixed', 'free_item'].includes(rewardForm.reward_type)">
                             <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('loyalty.select_items') }}</label>
                             <button 
                                type="button" 
                                @click="showItemModal = true"
                                class="w-full py-2.5 px-4 border border-gray-300 rounded-xl text-left text-sm text-gray-700 hover:bg-white bg-white shadow-sm flex justify-between items-center transition-all"
                             >
                                <span v-if="rewardForm.menu_item_ids.length === 0" class="text-gray-400">{{ $t('loyalty.choose_items') }}</span>
                                <span v-else class="font-medium text-primary">{{ $t('loyalty.items_selected', { count: rewardForm.menu_item_ids.length }) }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                             </button>
                        </div>

                        <!-- Values -->
                        <div v-if="rewardForm.reward_type === 'discount_percentage'">
                            <Input 
                                v-model="rewardForm.discount_value"
                                :label="$t('loyalty.discount_value_percent')"
                                type="number"
                                min="1"
                                max="100"
                                placeholder="e.g. 20"
                                required
                                suffix="%"
                                :error="rewardForm.errors.discount_value"
                            />
                             <p class="mt-1 text-xs text-gray-500">{{ $t('loyalty.percent_deduct') }}</p>
                        </div>

                        <div v-else-if="rewardForm.reward_type === 'discount_fixed'">
                             <Input 
                                v-model="rewardForm.discount_value"
                                :label="$t('loyalty.discount_value_amount')"
                                type="number"
                                min="1"
                                placeholder="e.g. 50"
                                required
                                :prefix="currency"
                                :error="rewardForm.errors.discount_value"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ $t('loyalty.fixed_deduct') }}</p>
                        </div>

                        <div v-else-if="rewardForm.reward_type === 'cashback'">
                            <Input 
                                v-model="rewardForm.discount_value"
                                :label="$t('loyalty.cashback_percent')"
                                type="number"
                                min="1"
                                max="100"
                                placeholder="e.g. 10"
                                required
                                suffix="%"
                                :error="rewardForm.errors.discount_value"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ $t('loyalty.percent_returned') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="secondary" @click="closeRewardModal">{{ $t('common.cancel') }}</Button>
                        <Button type="submit" :loading="rewardForm.processing">{{ $t('loyalty.add_reward') }}</Button>
                    </div>
                </form>
            </Modal>

             <!-- Design Reward Modal -->
            <Modal :show="showDesignModal" @close="showDesignModal = false" :title="`${$t('loyalty.customize_design')}: ${getLocaleName(selectedRewardForDesign?.name) || 'Reward'}`" size="6xl">
                 <CardDesigner 
                    v-if="selectedRewardForDesign"
                    mode="reward" 
                    :reward="selectedRewardForDesign" 
                    @success="showDesignModal = false"
                />
            </Modal>

            <!-- Item Selection Modal -->
            <Modal :show="showItemModal" @close="showItemModal = false" :title="$t('loyalty.select_eligible_items')" size="lg">
                <div class="space-y-4">
                    <input 
                        v-model="itemSearch"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    >
                    
                    <div class="h-64 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100">
                        <label 
                            v-for="item in filteredModalItems" 
                            :key="item.id"
                            class="flex items-center p-3 hover:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <input 
                                type="checkbox"
                                :checked="rewardForm.menu_item_ids.includes(item.id)"
                                @change="toggleModalItem(item.id)"
                                class="h-5 w-5 text-primary border-gray-300 rounded focus:ring-primary"
                            >
                            <span class="ml-3 text-sm text-gray-700 font-medium">{{ getLocaleName(item.name) }}</span>
                            <span class="ml-auto text-xs text-gray-400">{{ currency }} {{ item.price }}</span>
                        </label>
                        <div v-if="filteredModalItems.length === 0" class="p-4 text-center text-gray-500 text-sm">
                            {{ $t('loyalty.no_items_found') }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm text-gray-500">{{ $t('loyalty.items_selected', { count: rewardForm.menu_item_ids.length }) }}</span>
                        <Button @click="showItemModal = false">{{ $t('loyalty.done') }}</Button>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import CardDesigner from './CardDesigner.vue'; // New Import
import { usePermissions } from '@/Composables/usePermissions';

const { hasPermission } = usePermissions();

const props = withDefaults(defineProps<{
    customers: any;
    rewards: any[];
    menuItems: any[];
    settings?: any;
    earningMethod?: any;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    customers: () => ({ data: [] }),
    rewards: () => [],
    menuItems: () => [],
    settings: () => ({}),
    earningMethod: () => null,
    filters: () => ({})
});

const activeTab = ref('overview'); // Tab State

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const { locale, t } = useI18n();
const route = (window as any).route;
const showRewardModal = ref(false);
const showDesignModal = ref(false);
const selectedRewardForDesign = ref<any>(null);

const editingRewardId = ref<number | null>(null);

const params = ref({
    search: props.filters?.search || '',
    sort_field: props.filters?.sort_field || 'total_spent',
    sort_direction: props.filters?.sort_direction || 'desc'
});

const paginationMeta = computed(() => {
    return {
        current_page: props.customers.current_page,
        last_page: props.customers.last_page,
        per_page: props.customers.per_page,
        total: props.customers.total,
        from: props.customers.from,
        to: props.customers.to
    };
});

watch(
    () => params.value.search,
    debounce((value: string) => {
        router.get(route('loyalty.index'), { ...params.value, search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300)
);

const sort = (field: string) => {
    params.value.sort_field = field;
    params.value.sort_direction = params.value.sort_direction === 'asc' ? 'desc' : 'asc';
    
    router.get(route('loyalty.index'), params.value, {
        preserveState: true,
        replace: true
    });
};

const showItemModal = ref(false);
const itemSearch = ref('');

const filteredModalItems = computed(() => {
    if (!itemSearch.value) return props.menuItems;
    const q = itemSearch.value.toLowerCase();
    return props.menuItems.filter(i => 
        getLocaleName(i.name).toLowerCase().includes(q)
    );
});

const toggleModalItem = (id: number) => {
    const index = rewardForm.menu_item_ids.indexOf(id);
    if (index === -1) {
        rewardForm.menu_item_ids.push(id);
    } else {
        rewardForm.menu_item_ids.splice(index, 1);
    }
};

const rewardTypes = computed(() => [
    { value: 'discount_percentage', label: t('loyalty.discount_percentage') },
    { value: 'discount_fixed', label: t('loyalty.discount_fixed') },
    { value: 'free_item', label: t('loyalty.free_item') },
    { value: 'cashback', label: t('loyalty.cashback') }
]);

const rewardForm = useForm({
    name: { en: '', ar: '' },
    description: '',
    points_required: '',
    min_order_value: '',
    reward_type: 'discount_percentage',
    discount_value: '',
    menu_item_ids: [] as number[],
    apply_on: 'all',
    is_active: true
});

const getLocaleName = (name: any) => {
    if (!name) return t('common.unknown') || 'Unknown';
    if (typeof name === 'string') return name;
    return name[locale.value] || Object.values(name)[0] || '';
};

const formatRewardType = (type: string) => {
    return rewardTypes.value.find(t => t.value === type)?.label || type;
};

const formatRewardValue = (reward: any) => {
    switch (reward.reward_type) {
        case 'discount_percentage':
            return `${reward.discount_value}% ${t('loyalty.off')}`;
        case 'discount_fixed':
            return `${currency.value} ${reward.discount_value} ${t('loyalty.off')}`;
        case 'free_item':
            return t('loyalty.free_item_label');
        case 'cashback':
            return `${reward.discount_value}% ${t('loyalty.cashback')}`;
        default:
            return '';
    }
};

const closeRewardModal = () => {
    showRewardModal.value = false;
    rewardForm.reset();
    rewardForm.clearErrors();
    editingRewardId.value = null;
    rewardForm.apply_on = 'all';
};

const openEditModal = (reward: any) => {
    editingRewardId.value = reward.id;
    rewardForm.name = reward.name;
    rewardForm.description = reward.description || '';
    rewardForm.points_required = reward.points_required;
    rewardForm.min_order_value = reward.min_order_value || '';
    rewardForm.reward_type = reward.reward_type;
    rewardForm.discount_value = reward.discount_value;
    
    // Map existing items
    rewardForm.menu_item_ids = reward.menu_items ? reward.menu_items.map((i:any) => i.id) : [];
    rewardForm.apply_on = rewardForm.menu_item_ids.length > 0 ? 'specific' : 'all';
    
    rewardForm.is_active = reward.is_active;
    showRewardModal.value = true;
};

const openDesignModal = (reward: any) => {
    selectedRewardForDesign.value = reward;
    showDesignModal.value = true;
};

const submitReward = () => {
    if (editingRewardId.value) {
        rewardForm.put(route('loyalty.rewards.update', editingRewardId.value), {
            onSuccess: () => closeRewardModal()
        });
    } else {
        rewardForm.post(route('loyalty.rewards.store'), {
            onSuccess: () => closeRewardModal()
        });
    }
};

const deleteReward = (reward: any) => {
    if (confirm(t('loyalty.confirm_delete_reward'))) {
        router.delete(route('loyalty.rewards.delete', reward.id));
    }
};
</script>
