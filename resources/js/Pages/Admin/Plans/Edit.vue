<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Subscription Plan</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input v-model="form.name" type="text" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input v-model="form.slug" type="text" id="slug" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="form.description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="price_monthly" class="block text-sm font-medium text-gray-700">Monthly Price ({{ currency }})</label>
                                    <input v-model="form.price_monthly" type="number" step="0.01" id="price_monthly" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.price_monthly" class="text-red-500 text-xs mt-1">{{ form.errors.price_monthly }}</div>
                                </div>

                                <div>
                                    <label for="price_yearly" class="block text-sm font-medium text-gray-700">Yearly Price ({{ currency }})</label>
                                    <input v-model="form.price_yearly" type="number" step="0.01" id="price_yearly" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.price_yearly" class="text-red-500 text-xs mt-1">{{ form.errors.price_yearly }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="max_restaurants" class="block text-sm font-medium text-gray-700">Max Restaurants</label>
                                    <input v-model="form.max_restaurants" type="number" id="max_restaurants" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div v-if="form.errors.max_restaurants" class="text-red-500 text-xs mt-1">{{ form.errors.max_restaurants }}</div>
                                </div>
                                
                                <div>
                                    <label for="max_users" class="block text-sm font-medium text-gray-700">Max Users</label>
                                    <input v-model="form.max_users" type="number" id="max_users" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div v-if="form.errors.max_users" class="text-red-500 text-xs mt-1">{{ form.errors.max_users }}</div>
                                </div>
                                
                                <div>
                                    <label for="max_orders_per_month" class="block text-sm font-medium text-gray-700">Max Orders/Month</label>
                                    <input v-model="form.max_orders_per_month" type="number" id="max_orders_per_month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div v-if="form.errors.max_orders_per_month" class="text-red-500 text-xs mt-1">{{ form.errors.max_orders_per_month }}</div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Plan Features</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <label v-for="(label, key) in availableFeatures" :key="key" class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                type="checkbox" 
                                                :value="key" 
                                                v-model="form.features"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="font-medium text-gray-700">{{ label }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('admin.plans.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</Link>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="form.processing">
                                    Update Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    plan: any;
    availableFeatures: Record<string, string>;
}>();

const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description || '',
    price_monthly: props.plan.price_monthly,
    price_yearly: props.plan.price_yearly,
    max_restaurants: props.plan.max_restaurants,
    max_users: props.plan.max_users,
    max_orders_per_month: props.plan.max_orders_per_month,
    is_active: !!props.plan.is_active,
    features: props.plan.features || [],
});

const route = (window as any).route;

const submit = () => {
    form.put(route('admin.plans.update', props.plan.id));
};
</script>
