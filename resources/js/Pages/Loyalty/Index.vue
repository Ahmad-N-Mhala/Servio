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
                    <StatsCard
                        :title="$t('loyalty.total_members')"
                        :value="stats.total_members"
                        icon="customers"
                        color="purple"
                    />
                    <StatsCard
                        :title="$t('loyalty.active_rewards')"
                        :value="stats.active_rewards"
                        icon="gift"
                        color="blue"
                    />
                    <StatsCard
                        :title="$t('loyalty.redemptions')"
                        :value="stats.total_redemptions"
                        icon="orders"
                        color="green"
                    />
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
                            <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ reward.description || $t('common.no_description') }}</p>
                            
                            <!-- Applied On details -->
                            <div class="text-xs mb-4">
                                <span class="text-gray-500">{{ $t('loyalty.apply_on') }}: </span>
                                <span v-if="reward.menu_items && reward.menu_items.length > 0" class="font-medium text-primary">
                                    {{ reward.menu_items.map((i: any) => getLocaleName(i.name)).join(', ') }}
                                </span>
                                <span v-else class="font-medium text-gray-700">
                                    {{ $t('loyalty.whole_menu') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ formatRewardType(reward.reward_type) }}
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

            <Table
                :title="$t('loyalty.members')"
                v-model:search="params.search"
                :columns="customerColumns"
                :data="customers.data"
                :pagination="paginationMeta"
                :currency="currency"
                serverSide
                @sort="handleSort"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-bold">
                            {{ row.name ? row.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ row.name || $t('common.unknown') }}</div>
                            <div class="text-xs text-gray-500">{{ $t('loyalty.member_since') }} {{ new Date(row.created_at).toLocaleDateString() }}</div>
                        </div>
                    </div>
                </template>
                <template #cell-phone="{ row }">
                    <div class="text-sm text-gray-900">{{ row.phone }}</div>
                    <div class="text-xs text-gray-500">{{ row.email || '-' }}</div>
                </template>
                <template #cell-points_balance="{ row }">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        {{ row.loyalty_points?.balance || 0 }} {{ $t('loyalty.points') }}
                    </span>
                </template>
                <template #cell-total_spent="{ row }">
                    {{ currency }} {{ row.total_spent || '0.00' }}
                </template>
                <template #actions="{ row }">
                    <Link :href="route('loyalty.customers.show', row.id)" class="text-primary hover:text-primary-hover font-bold">
                        {{ $t('loyalty.view_details') }}
                    </Link>
                </template>
            </Table>

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
                    
                    <div class="h-96 overflow-y-auto border border-gray-200 rounded-xl px-2 py-2">
                        <div v-for="category in filteredCategories" :key="category.id" class="mb-5 last:mb-0">
                            <!-- Category Header -->
                             <h4 class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm px-3 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 rounded-lg mb-2 border border-gray-100 shadow-sm">
                                {{ getLocaleName(category.name) }}
                            </h4>

                            <!-- Items Grid -->
                            <div class="space-y-1">
                                <div 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    @click="toggleModalItem(item.id)"
                                    class="flex items-center p-3 cursor-pointer transition-all rounded-lg border group"
                                    :class="rewardForm.menu_item_ids.includes(item.id) 
                                        ? 'bg-primary/5 border-primary ring-1 ring-primary/20' 
                                        : 'bg-white border-transparent hover:bg-gray-50 hover:border-gray-100'"
                                >
                                    <div class="relative flex items-center justify-center h-5 w-5 rounded-full border transition-colors"
                                        :class="rewardForm.menu_item_ids.includes(item.id) ? 'border-primary bg-primary' : 'border-gray-300 bg-white'"
                                    >
                                        <div v-if="rewardForm.menu_item_ids.includes(item.id)" class="h-2 w-2 rounded-full bg-white"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium transition-colors"
                                        :class="rewardForm.menu_item_ids.includes(item.id) ? 'text-primary' : 'text-gray-700 group-hover:text-gray-900'"
                                    >{{ getLocaleName(item.name) }}</span>
                                    <span class="ml-auto text-xs font-mono px-2 py-1 rounded"
                                         :class="rewardForm.menu_item_ids.includes(item.id) ? 'text-primary bg-primary/10' : 'text-gray-400 bg-gray-50'"
                                    >{{ currency }} {{ item.price }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="filteredCategories.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 gap-2">
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span class="text-sm font-medium">{{ $t('loyalty.no_items_found') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm text-gray-500">
                            <span v-if="rewardForm.menu_item_ids.length > 0">{{ $t('loyalty.item_selected') }}</span>
                            <span v-else>{{ $t('loyalty.no_item_selected') }}</span>
                        </span>
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
import { useForm, router, usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Table from '@/Components/Table.vue';
import StatsCard from '@/Components/StatsCard.vue';
import CardDesigner from './CardDesigner.vue'; // New Import
import { usePermissions } from '@/Composables/usePermissions';

const { hasPermission } = usePermissions();

const props = withDefaults(defineProps<{
    customers: any;
    rewards: any[];
    menuCategories: any[];
    settings?: any;
    earningMethod?: any;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
    stats: {
        total_members: number;
        active_rewards: number;
        total_redemptions: number;
    };
}>(), {
    customers: () => ({ data: [] }),
    rewards: () => [],
    menuCategories: () => [],
    settings: () => ({}),
    earningMethod: () => null,
    filters: () => ({}),
    stats: () => ({ total_members: 0, active_rewards: 0, total_redemptions: 0 })
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

const customerColumns = [
    { key: 'name', label: t('orders.customer'), sortable: true },
    { key: 'phone', label: t('orders.customer_phone'), sortable: true },
    { key: 'points_balance', label: t('loyalty.points_balance'), sortable: true },
    { key: 'total_spent', label: t('loyalty.total_spent'), sortable: true }
];

const handleSort = (field: string, direction: 'asc' | 'desc') => {
    params.value.sort_field = field;
    params.value.sort_direction = direction;
    
    router.get(route('loyalty.index'), params.value, {
        preserveState: true,
        replace: true
    });
};

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

const filteredCategories = computed(() => {
    if (!itemSearch.value) return props.menuCategories;
    const q = itemSearch.value.toLowerCase();
    
    // Return filtered copy of categories containing matching items
    return props.menuCategories.map((cat: any) => ({
        ...cat,
        items: cat.items.filter((i: any) => 
            getLocaleName(i.name).toLowerCase().includes(q)
        )
    })).filter((cat: any) => cat.items.length > 0);
});

const toggleModalItem = (id: number) => {
    // Single Select Mode
    if (rewardForm.menu_item_ids.includes(id)) {
        rewardForm.menu_item_ids = [];
    } else {
        rewardForm.menu_item_ids = [id];
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
    const value = Math.round(reward.discount_value || 0);

    switch (reward.reward_type) {
        case 'discount_percentage':
            return t('loyalty.discount_percentage_off', { value });
        case 'discount_fixed':
            return t('loyalty.discount_fixed_off', { amount: currency.value + ' ' + value });
        case 'free_item':
            return t('loyalty.free_item');
        case 'cashback':
            return t('loyalty.cashback_back', { value });
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
