<template>
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('restaurants.edit_title') }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Restaurant Info Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                {{ $t('restaurants.details') }}
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

                                <Input v-model="form.name" :label="$t('restaurants.name')" required :error="form.errors.name" />
                                <Input v-model="form.slug" :label="$t('restaurants.slug')" required :error="form.errors.slug" />
                                
                                <div>
                                    <Input v-model="form.email" :label="$t('restaurants.email')" type="email" required :error="form.errors.email" />
                                    <p class="text-xs text-gray-500 mt-1">{{ $t('restaurants.email_help') }}</p>
                                </div>
                                
                                <div>
                                    <Input v-model="form.notification_email" :label="$t('restaurants.notification_email')" type="email" required :error="form.errors.notification_email" />
                                    <p class="text-xs text-gray-500 mt-1">{{ $t('restaurants.notification_email_help') }}</p>
                                </div>

                                <PhoneInput 
                                    v-model="form.phone" 
                                    :country="form.country || ''"
                                    :label="$t('restaurants.phone')" 
                                    :error="form.errors.phone" 
                                />

                                <Select 
                                    v-model="form.country" 
                                    :label="$t('restaurants.country')" 
                                    :options="countries.map(c => ({ label: c.name, value: c.name }))" 
                                    :error="form.errors.country" 
                                    required
                                />

                                <Input v-model="form.currency" :label="$t('restaurants.currency')" readonly class="bg-gray-100" />
                                
                            </div>
                        </div>
                    </div>

                    <!-- Service & Configuration Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ $t('restaurants.configuration') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Select 
                                    v-model="form.service_type" 
                                    :label="$t('restaurants.service_type')" 
                                    :options="[
                                        { label: $t('restaurants.service_both'), value: 'both' },
                                        { label: $t('restaurants.service_table'), value: 'table_service' },
                                        { label: $t('restaurants.service_self'), value: 'self_service' }
                                    ]"
                                    required 
                                    :error="form.errors.service_type" 
                                />

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-900">{{ $t('restaurants.cash_drawer') }}</label>
                                        <p class="text-xs text-gray-500">{{ $t('restaurants.cash_drawer_help') }}</p>
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

                    <!-- Location Card -->
                    <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ $t('restaurants.location_details') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <Input v-model="form.address" :label="$t('restaurants.address')" :error="form.errors.address" />
                                </div>
                                <Input v-model="form.city" :label="$t('restaurants.city')" :error="form.errors.city" />
                                <Input v-model="form.state" :label="$t('restaurants.state')" :error="form.errors.state" />
                                <Input v-model="form.zip_code" :label="$t('restaurants.zip')" :error="form.errors.zip_code" />
                                <div class="md:col-span-2">
                                    <Input v-model="form.google_map_location" :label="$t('restaurants.google_map')" type="url" placeholder="https://maps.google.com/..." :error="form.errors.google_map_location" />
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="flex items-center justify-end gap-4 p-4">
                        <Link :href="route('restaurants.index')" class="px-6 py-3 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors">
                            {{ $t('restaurants.cancel') }}
                        </Link>
                        <Button type="submit" :loading="form.processing" class="px-8 py-3 text-lg shadow-xl shadow-indigo-500/20">
                            {{ $t('restaurants.save_changes') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps<{
    restaurant: any;
    countries: any[]; 
    planFeatures?: string[];
    earningMethod?: any;
}>();

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
    address: props.restaurant.address,
    city: props.restaurant.city,
    country: props.restaurant.country || 'United Arab Emirates', // Default for now
    state: props.restaurant.state,
    zip_code: props.restaurant.zip_code,
    google_map_location: props.restaurant.google_map_location || '',
    logo: null as File | null,
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
    form.post(route('restaurants.update', props.restaurant.id), {
        forceFormData: true,
    });
};
</script>
