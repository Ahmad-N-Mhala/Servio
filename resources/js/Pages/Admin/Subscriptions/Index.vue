<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Restaurant Subscriptions
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Manage Restaurant Subscriptions</h3>
                    <p class="mt-1 text-sm text-gray-600">View and update subscription plans for all restaurants</p>
                </div>

                <!-- Restaurants Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Restaurant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="restaurant in restaurants.data" :key="restaurant.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ restaurant.name }}</div>
                                    <div class="text-sm text-gray-500">{{ restaurant.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="restaurant.subscription" class="text-sm text-gray-900">
                                        {{ restaurant.subscription.plan?.name || 'N/A' }}
                                    </div>
                                    <span v-else class="text-sm text-gray-400 italic">No subscription</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ restaurant.subscription?.starts_at ? formatDate(restaurant.subscription.starts_at) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ restaurant.subscription?.ends_at ? formatDate(restaurant.subscription.ends_at) : 'No end date' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="restaurant.subscription" :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        restaurant.subscription.status === 'active' ? 'bg-green-100 text-green-800' : 
                                        restaurant.subscription.status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                        'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ restaurant.subscription.status }}
                                    </span>
                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                        None
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        @click="openEditModal(restaurant)"
                                        class="text-primary hover:text-primary/80 mr-3"
                                    >
                                        {{ restaurant.subscription ? 'Update' : 'Assign' }}
                                    </button>
                                    <button 
                                        v-if="restaurant.subscription"
                                        @click="deleteSubscription(restaurant.subscription.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="restaurants.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <p>No restaurants found.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="restaurants.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ restaurants.from }} to {{ restaurants.to }} of {{ restaurants.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link 
                                    v-for="link in restaurants.links" 
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1 rounded-md text-sm',
                                        link.active ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit/Assign Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
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
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    restaurants: {
        data: Array<any>;
        from: number;
        to: number;
        total: number;
        links: Array<any>;
    };
    plans: Array<any>;
}>();

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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

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
