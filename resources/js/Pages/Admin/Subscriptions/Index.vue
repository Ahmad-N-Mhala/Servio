<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Restaurant Subscriptions
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Data Table -->
                <Table
                    :columns="columns"
                    :data="restaurants.data"
                    :pagination="restaurants"
                    v-model:search="search"
                    title="Manage Restaurant Subscriptions"
                >
                    <!-- Restaurant Column -->
                    <template #cell-restaurant="{ row }">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ row.name }}</div>
                            <div class="text-xs text-gray-500">{{ row.email }}</div>
                        </div>
                    </template>

                    <!-- Plan Column -->
                    <template #cell-subscription.plan.name="{ row }">
                        <div v-if="row.subscription" class="text-sm text-gray-900 font-medium">
                            {{ row.subscription.plan?.name || 'N/A' }}
                        </div>
                        <span v-else class="text-xs text-gray-400 italic">No subscription</span>
                    </template>

                    <!-- Actions Column -->
                    <template #actions="{ row }">
                        <button 
                            @click="openEditModal(row)"
                            class="text-primary hover:text-primary/80 text-xs font-medium px-2 py-1 rounded hover:bg-primary/5 transition-colors mr-2"
                        >
                            {{ row.subscription ? 'Update' : 'Assign' }}
                        </button>
                        <button 
                            v-if="row.subscription"
                            @click="deleteSubscription(row.subscription.id)"
                            class="text-red-600 hover:text-red-900 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
                        >
                            Remove
                        </button>
                    </template>
                </Table>
            </div>
        </div>

        <!-- Edit/Assign Modal remains same -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal"> 
            <!-- ... modal content ... -->
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ selectedRestaurant?.subscription ? 'Update' : 'Assign' }} Subscription
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Restaurant: <span class="font-medium text-gray-900">{{ selectedRestaurant?.name }}</span></p>
                    </div>

                    <form @submit.prevent="saveSubscription">
                        <!-- Plan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subscription Plan *</label>
                            <select v-model="form.plan_id" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="">Select Plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }} - ${{ plan.price_monthly }}/month
                                </option>
                            </select>
                            <div v-if="form.errors.plan_id" class="text-red-600 text-sm mt-1">{{ form.errors.plan_id }}</div>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select v-model="form.status" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                {{ selectedRestaurant?.subscription ? 'Update' : 'Assign' }} Subscription
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
    { key: 'subscription.plan.name', label: 'Current Plan', sortable: false },
    { key: 'subscription.starts_at', label: 'Start Date', sortable: true, format: 'date' as const },
    { key: 'subscription.ends_at', label: 'End Date', sortable: true, format: 'date' as const },
    { key: 'subscription.status', label: 'Status', sortable: true, format: 'badge' as const },
];

const props = defineProps<{
    restaurants: {
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
const selectedRestaurant = ref<any>(null);

const form = useForm({
    restaurant_id: '',
    plan_id: '',
    starts_at: new Date().toISOString().split('T')[0],
    ends_at: '',
    status: 'active',
});

const openEditModal = (restaurant: any) => {
    selectedRestaurant.value = restaurant;
    form.restaurant_id = restaurant.id;
    
    if (restaurant.subscription) {
        // Update existing subscription
        form.plan_id = restaurant.subscription.plan_id;
        form.starts_at = restaurant.subscription.starts_at?.split('T')[0] || '';
        form.ends_at = restaurant.subscription.ends_at?.split('T')[0] || '';
        form.status = restaurant.subscription.status;
    } else {
        // New subscription
        form.plan_id = '';
        form.starts_at = new Date().toISOString().split('T')[0];
        form.ends_at = '';
        form.status = 'active';
    }
    
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedRestaurant.value = null;
    form.reset();
};

const saveSubscription = () => {
    if (selectedRestaurant.value?.subscription) {
        // Update existing
        form.put(route('admin.subscriptions.update', selectedRestaurant.value.subscription.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        // Create new
        form.post(route('admin.subscriptions.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSubscription = (id: number) => {
    if (confirm('Are you sure you want to remove this subscription?')) {
        router.delete(route('admin.subscriptions.destroy', id));
    }
};
</script>
