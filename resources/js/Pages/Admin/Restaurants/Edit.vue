<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Restaurant</h2>
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

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email (Owner)</label>
                                    <input v-model="form.email" type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input v-model="form.phone" type="text" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                                </div>

                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                    <input v-model="form.currency" type="text" id="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="AED" required>
                                    <div v-if="form.errors.currency" class="text-red-500 text-xs mt-1">{{ form.errors.currency }}</div>
                                </div>
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select v-model="form.status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <input v-model="form.address" type="text" id="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                                    </div>
                                    
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input v-model="form.city" type="text" id="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.city" class="text-red-500 text-xs mt-1">{{ form.errors.city }}</div>
                                    </div>
                                    
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                        <input v-model="form.country" type="text" id="country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.country" class="text-red-500 text-xs mt-1">{{ form.errors.country }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loyalty Setup (Aligned with Onboarding) -->
                            <div class="border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Loyalty Program Setup</h3>
                                <div class="space-y-4">
                                    <label class="block text-sm font-medium text-gray-700">How should customers earn points?</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div 
                                            @click="form.earning_method_type = 'order_total'"
                                            class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                            :class="form.earning_method_type === 'order_total' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-full" :class="form.earning_method_type === 'order_total' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900">Per Spend</div>
                                                    <div class="text-xs text-gray-500">Earn points based on bill total</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div 
                                            @click="form.earning_method_type = 'visit'"
                                            class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                            :class="form.earning_method_type === 'visit' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-full" :class="form.earning_method_type === 'visit' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900">Per Visit</div>
                                                    <div class="text-xs text-gray-500">Fixed points per order/visit</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 bg-gray-50 p-4 rounded-xl">
                                         <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                {{ form.earning_method_type === 'order_total' ? 'Points per 1 ' + form.currency + ' Spent' : 'Points per Visit' }}
                                            </label>
                                            <input 
                                                v-model="form.earning_points" 
                                                type="number" 
                                                min="1" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                required
                                            >
                                            <div v-if="(form.errors as any).earning_points" class="text-red-500 text-xs mt-1">{{ (form.errors as any).earning_points }}</div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">
                                            {{ form.earning_method_type === 'order_total' ? 'Example: If set to 1, a 100 ' + form.currency + ' order earns 100 points.' : 'Example: If set to 10, every visit earns 10 points regardless of spend.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('admin.restaurants.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</Link>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="form.processing">
                                    Update Restaurant
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
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    restaurant: {
        id: number;
        name: string;
        slug: string;
        email: string;
        phone: string | null;
        currency: string;
        status: string;
        address: string | null;
        city: string | null;
        country: string | null;
        earning_method_type?: string; 
        earning_points?: number;
    };
}>();

const form = useForm({
    name: props.restaurant.name,
    slug: props.restaurant.slug,
    email: props.restaurant.email,
    phone: props.restaurant.phone,
    currency: props.restaurant.currency,
    status: props.restaurant.status || 'active',
    address: props.restaurant.address,
    city: props.restaurant.city,
    country: props.restaurant.country,
    // Initialize Loyalty fields. Default to 'order_total' and 1 if not set in DB
    earning_method_type: props.restaurant.earning_method_type || 'order_total',
    earning_points: props.restaurant.earning_points || 1,
});

const route = (window as any).route;

const submit = () => {
    form.put(route('admin.restaurants.update', props.restaurant.id));
};
</script>
