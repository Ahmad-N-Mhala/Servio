<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Restaurant</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Restaurant Info -->
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Restaurant Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Restaurant Name</label>
                                        <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                                        <input v-model="form.slug" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Contact Email (Restaurant)</label>
                                        <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Contact Phone (Restaurant)</label>
                                        <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                                        <input v-model="form.currency" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="AED" required>
                                        <div v-if="form.errors.currency" class="text-red-500 text-xs mt-1">{{ form.errors.currency }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Owner Account -->
                            <div class="pt-2">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Owner Account</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Owner Name</label>
                                        <input v-model="form.owner_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.owner_name" class="text-red-500 text-xs mt-1">{{ form.errors.owner_name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Owner Email (Login)</label>
                                        <input v-model="form.owner_email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.owner_email" class="text-red-500 text-xs mt-1">{{ form.errors.owner_email }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Owner Phone</label>
                                        <input v-model="form.owner_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <div v-if="form.errors.owner_phone" class="text-red-500 text-xs mt-1">{{ form.errors.owner_phone }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Owner Password</label>
                                        <input v-model="form.owner_password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required placeholder="Min 8 characters">
                                        <div v-if="form.errors.owner_password" class="text-red-500 text-xs mt-1">{{ form.errors.owner_password }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="pt-2">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Location Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <input v-model="form.address" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">City</label>
                                        <input v-model="form.city" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">State / Province</label>
                                        <input v-model="form.state" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Country</label>
                                        <input v-model="form.country" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. United Arab Emirates">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Zip / Postal Code</label>
                                        <input v-model="form.zip_code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                     <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Google Map Location (Optional)</label>
                                        <input v-model="form.google_map_location" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="https://maps.google.com/...">
                                        <div v-if="form.errors.google_map_location" class="text-red-500 text-xs mt-1">{{ form.errors.google_map_location }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loyalty -->
                            <div class="pt-2">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Loyalty Setup</h3>
                                <div class="space-y-4">
                                     <label class="block text-sm font-medium text-gray-700">Earning Method</label>
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

                                    <div>
                                         <label class="block text-sm font-medium text-gray-700">Points</label>
                                         <input 
                                            v-model="form.earning_points" 
                                            type="number" 
                                            min="1" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>    

                            <div class="flex items-center justify-end pt-4">
                                <Link :href="route('admin.restaurants.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</Link>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="form.processing">
                                    Create Restaurant
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

const form = useForm({
    name: '',
    slug: '',
    email: '',
    phone: '',
    currency: 'AED',
    
    // Owner Details
    owner_name: '',
    owner_email: '',
    owner_phone: '',
    owner_password: '',
    
    // Location
    address: '',
    city: '',
    state: '',
    zip_code: '',
    country: '',
    google_map_location: '',
    
    // Loyalty
    earning_method_type: 'order_total',
    earning_points: 1,
});

const route = (window as any).route;

const submit = () => {
    form.post(route('admin.restaurants.store'));
};
</script>
