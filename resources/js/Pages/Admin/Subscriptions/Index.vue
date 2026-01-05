<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                All Subscriptions
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Data Table -->
                <Table
                    :columns="columns"
                    :data="subscriptions.data"
                    :pagination="subscriptions"
                    v-model:search="search"
                    title="Subscription History"
                >
                    <!-- Restaurant Column -->
                    <template #cell-restaurant="{ row }">
                        <div v-if="row.restaurant" class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">{{ row.restaurant.name }}</span>
                            <span class="text-xs text-gray-500">{{ row.restaurant.email }}</span>
                        </div>
                        <div v-else class="text-xs text-red-500 italic">Deleted Restaurant</div>
                    </template>

                    <!-- Plan Column -->
                    <template #cell-plan="{ row }">
                        <div v-if="row.plan" class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">{{ row.plan.name }}</span>
                            <span class="text-xs text-gray-500">{{ row.plan.description }}</span>
                        </div>
                        <span v-else class="text-xs text-gray-400 italic">Unknown Plan</span>
                    </template>

                    <!-- Price Column -->
                    <template #cell-price="{ row }">
                        <div v-if="row.plan">
                           <span class="font-mono text-sm">
                                ${{ row.billing_cycle === 'yearly' ? row.plan.price_yearly : row.plan.price_monthly }}
                           </span>
                           <span class="text-xs text-gray-500 block">/ {{ row.billing_cycle }}</span>
                        </div>
                    </template>

                    <!-- Status Column -->
                    <template #cell-status="{ row }">
                        <span 
                            class="px-2 py-1 rounded text-xs font-bold capitalize"
                            :class="{
                                'bg-green-100 text-green-700': row.status === 'active',
                                'bg-red-100 text-red-700': row.status === 'expired' || row.status === 'cancelled',
                                'bg-gray-100 text-gray-700': !row.status
                            }"
                        >
                            {{ row.status || 'Unknown' }}
                        </span>
                    </template>

                    <!-- Actions Column -->
                    <template #actions="{ row }">
                        <button 
                            @click="openEditModal(row)"
                            class="text-primary hover:text-primary/80 text-xs font-medium px-2 py-1 rounded hover:bg-primary/5 transition-colors mr-2"
                        >{{ $t('common.edit') }}</button>
                        <button 
                            @click="deleteSubscription(row.id)"
                            class="text-red-600 hover:text-red-900 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
                        >{{ $t('common.delete') }}</button>
                    </template>
                </Table>
            </div>
        </div>

        <!-- Edit Subscription Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal"> 
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Edit Subscription
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4" v-if="selectedSubscription?.restaurant">
                        <p class="text-sm text-gray-600">Restaurant: <span class="font-medium text-gray-900">{{ selectedSubscription.restaurant.name }}</span></p>
                    </div>

                    <form @submit.prevent="saveSubscription">
                        <!-- Plan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subscription Plan</label>
                            <select v-model="form.plan_id" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="">Select Plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.plan_id" class="text-red-600 text-sm mt-1">{{ form.errors.plan_id }}</div>
                        </div>

                         <!-- Billing Cycle -->
                         <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
                            <select v-model="form.billing_cycle" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input v-model="form.starts_at" type="date" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <div v-if="form.errors.starts_at" class="text-red-600 text-sm mt-1">{{ form.errors.starts_at }}</div>
                        </div>

                        <!-- End Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
                            <input v-model="form.ends_at" type="date" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <p class="text-xs text-gray-500 mt-1">Leave blank for ongoing subscription</p>
                            <div v-if="form.errors.ends_at" class="text-red-600 text-sm mt-1">{{ form.errors.ends_at }}</div>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('common.status') }}</label>
                            <select v-model="form.status" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="active">{{ $t('common.active') }}</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                Update Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Table from '@/Components/Table.vue';
import { debounce } from 'lodash';

const columns = [
    { key: 'restaurant', label: 'Restaurant', sortable: false },
    { key: 'plan', label: 'Plan Details', sortable: false },
    { key: 'price', label: 'Price', sortable: false },
    { key: 'starts_at', label: 'Started', sortable: true, format: 'date' as const },
    { key: 'ends_at', label: 'Expires', sortable: true, format: 'date' as const },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'actions', label: '', sortable: false },
];

const props = defineProps<{
    subscriptions: {
        data: Array<any>;
        from: number;
        to: number;
        total: number;
        links: Array<any>;
    };
    plans: Array<any>;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search || '');

watch(search, debounce((value: string) => {
    router.get(route('admin.subscriptions.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const route = (window as any).route;

const showModal = ref(false);
const selectedSubscription = ref<any>(null);

const form = useForm({
    restaurant_id: '',
    plan_id: '',
    billing_cycle: 'monthly',
    starts_at: '',
    ends_at: '',
    status: 'active',
});

const openEditModal = (subscription: any) => {
    selectedSubscription.value = subscription;
    form.restaurant_id = subscription.restaurant_id;
    form.plan_id = subscription.plan_id;
    form.billing_cycle = subscription.billing_cycle || 'monthly';
    form.starts_at = subscription.starts_at ? subscription.starts_at.split('T')[0] : '';
    form.ends_at = subscription.ends_at ? subscription.ends_at.split('T')[0] : '';
    form.status = subscription.status;
    
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedSubscription.value = null;
    form.reset();
};

const saveSubscription = () => {
    if (selectedSubscription.value) {
        form.put(route('admin.subscriptions.update', selectedSubscription.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSubscription = (id: number) => {
    if (confirm('Are you sure you want to delete this subscription record?')) {
        router.delete(route('admin.subscriptions.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>
