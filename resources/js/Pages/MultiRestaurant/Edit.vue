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
                     Edit Restaurant
                 </h1>
                 <p class="mt-2 sm:mt-4 text-lg sm:text-xl text-gray-500 max-w-2xl mx-auto">
                     Update your restaurant details
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
                    <h3 class="text-sm font-bold text-red-800">Update Error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ (form.errors as any).error }}</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-[2rem] shadow-xl sm:shadow-2xl overflow-hidden border border-gray-100 mx-1 sm:mx-0">
                <div class="p-6 sm:p-10">
                    <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                        
                        <!-- Logo Upload -->
                        <div class="flex flex-col items-center justify-center p-6 bg-gray-50 border border-gray-100 rounded-2xl">
                             <div class="relative group cursor-pointer mb-4" @click="$refs.logoInput.click()">
                                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white flex items-center justify-center relative">
                                    <img v-if="logoPreview || currentLogo" :src="logoPreview || currentLogo" class="w-full h-full object-cover" />
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
                                v-model="form.name"
                                label="Restaurant Name"
                                type="text"
                                placeholder="e.g. My Great Bistro - Downtown"
                                required
                                :error="form.errors.name"
                            />
                        </div>

                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Contact Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    v-model="form.email"
                                    label="Public Email"
                                    type="email"
                                    :error="form.errors.email"
                                />
                                <Input
                                    v-model="form.phone"
                                    label="Phone Number"
                                    type="text"
                                    :error="form.errors.phone"
                                />
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

                            <Input
                                v-model="form.address"
                                label="Street Name / Address"
                                type="text"
                                placeholder="e.g. Sheikh Zayed Road, Building 5"
                                required
                                :error="form.errors.address"
                            />
                        </div>

                        <div class="bg-gray-50 p-4 sm:p-6 rounded-2xl border border-gray-100 space-y-4">
                             <Input
                                v-model="form.google_map_location"
                                label="Google Map Embed URL (Optional)"
                                type="text"
                                placeholder="<iframe>...</iframe> or URL"
                                :error="form.errors.google_map_location"
                            />
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
                                    Update Details
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
import { useForm, router } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    restaurant: any;
    countries: any[];
}>();

const form = useForm({
    _method: 'PUT',
    name: props.restaurant.name,
    email: props.restaurant.email,
    phone: props.restaurant.phone,
    country: props.restaurant.country || 'United Arab Emirates',
    state: props.restaurant.state,
    city: props.restaurant.city,
    address: props.restaurant.address,
    zip_code: props.restaurant.zip_code,
    google_map_location: props.restaurant.google_map_location,
    logo: null as File | null,
});

const currentLogo = ref(props.restaurant.logo ? `/storage/${props.restaurant.logo}` : null);
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

// Toast state
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
    form.post((window as any).route('restaurants.update', props.restaurant.id), {
        onSuccess: () => {
             // Reset preview if needed or rely on page reload. 
             // Since we redirect to index, it's fine.
            showToast('Restaurant details updated successfully!', 'success');
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
