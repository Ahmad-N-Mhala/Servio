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
                     <a href="/en/select-restaurant" class="text-primary hover:text-primary-hover font-medium inline-flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Back to list
                     </a>
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
                        <p class="text-sm sm:text-base text-gray-500 mt-1">This restaurant will be added to your existing subscription plan.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                        
                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100">
                            <Input
                                v-model="form.restaurant_name"
                                label="Restaurant Name"
                                type="text"
                                placeholder="e.g. My Great Bistro - Downtown"
                                required
                                :error="form.errors.restaurant_name"
                            />
                        </div>

                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Location Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <div class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-gray-500 sm:text-sm">
                                        {{ form.country }} <span class="text-xs text-gray-400 ml-2">(Auto-detected)</span>
                                    </div>
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

                            <Input
                                v-model="form.address"
                                label="Street Name / Address"
                                type="text"
                                placeholder="e.g. Sheikh Zayed Road, Building 5"
                                required
                                :error="form.errors.address"
                            />
                        </div>

                        <!-- Loyalty Setup -->
                        <div class="pt-6 sm:pt-8 border-t border-gray-100">
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

                                <div class="mt-4 bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-100">
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
import { useForm } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    defaultCountry?: string;
}>();

const form = useForm({
    restaurant_name: '',
    
    // Location Details
    country: props.defaultCountry || 'United Arab Emirates',
    state: '',
    city: '',
    address: '',
    zip_code: '',

    earning_method_type: 'order_total',
    earning_points: 1,
});

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
