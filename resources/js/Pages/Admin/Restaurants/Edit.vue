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
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ $t('common.name') }}</label>
                                    <input v-model="form.name" type="text" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input v-model="form.slug" type="text" id="slug" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                                    <input v-model="form.email" type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">
                                        Phone Number
                                        <span class="text-xs text-gray-500">- Current</span>
                                    </label>
                                    <input 
                                        v-model="form.phone" 
                                        type="text" 
                                        id="phone" 
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm cursor-not-allowed"
                                        readonly
                                        disabled
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Current phone number on file</p>
                                </div>

                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                    <input v-model="form.currency" type="text" id="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="AED" required>
                                    <div v-if="form.errors.currency" class="text-red-500 text-xs mt-1">{{ form.errors.currency }}</div>
                                </div>
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">{{ $t('common.status') }}</label>
                                    <select v-model="form.status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="active">{{ $t('common.active') }}</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Service Type</label>
                                    <select v-model="form.service_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="both">Both (Table Service & Self Service)</option>
                                        <option value="self_service">Self Service Only (Counter/Kiosk)</option>
                                        <option value="table_service">Table Service Only (Waiter)</option>
                                    </select>
                                    <div v-if="form.errors.service_type" class="text-red-500 text-xs mt-1">{{ form.errors.service_type }}</div>
                                    <p class="text-xs text-gray-500 mt-1" v-if="form.service_type === 'self_service'">Hides waiter UI, enables pickup screen.</p>
                                    <p class="text-xs text-gray-500 mt-1" v-if="form.service_type === 'table_service'">Hides public status screen.</p>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700">Street Name / Address</label>
                                        <input v-model="form.address" type="text" id="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                                    </div>
                                    
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                        <select v-model="form.country" id="country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="" disabled>Select Country</option>
                                            <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                                        </select>
                                        <div v-if="form.errors.country" class="text-red-500 text-xs mt-1">{{ form.errors.country }}</div>
                                    </div>

                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State / Province</label>
                                        <input v-model="form.state" type="text" id="state" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.state" class="text-red-500 text-xs mt-1">{{ form.errors.state }}</div>
                                    </div>

                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input v-model="form.city" type="text" id="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.city" class="text-red-500 text-xs mt-1">{{ form.errors.city }}</div>
                                    </div>

                                    <div>
                                        <label for="zip_code" class="block text-sm font-medium text-gray-700">Zip / Postal Code</label>
                                        <input v-model="form.zip_code" type="text" id="zip_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div v-if="form.errors.zip_code" class="text-red-500 text-xs mt-1">{{ form.errors.zip_code }}</div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="google_map_location" class="block text-sm font-medium text-gray-700">Google Map Location (Optional)</label>
                                        <input v-model="form.google_map_location" type="url" id="google_map_location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="https://maps.google.com/...">
                                        <div v-if="form.errors.google_map_location" class="text-red-500 text-xs mt-1">{{ form.errors.google_map_location }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Owner Account Management -->
                            <div class="border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Owner Account Management</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Display Current Owner Email -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Current Owner Email
                                        </label>
                                        <div class="mt-1 p-2 bg-gray-100 rounded-md border border-gray-300 text-gray-600 sm:text-sm">
                                            {{ props.restaurant.owner_email }}
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">To change this email, use the field below.</p>
                                    </div>
                                    
                                     <div>
                                        <label for="new_owner_name" class="block text-sm font-medium text-gray-700">
                                            Owner Name
                                            <span class="text-gray-500 font-normal">(Leave blank to keep current)</span>
                                        </label>
                                        <input 
                                            v-model="form.new_owner_name" 
                                            type="text" 
                                            id="new_owner_name" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Enter new name"
                                        >
                                        <div v-if="(form.errors as any).new_owner_name" class="text-red-500 text-xs mt-1">{{ (form.errors as any).new_owner_name }}</div>
                                    </div>
                                    
                                     <div>
                                        <label for="new_owner_phone" class="block text-sm font-medium text-gray-700">
                                            Owner Phone
                                            <span class="text-gray-500 font-normal">(Leave blank to keep current)</span>
                                        </label>
                                        <input 
                                            v-model="form.new_owner_phone" 
                                            type="text" 
                                            id="new_owner_phone" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Enter new phone"
                                        >
                                        <div v-if="(form.errors as any).new_owner_phone" class="text-red-500 text-xs mt-1">{{ (form.errors as any).new_owner_phone }}</div>
                                    </div>

                                    <div>
                                        <label for="new_owner_email" class="block text-sm font-medium text-gray-700">
                                            Owner Email
                                            <span class="text-gray-500 font-normal">(Leave blank to keep current)</span>
                                        </label>
                                        <input 
                                            v-model="form.new_owner_email" 
                                            type="email" 
                                            id="new_owner_email" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Enter new email to change"
                                        >
                                        <div v-if="(form.errors as any).new_owner_email" class="text-red-500 text-xs mt-1">{{ (form.errors as any).new_owner_email }}</div>
                                        <p class="text-xs text-gray-500 mt-1">⚠️ This will update the owner's login email</p>
                                    </div>

                                    <div>
                                        <label for="new_owner_password" class="block text-sm font-medium text-gray-700">
                                            Owner Password
                                            <span class="text-gray-500 font-normal">(Leave blank to keep current)</span>
                                        </label>
                                        <input 
                                            v-model="form.new_owner_password" 
                                            type="text" 
                                            id="new_owner_password" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                            placeholder="Enter new password"
                                        >
                                        <div v-if="(form.errors as any).new_owner_password" class="text-red-500 text-xs mt-1">{{ (form.errors as any).new_owner_password }}</div>
                                        <p class="text-xs text-gray-500 mt-1">💡 Minimum 8 characters recommended. Current password cannot be viewed.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loyalty Program Setup (Aligned with Earning Methods) -->
                            <div class="border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Loyalty Program Setup</h3>
                                
                                <!-- Name (Multilingual) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="method_name_en" class="block text-sm font-medium text-gray-700">Method Name (English)</label>
                                        <input 
                                            v-model="form.earning_method_name_en" 
                                            type="text" 
                                            id="method_name_en" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="e.g. Loyalty Points"
                                        >
                                    </div>
                                    <div>
                                        <label for="method_name_ar" class="block text-sm font-medium text-gray-700">Method Name (Arabic)</label>
                                        <input 
                                            v-model="form.earning_method_name_ar" 
                                            type="text" 
                                            id="method_name_ar" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right"
                                            dir="rtl"
                                            placeholder="نقاط الولاء"
                                        >
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="method_description" class="block text-sm font-medium text-gray-700">{{ $t('common.description') }}</label>
                                    <textarea 
                                        v-model="form.earning_method_description"
                                        id="method_description"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Brief description of how this method works..."
                                    ></textarea>
                                </div>

                                <!-- Type Selection -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Earning Type</label>
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
                                                    <div class="font-bold text-gray-900">Per Order Amount</div>
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
                                </div>

                                <!-- Points Configuration -->
                                <div class="bg-gray-50 p-5 rounded-xl border border-gray-100 space-y-4 mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900">Points Configuration</h4>
                                    
                                    <!-- For Order Total Type -->
                                    <div v-if="form.earning_method_type === 'order_total'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Earn Points</label>
                                            <input 
                                                v-model="form.earning_points" 
                                                type="number" 
                                                min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">For Every ({{ form.currency || 'Currency Unit' }})</label>
                                            <input 
                                                v-model="form.earning_currency_amount" 
                                                type="number" 
                                                min="0.01"
                                                step="0.01"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                        </div>
                                    </div>

                                    <!-- For Visit Type -->
                                    <div v-else>
                                        <label class="block text-sm font-medium text-gray-700">Points per Visit</label>
                                        <input 
                                            v-model="form.earning_points" 
                                            type="number" 
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>

                                    <!-- Conditions -->
                                    <div class="pt-4 mt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Min Spent Amount (Optional)</label>
                                            <input 
                                                v-model="form.earning_min_spent" 
                                                type="number" 
                                                min="0"
                                                step="0.01"
                                                placeholder="Optional"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                        </div>
                                        <div v-if="form.earning_method_type === 'order_total'">
                                            <label class="block text-sm font-medium text-gray-700">Max Points Cap (Optional)</label>
                                            <input 
                                                v-model="form.earning_max_points" 
                                                type="number" 
                                                min="1"
                                                placeholder="Optional"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Status -->
                                <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex items-center h-5">
                                        <input 
                                            type="checkbox" 
                                            v-model="form.earning_is_active"
                                            id="earning_is_active"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 cursor-pointer"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="earning_is_active" class="font-medium text-gray-700 cursor-pointer">Active Status</label>
                                        <p class="text-gray-500">Enable or disable this earning method.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('admin.restaurants.index')" class="text-gray-600 hover:text-gray-900 mr-4">{{ $t('common.cancel') }}</Link>
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
        owner_email?: string;
        phone: string | null;
        currency: string;
        service_type?: string;
        status: string;
        address: string | null;
        city: string | null;
        country: string | null;
        state: string | null;
        zip_code: string | null;
        google_map_location?: string | null;
        earning_method_type?: string; 
        earning_points?: number;
        earning_method_name_en?: string;
        earning_method_name_ar?: string;
        earning_method_description?: string;
        earning_currency_amount?: number;
        earning_min_spent?: number;
        earning_max_points?: number;
        earning_is_active?: boolean;
    };
}>();

const countries = [
    'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman',
    'United States', 'United Kingdom', 'Canada', 'Australia', 'India', 'Pakistan',
    'Egypt', 'Jordan', 'Lebanon', 'Germany', 'France', 'Italy', 'Spain'
];

const form = useForm({
    name: props.restaurant.name,
    slug: props.restaurant.slug,
    email: props.restaurant.email,
    phone: props.restaurant.phone,
    currency: props.restaurant.currency,
    service_type: props.restaurant.service_type || 'both',
    status: props.restaurant.status || 'active',
    address: props.restaurant.address,
    city: props.restaurant.city,
    country: props.restaurant.country || '',
    state: props.restaurant.state,
    zip_code: props.restaurant.zip_code,
    google_map_location: props.restaurant.google_map_location || '',

    // Loyalty/Earning Method fields - comprehensive
    earning_method_type: props.restaurant.earning_method_type || 'order_total',
    earning_points: props.restaurant.earning_points || 1,
    earning_method_name_en: props.restaurant.earning_method_name_en || 'Loyalty Points',
    earning_method_name_ar: props.restaurant.earning_method_name_ar || 'نقاط الولاء',
    earning_method_description: props.restaurant.earning_method_description || '',
    earning_currency_amount: props.restaurant.earning_currency_amount || 1,
    earning_min_spent: props.restaurant.earning_min_spent || null,
    earning_max_points: props.restaurant.earning_max_points || null,
    earning_is_active: props.restaurant.earning_is_active !== undefined ? props.restaurant.earning_is_active : true,
    // New owner management fields
    new_owner_name: '',
    new_owner_email: '',
    new_owner_phone: '',
    new_owner_password: '',
});

const route = (window as any).route;

const submit = () => {
    form.put(route('admin.restaurants.update', props.restaurant.id));
};
</script>

