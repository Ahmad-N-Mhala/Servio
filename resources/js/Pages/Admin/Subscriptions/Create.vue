<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign Subscription
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <form @submit.prevent="submit">
                        <!-- Restaurant -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant *</label>
                            <select v-model="form.restaurant_id" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="">Select Restaurant</option>
                                <option v-for="restaurant in restaurants" :key="restaurant.id" :value="restaurant.id">
                                    {{ restaurant.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.restaurant_id" class="text-red-600 text-sm mt-1">{{ form.errors.restaurant_id }}</div>
                        </div>

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
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('common.status') }} *</label>
                            <select v-model="form.status" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <option value="active">{{ $t('common.active') }}</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <Link :href="route('admin.subscriptions.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">{{ $t('common.cancel') }}</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                Assign Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    restaurants: Array<any>;
    plans: Array<any>;
}>();

const route = (window as any).route;

const form = useForm({
    restaurant_id: '',
    plan_id: '',
    starts_at: new Date().toISOString().split('T')[0],
    ends_at: '',
    status: 'active',
});

const submit = () => {
    form.post(route('admin.subscriptions.store'));
};
</script>
