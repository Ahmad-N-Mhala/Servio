<template>
    <div class="min-h-screen bg-gray-50 py-6 sm:py-12 px-4 sm:px-6 lg:px-8 font-sans">
        <!-- Toast for Global Feedback -->
        <Toast 
            :message="toastMessage" 
            :title="toastTitle" 
            :type="toastType" 
            :trigger="toastTrigger" 
        />
        
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8 sm:mb-12">
                 <div class="flex justify-center mb-4 sm:mb-6">
                     <Logo class="h-16 w-16 sm:h-20 sm:w-20" iconClass="w-16 h-16 sm:w-20 sm:h-20" :showText="true" />
                 </div>
                 <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl tracking-tight">
                     Add New Restaurant
                 </h1>
                 <p class="mt-2 sm:mt-4 text-lg sm:text-xl text-gray-500 max-w-2xl mx-auto">
                     Expand your business by adding another location
                 </p>
                 <div class="mt-4">
                     <Link :href="route('restaurants.index')" class="text-primary hover:text-primary-hover font-medium inline-flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Back to list
                     </Link>
                 </div>
            </div>

            <!-- Global Error Message -->
            <div v-if="(form.errors as any).error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 animate-fade-in mx-2 sm:mx-0">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-red-800">Registration Error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ (form.errors as any).error }}</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-[2rem] shadow-xl sm:shadow-2xl overflow-hidden border border-gray-100 mx-1 sm:mx-0">
                <div class="p-6 sm:p-10">
                    <div class="mb-6 sm:mb-8">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Restaurant Details</h2>
                        <p class="text-sm sm:text-base text-gray-500 mt-1">Configure your new restaurant location.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                        
                        <!-- Plan Selection Removed as per User Request -->
                        
                         <!-- Logo Upload -->
                        <div class="flex flex-col items-center justify-center p-6 bg-gray-50 border border-gray-100 rounded-2xl">
                             <div class="relative group cursor-pointer mb-4" @click="logoInput?.click()">
                                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white flex items-center justify-center relative">
                                    <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                    <div v-else class="text-gray-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    
                                    <!-- Overlay -->
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </div>
                                </div>
                                <div class="absolute bottom-0 right-0 bg-primary text-white p-1.5 rounded-full shadow-md border-2 border-white translate-x-1 translate-y-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </div>
                             </div>
                             <p class="text-sm text-gray-500 font-medium">Click to upload brand logo</p>
                             <input type="file" ref="logoInput" class="hidden" accept="image/*" @change="handleLogoChange" />
                             <p v-if="form.errors.logo" class="text-xs text-red-500 mt-2">{{ form.errors.logo }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100">
                             <Input
                                v-model="form.restaurant_name"
                                label="Restaurant Name"
                                type="text"
                                placeholder="e.g. Tasty Bites - Downtown"
                                required
                                :error="form.errors.restaurant_name"
                            />
                        </div>

                        <!-- Contact Details -->
                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Contact Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    v-model="form.email"
                                    label="Public Email"
                                    type="email"
                                    placeholder="contact@restaurant.com"
                                    required
                                    :error="form.errors.email"
                                />
                                <Input
                                    v-model="form.phone"
                                    label="Phone Number"
                                    type="tel"
                                    placeholder="+971 50 123 4567"
                                    required
                                    :error="form.errors.phone"
                                />
                            </div>
                        </div>

                        <!-- Service Type Configuration -->
                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Service Configuration</h3>
                            <label class="block text-sm font-medium text-gray-700">Service Type</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div 
                                    @click="form.service_type = 'both'"
                                    class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                    :class="form.service_type === 'both' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="font-bold text-gray-900 text-center">Both</div>
                                    <div class="text-xs text-gray-500 text-center mt-1">Table & Self Service</div>
                                </div>
                                <div 
                                    @click="form.service_type = 'table_service'"
                                    class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                    :class="form.service_type === 'table_service' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="font-bold text-gray-900 text-center">Table Service</div>
                                    <div class="text-xs text-gray-500 text-center mt-1">Waiter Only</div>
                                </div>
                                <div 
                                    @click="form.service_type = 'self_service'"
                                    class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                    :class="form.service_type === 'self_service' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="font-bold text-gray-900 text-center">Self Service</div>
                                    <div class="text-xs text-gray-500 text-center mt-1">Pickup/Kiosk</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Location Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <Select v-model="form.country" :error="form.errors.country">
                                        <option v-for="c in countries" :key="c.name" :value="c.name">{{ c.name }}</option>
                                    </Select>
                                </div>

                                <Input
                                    v-model="form.state"
                                    label="State / Province"
                                    type="text"
                                    placeholder="e.g. Dubai"
                                    required
                                    :error="form.errors.state"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    v-model="form.city"
                                    label="City"
                                    type="text"
                                    placeholder="e.g. Downtown"
                                    required
                                    :error="form.errors.city"
                                />
                                <Input
                                    v-model="form.zip_code"
                                    label="Zip / Postal Code"
                                    type="text"
                                    placeholder="e.g. 00000"
                                    :error="form.errors.zip_code"
                                />
                            </div>

                            <div class="mt-4">
                                <Input
                                    v-model="form.address"
                                    label="Street Address"
                                    type="text"
                                    placeholder="e.g. 123 Main St"
                                    required
                                    :error="form.errors.address"
                                />
                            </div>

                             <div class="mt-4">
                                <Input
                                    v-model="form.google_map_location"
                                    label="Google Map Embed URL (Optional)"
                                    type="text"
                                    placeholder="<iframe>...</iframe> or URL"
                                    :error="form.errors.google_map_location"
                                />
                            </div>
                        </div>

                        <!-- Loyalty Setup -->
                        <div v-if="hasLoyalty" class="pt-6 sm:pt-8 border-t border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 tracking-tight">Loyalty Program Setup</h3>
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">How should customers earn points?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div 
                                        @click="form.earning_method_type = 'order_total'"
                                        class="cursor-pointer border-2 rounded-2xl p-4 transition-all duration-300"
                                        :class="form.earning_method_type === 'order_total' ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-gray-100 hover:border-gray-200'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 rounded-xl" :class="form.earning_method_type === 'order_total' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">Per Spend</div>
                                                <div class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-medium">Bill total</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div 
                                        @click="form.earning_method_type = 'visit'"
                                        class="cursor-pointer border-2 rounded-2xl p-4 transition-all duration-300"
                                        :class="form.earning_method_type === 'visit' ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-gray-100 hover:border-gray-200'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 rounded-xl" :class="form.earning_method_type === 'visit' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">Per Visit</div>
                                                <div class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-medium">Fixed points</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-100">
                                        <Input
                                            v-model="form.earning_points"
                                            :label="form.earning_method_type === 'order_total' ? 'Points per 1 ' + currency + ' Spent' : 'Points per Visit'"
                                            type="number"
                                            min="1"
                                            required
                                            :error="(form.errors as any).earning_points"
                                        />
                                        <p class="text-xs text-gray-500 mt-2 font-medium">
                                            {{ form.earning_method_type === 'order_total' ? 'Tip: Set to 1 for basic 1 point = 1 ' + currency + '.' : 'Tip: Set to 10 for standard visit reward.' }}
                                        </p>
                                    </div>
                                     <div class="bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-100">
                                        <Input
                                            v-model="form.min_spent"
                                            label="Minimum Spend"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :error="(form.errors as any).min_spent"
                                        />
                                        <p class="text-xs text-gray-500 mt-2 font-medium">
                                            Minimum bill amount required to earn points.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <Button
                                type="submit"
                                :loading="form.processing"
                                block
                                size="xl"
                                class="w-full text-lg font-bold py-4 rounded-2xl shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all duration-300 transform hover:-translate-y-1 active:translate-y-0 active:scale-[0.98]"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Create Restaurant
                                </span>
                            </Button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    defaultCountry?: string;
    countries?: any[];
    planFeatures?: string[];
}>();

const hasLoyalty = computed(() => {
    return props.planFeatures ? props.planFeatures.includes('loyalty') : false;
});

const form = useForm({
    restaurant_name: '',
    
    // Location Details
    country: props.defaultCountry || 'United Arab Emirates',
    state: '',
    city: '',
    address: '',
    zip_code: '',

    logo: null as File | null,

    earning_method_type: 'order_total',
    earning_points: 1,
    min_spent: 0,

    // New Fields
    email: '',
    phone: '',
    google_map_location: '',
    service_type: 'both',
});

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);

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

const currency = computed(() => {
    // Simple mapping, expand as needed or fetch from backend config
    if (form.country === 'United Arab Emirates') return 'AED';
    if (form.country === 'Saudi Arabia') return 'SAR';
    if (form.country === 'United States') return 'USD';
    return 'Currency';
});

// Toast state for interactive feedback
const toastMessage = ref('');
const toastTitle = ref('');
const toastType = ref('info');
const toastTrigger = ref(0);

const showToast = (message: string, type: 'success' | 'error' | 'info' = 'info', title: string = '') => {
    toastMessage.value = message;
    toastTitle.value = title || (type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Notification');
    toastType.value = type;
    toastTrigger.value++;
};

const submit = () => {
    form.post((window as any).route('restaurants.store'), {
        onSuccess: () => {
             // Reset preview logic not needed as we redirect
            showToast('Restaurant created successfully!', 'success');
        },
        onError: (errors) => {
            if (errors.error) {
                showToast(errors.error, 'error', 'Action Required');
            } else {
                showToast('Please check the form for errors.', 'error');
            }
        }
    });
};
</script>
