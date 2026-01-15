<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Restaurant</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Restaurant Info Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Restaurant Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Logo Upload -->
                                <div class="md:col-span-2 flex justify-center mb-6">
                                    <div class="relative group cursor-pointer" @click="logoInput?.click()">
                                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 shadow-lg bg-white flex items-center justify-center relative">
                                            <img v-if="logoPreview || props.restaurant.logo" :src="logoPreview || props.restaurant.logo" class="w-full h-full object-cover" />
                                            <div v-else class="text-gray-300">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                            <!-- Overlay -->
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </div>
                                        </div>
                                        <input type="file" ref="logoInput" class="hidden" accept="image/*" @change="handleLogoChange" />
                                    </div>
                                    <div v-if="form.errors.logo" class="text-red-500 text-xs mt-1 text-center w-full">{{ form.errors.logo }}</div>
                                </div>

                                <Input v-model="form.name" label="Restaurant Name" required :error="form.errors.name" />
                                <Input v-model="form.slug" label="Slug (URL Friendly)" required :error="form.errors.slug" />
                                
                                <div>
                                    <Input v-model="form.email" label="Restaurant Email (General)" type="email" required :error="form.errors.email" />
                                    <p class="text-xs text-gray-500 mt-1">General contact email for the business.</p>
                                </div>
                                
                                <div>
                                    <Input v-model="form.notification_email" label="Notification Email (Reminders)" type="email" required :error="form.errors.notification_email" />
                                    <p class="text-xs text-gray-500 mt-1">Receives system alerts, low stock warnings, etc.</p>
                                </div>

                                <PhoneInput 
                                    v-model="form.phone" 
                                    :country="form.country || ''"
                                    label="Contact Phone" 
                                    :error="form.errors.phone" 
                                />

                                <Select 
                                    v-model="form.country" 
                                    label="Country" 
                                    :options="countries.map(c => ({ label: c, value: c }))" 
                                    :error="form.errors.country" 
                                    required
                                />

                                <Input v-model="form.currency" label="Currency" required :error="form.errors.currency" />
                                
                                <Select 
                                    v-model="form.status" 
                                    label="Status" 
                                    :options="[
                                        { label: 'Active', value: 'active' },
                                        { label: 'Suspended', value: 'suspended' }
                                    ]" 
                                    required 
                                    :error="form.errors.status" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Service & Configuration Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Service Configuration
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Select 
                                    v-model="form.service_type" 
                                    label="Service Type" 
                                    :options="[
                                        { label: 'Both (Table & Self Service)', value: 'both' },
                                        { label: 'Table Service Only', value: 'table_service' },
                                        { label: 'Self Service Only', value: 'self_service' }
                                    ]"
                                    required 
                                    :error="form.errors.service_type" 
                                />

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-900">Cash Drawer</label>
                                        <p class="text-xs text-gray-500">Enable if a cash drawer is connected to printer</p>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="form.has_cash_drawer = !form.has_cash_drawer"
                                        :class="form.has_cash_drawer ? 'bg-indigo-600' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                    >
                                        <span class="sr-only">Use setting</span>
                                        <span 
                                            aria-hidden="true" 
                                            :class="form.has_cash_drawer ? 'translate-x-5' : 'translate-x-0'"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Account Management -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Owner Account Management
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Existing Owner Info -->
                                <div class="md:col-span-2">
                                    <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                        <label class="block text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">Current Owner Email</label>
                                        <div class="text-gray-900 font-medium">{{ props.restaurant.owner_email }}</div>
                                    </div>
                                </div>

                                <Input 
                                    v-model="form.new_owner_name" 
                                    label="Change Owner Name"
                                    placeholder="Leave blank to keep current" 
                                    :error="(form.errors as any).new_owner_name"
                                />

                                <PhoneInput 
                                    v-model="form.new_owner_phone" 
                                    :country="form.country || ''"
                                    label="Change Owner Phone"
                                    placeholder="Leave blank to keep current" 
                                    :error="(form.errors as any).new_owner_phone"
                                />

                                <Input 
                                    v-model="form.new_owner_email" 
                                    label="Change Owner Email"
                                    type="email"
                                    placeholder="Leave blank to keep current" 
                                    :error="(form.errors as any).new_owner_email"
                                />

                                <Input 
                                    v-model="form.new_owner_password" 
                                    label="Change Owner Password"
                                    type="text"
                                    placeholder="Leave blank to keep current" 
                                    :error="(form.errors as any).new_owner_password"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Location Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <Input v-model="form.address" label="Street Address" :error="form.errors.address" />
                                </div>
                                <Input v-model="form.city" label="City" :error="form.errors.city" />
                                <Input v-model="form.state" label="State / Province" :error="form.errors.state" />
                                <Input v-model="form.zip_code" label="Zip / Postal Code" :error="form.errors.zip_code" />
                                <div class="md:col-span-2">
                                    <Input v-model="form.google_map_location" label="Google Map Link (Optional)" type="url" placeholder="https://maps.google.com/..." :error="form.errors.google_map_location" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loyalty Setup Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-emerald-100 ring-1 ring-emerald-500/20">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <div class="bg-emerald-100 p-1.5 rounded-lg text-emerald-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                                    </div>
                                    Loyalty Program Setup
                                </h3>
                            </div>
                            
                            <div class="space-y-6">
                                <label class="block text-sm font-bold text-gray-700">How should customers earn points?</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div 
                                        @click="form.earning_method_type = 'order_total'"
                                        class="cursor-pointer border-2 rounded-2xl p-5 hover:border-indigo-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group"
                                        :class="form.earning_method_type === 'order_total' ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-100 bg-white'"
                                    >
                                        <div class="absolute inset-0 bg-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="p-3 rounded-xl transition-colors" :class="form.earning_method_type === 'order_total' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-100 text-gray-400'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-lg">Per Spend</div>
                                            <div class="text-sm text-gray-500 font-medium">Earn points based on bill total</div>
                                        </div>
                                        <div v-if="form.earning_method_type === 'order_total'" class="absolute top-4 right-4 text-indigo-500">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </div>

                                    <div 
                                        @click="form.earning_method_type = 'visit'"
                                        class="cursor-pointer border-2 rounded-2xl p-5 hover:border-indigo-200 transition-all duration-200 flex items-center gap-4 relative overflow-hidden group"
                                        :class="form.earning_method_type === 'visit' ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-100 bg-white'"
                                    >
                                        <div class="absolute inset-0 bg-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="p-3 rounded-xl transition-colors" :class="form.earning_method_type === 'visit' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-100 text-gray-400'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-lg">Per Visit</div>
                                            <div class="text-sm text-gray-500 font-medium">Fixed points per visit</div>
                                        </div>
                                        <div v-if="form.earning_method_type === 'visit'" class="absolute top-4 right-4 text-indigo-500">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-6">
                                    <div v-if="form.earning_method_type === 'order_total'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                                <Input 
                                                v-model="form.earning_points" 
                                                label="Earn Points" 
                                                type="number" 
                                                min="1" 
                                                required 
                                                :error="form.errors.earning_points" 
                                            />
                                        </div>
                                        <div>
                                                <Input 
                                                v-model="form.earning_currency_amount" 
                                                :label="'For Every (' + form.currency + ')'" 
                                                type="number" 
                                                min="0.01"
                                                step="0.01" 
                                                required 
                                                :error="form.errors.earning_currency_amount" 
                                            />
                                        </div>
                                    </div>

                                    <div v-else>
                                        <Input 
                                            v-model="form.earning_points" 
                                            label="Points per Visit" 
                                            type="number" 
                                            min="1" 
                                            required 
                                            :error="form.errors.earning_points" 
                                        />
                                    </div>

                                    <!-- Conditions -->
                                    <div class="border-t border-gray-200 pt-4">
                                        <h4 class="text-sm font-bold text-gray-900 mb-3">Conditions</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <Input 
                                                    v-model="form.earning_min_spent" 
                                                    :label="'Minimum Spend (' + form.currency + ') - Optional'" 
                                                    type="number" 
                                                    min="0" 
                                                    step="0.01" 
                                                    placeholder="0"
                                                    :error="form.errors.earning_min_spent" 
                                                />
                                            </div>
                                            <div v-if="form.earning_method_type === 'order_total'">
                                                <Input 
                                                    v-model="form.earning_max_points" 
                                                    label="Max Points Cap (Optional)" 
                                                    type="number" 
                                                    min="1"
                                                    placeholder="No Limit"
                                                    :error="form.errors.earning_max_points" 
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 p-4">
                        <Link :href="route('admin.restaurants.index')" class="px-6 py-3 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors">
                            Cancel
                        </Link>
                        <Button type="submit" :loading="form.processing" class="px-8 py-3 text-lg shadow-xl shadow-indigo-500/20">
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps<{
    restaurant: {
        id: number;
        name: string;
        slug: string;
        email: string;
        notification_email?: string; 
        owner_email?: string;
        phone: string | null;
        currency: string;
        service_type?: string;
        has_cash_drawer?: boolean;
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
        logo?: string;
    };
}>();

const countries = [
    'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman',
    'United States', 'United Kingdom', 'Canada', 'Australia', 'India', 'Pakistan',
    'Egypt', 'Jordan', 'Lebanon', 'Germany', 'France', 'Italy', 'Spain'
];

const form = useForm({
    _method: 'PUT',
    name: props.restaurant.name,
    slug: props.restaurant.slug,
    email: props.restaurant.email,
    notification_email: props.restaurant.notification_email || props.restaurant.email,
    phone: props.restaurant.phone,
    currency: props.restaurant.currency,
    service_type: props.restaurant.service_type || 'both',
    has_cash_drawer: props.restaurant.has_cash_drawer || false,
    status: props.restaurant.status || 'active',
    address: props.restaurant.address,
    city: props.restaurant.city,
    country: props.restaurant.country || 'United Arab Emirates',
    state: props.restaurant.state,
    zip_code: props.restaurant.zip_code,
    google_map_location: props.restaurant.google_map_location || '',
    logo: null as File | null,

    // Loyalty/Earning Method fields
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

const logoPreview = ref<string | null>(null);
const logoInput = ref<HTMLInputElement | null>(null);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    } else {
        form.logo = null;
        logoPreview.value = null;
    }
};

const route = (window as any).route;

const submit = () => {
    // We use form.post because we may be uploading a file (logo), even though logic is update.
    // _method: 'PUT' is handled by Laravel to treat this as a PUT request.
    form.post(route('admin.restaurants.update', props.restaurant.id), {
        forceFormData: true,
    });
};
</script>
