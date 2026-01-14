<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Restaurant</h2>
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
                                <Input v-model="form.name" label="Restaurant Name" required :error="form.errors.name" />
                                <Input v-model="form.slug" label="Slug (URL Friendly)" required :error="form.errors.slug" />
                                <Input v-model="form.email" label="Contact Email" type="email" required :error="form.errors.email" />
                                <PhoneInput 
                                    v-model="form.phone" 
                                    :country="form.country"
                                    label="Contact Phone" 
                                    required 
                                    :error="form.errors.phone" 
                                    placeholder="50 123 4567"
                                />

                                <Select 
                                    v-model="form.country" 
                                    label="Country" 
                                    :options="countries.map(c => ({ label: c, value: c }))" 
                                    :error="form.errors.country" 
                                    required
                                />

                                <Input 
                                    v-model="form.currency" 
                                    label="Currency (Auto)" 
                                    readonly 
                                    class="bg-gray-100 text-gray-500 cursor-not-allowed" 
                                    required 
                                    :error="form.errors.currency" 
                                />
                                
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">Logo</label>
                                    <input @change="handleLogoChange" type="file" ref="logoInput" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" accept="image/*">
                                    <div v-if="form.errors.logo" class="text-rose-500 text-xs mt-1">{{ form.errors.logo }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service & Subscription Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Configuration
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
                                
                                <!-- Spacer for grid alignment -->
                                <div class="hidden md:block"></div>

                                <Select 
                                    v-model="form.billing_cycle" 
                                    label="Billing Cycle" 
                                    :options="[
                                        { label: 'Monthly', value: 'monthly' },
                                        { label: 'Yearly (Save ~20%)', value: 'yearly' }
                                    ]" 
                                    required 
                                    :error="form.errors.billing_cycle" 
                                />

                                <Select 
                                    v-model="form.plan_id" 
                                    label="Subscription Plan" 
                                    :options="planOptions" 
                                    required 
                                    :error="form.errors.plan_id" 
                                />
                                <div v-if="selectedPlanFeatures.length > 0" class="col-span-full text-xs text-gray-500 -mt-4 pl-1">
                                    Includes: {{ selectedPlanFeatures.map(f => f.replace('_', ' ')).join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Account Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Owner Account
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Input v-model="form.owner_name" label="Owner Name" required :error="form.errors.owner_name" />
                                <Input v-model="form.owner_email" label="Owner Email (Login)" type="email" required :error="form.errors.owner_email" />
                                <PhoneInput 
                                    v-model="form.owner_phone" 
                                    :country="form.country"
                                    label="Owner Phone" 
                                    required 
                                    :error="form.errors.owner_phone" 
                                    placeholder="50 123 4567"
                                />
                                <Input v-model="form.owner_password" label="Owner Password" type="password" placeholder="Min 8 characters" required :error="form.errors.owner_password" />
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
                                <Input v-model="form.state" label="State / Province" :error="form.errors.state" />
                                <Input v-model="form.zip_code" label="Zip / Postal Code" :error="form.errors.zip_code" />
                                <div class="md:col-span-2">
                                    <Input v-model="form.google_map_location" label="Google Map Link (Optional)" type="url" placeholder="https://maps.google.com/..." :error="form.errors.google_map_location" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loyalty Setup Card -->
                    <transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-4"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-4"
                    >
                        <div v-if="hasLoyalty" class="bg-white shadow-xl sm:rounded-2xl border border-emerald-100 ring-1 ring-emerald-500/20">
                            <div class="p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <div class="bg-emerald-100 p-1.5 rounded-lg text-emerald-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                                        </div>
                                        Loyalty Program Setup
                                    </h3>
                                    <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full uppercase tracking-wider">Plan Feature</span>
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
                                        <!-- Points Configuration -->
                                        <div>
                                             <h4 class="text-sm font-bold text-gray-900 mb-3">Points Configuration</h4>
                                             
                                             <!-- Order Total Layout -->
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

                                             <!-- Visit Layout -->
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
                                                    <p class="text-xs text-gray-500 mt-1">Min bill amount to qualify.</p>
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
                                                    <p class="text-xs text-gray-500 mt-1">Max points per order.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <div class="flex items-center justify-end gap-4 p-4">
                        <Link :href="route('admin.restaurants.index')" class="px-6 py-3 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors">
                            Cancel
                        </Link>
                        <Button type="submit" :loading="form.processing" class="px-8 py-3 text-lg shadow-xl shadow-indigo-500/20">
                            Create Restaurant
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
import { computed, watch } from 'vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps<{
    plans: Array<{
        id: any;
        name: string;
        price_monthly: number;
        price_yearly: number;
        features: string[];
        enabled_features?: string[];
    }>;
}>();

const planOptions = computed(() => {
    const isYearly = form.billing_cycle === 'yearly';
    return props.plans.map(p => ({
        label: `${p.name} (${form.currency} ${isYearly ? p.price_yearly : p.price_monthly}/${isYearly ? 'yr' : 'mo'})`,
        value: p.id
    }));
});

const countries = [
    'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman',
    'United States', 'United Kingdom', 'Canada', 'Australia', 'India', 'Pakistan',
    'Egypt', 'Jordan', 'Lebanon', 'Germany', 'France', 'Italy', 'Spain'
];

const countryCurrencyMap: Record<string, string> = {
    'United Arab Emirates': 'AED',
    'Saudi Arabia': 'SAR',
    'Qatar': 'QAR',
    'Kuwait': 'KWD',
    'Bahrain': 'BHD',
    'Oman': 'OMR',
    'United States': 'USD',
    'United Kingdom': 'GBP',
    'Canada': 'CAD',
    'Australia': 'AUD',
    'India': 'INR',
    'Pakistan': 'PKR',
    'Egypt': 'EGP',
    'Jordan': 'JOD',
    'Lebanon': 'LBP',
    'Germany': 'EUR',
    'France': 'EUR',
    'Italy': 'EUR',
    'Spain': 'EUR'
};

const form = useForm({
    name: '',
    slug: '',
    email: '',
    phone: '',
    currency: 'AED', // Default
    
    // Subscription
    plan_id: null as any,
    billing_cycle: 'monthly',
    
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
    country: 'United Arab Emirates', // Default
    google_map_location: '',
    
    // Loyalty
    earning_method_type: 'order_total',
    earning_points: 1,
    earning_min_spent: 0,
    earning_max_points: null as number | null,
    earning_currency_amount: 1,

    // New Fields
    logo: null as File | null,
    service_type: 'both',
    has_cash_drawer: false,
});


// Watch country to update currency
watch(() => form.country, (newCountry) => {
    if (newCountry && countryCurrencyMap[newCountry]) {
        form.currency = countryCurrencyMap[newCountry];
    }
});

const hasLoyalty = computed(() => {
    if (!form.plan_id) return false;
    const selectedPlan = props.plans.find(p => p.id == form.plan_id);
    
    if (!selectedPlan) return false;
    
    const technicalFeatures = selectedPlan.enabled_features || [];
    return Array.isArray(technicalFeatures) && technicalFeatures.includes('loyalty');
});

const selectedPlanFeatures = computed(() => {
    if (!form.plan_id) return [];
    const selectedPlan = props.plans.find(p => p.id == form.plan_id);
    return selectedPlan ? (selectedPlan.enabled_features || []) : [];
});

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
    } else {
        form.logo = null;
    }
};

const route = (window as any).route;

const submit = () => {
    form.post(route('admin.restaurants.store'));
};
</script>
