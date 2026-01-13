<template>
    <div :dir="locale === 'ar' ? 'rtl' : 'ltr'" class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                 <div class="flex justify-center mb-6">
                     <Logo class="h-20 w-20" iconClass="w-20 h-20" :showText="true" />
                 </div>
                 <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
                     {{ $t('landing.plans_pricing') }}
                 </h1>
                 <p class="mt-4 text-xl text-gray-500 max-w-2xl mx-auto">
                     {{ $t('landing.choose_plan') }}
                 </p>
            </div>

            <!-- Language Switcher -->
            <div class="absolute top-6 right-6 z-10">
                <button 
                    @click="toggleLanguage" 
                    class="bg-white/50 backdrop-blur-md p-2.5 rounded-xl hover:bg-white/80 transition-all shadow-sm border border-white/50 flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-primary group"
                >
                    <span class="uppercase font-extrabold tracking-wider">{{ locale }}</span>
                    <span class="w-px h-4 bg-gray-300 group-hover:bg-primary/30 transition-colors"></span>
                    <div class="bg-white rounded-full p-1 shadow-sm group-hover:shadow group-hover:scale-110 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                    </svg>
                    </div>
                </button>
            </div>

            <!-- Billing Toggle -->
            <div class="flex justify-center mb-12">
                <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-200 inline-flex">
                    <button
                        type="button"
                        @click="billingCycle = 'monthly'"
                        class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200"
                        :class="billingCycle === 'monthly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                    >
                        {{ $t('landing.monthly') }}
                    </button>
                    <button
                        type="button"
                        @click="billingCycle = 'yearly'"
                        class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2"
                        :class="billingCycle === 'yearly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                    >
                        {{ $t('landing.yearly') }}
                        <span :class="billingCycle === 'yearly' ? 'bg-white/20 text-white' : 'bg-green-100 text-green-700'" class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $t('landing.discount_20') }}</span>
                    </button>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="bg-white rounded-3xl p-8 border-2 transition-all duration-300 relative group flex flex-col h-full overflow-hidden"
                    :class="[
                        plan.is_featured 
                            ? 'border-primary shadow-2xl ring-4 ring-primary-100 transform -translate-y-2' 
                            : 'border-gray-200 shadow-lg hover:border-primary-300 hover:shadow-xl hover:-translate-y-1'
                    ]"
                >
                    <!-- Best Value Indicator -->
                    <div v-if="plan.is_featured" class="absolute top-0 right-0 p-4">
                        <div class="bg-primary px-3 py-1 rounded-full shadow-lg">
                            <span class="text-xs font-bold text-white uppercase">{{ $t('landing.most_popular') }}</span>
                        </div>
                    </div>

                    <div class="mb-6 relative z-10">
                        <h3 class="text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors duration-300">
                             {{ plan.slug ? ($t('plans.' + plan.slug) !== 'plans.' + plan.slug ? $t('plans.' + plan.slug) : plan.name) : plan.name }}
                        </h3>
                        <p class="text-gray-500 text-sm mt-2 min-h-[40px]">{{ plan.description }}</p>
                    </div>

                    <div class="mb-8 p-6 bg-gray-50 rounded-2xl group-hover:bg-primary-50 transition-colors duration-300">
                            <div class="flex items-baseline justify-center">
                            <span class="text-4xl font-extrabold text-gray-900 group-hover:text-primary transition-colors duration-300">
                                {{ plan.currency || $t('landing.currency') }} {{ billingCycle === 'monthly' ? plan.price_monthly : plan.price_yearly }}
                            </span>
                            <span class="text-gray-500 ml-1 font-medium">{{ billingCycle === 'monthly' ? $t('landing.per_month') : $t('landing.per_year') }}</span>
                        </div>
                    </div>

                    <div class="flex-grow mb-8 relative z-10">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 group-hover:text-primary-400">{{ $t('landing.plan_features') }}</h4>
                        <ul class="space-y-4">
                            <li v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 flex items-center justify-center mt-0.5 group-hover:bg-primary-100 transition-colors">
                                    <svg class="w-3 h-3 text-green-600 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm font-medium group-hover:text-gray-900 transition-colors">{{ $t(feature) !== feature ? $t(feature) : feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <button
                        @click="openRegisterModal(plan)"
                        class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-300 shadow-md"
                        :class="[
                            plan.is_featured
                                ? 'bg-primary text-white shadow-primary/30 hover:bg-primary-hover'
                                : 'bg-gray-900 text-white hover:bg-gray-800'
                        ]"
                    >
                        {{ $t('landing.select_plan') }}
                    </button>
                </div>
            </div>

            <!-- Registration Modal (Interest Only) -->
            <Modal :show="showRegisterModal" @close="showRegisterModal = false">
                 <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mx-auto mb-6 shadow-sm border border-emerald-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                         <h3 class="text-3xl font-extrabold text-gray-900 mb-2">{{ $t('landing.register_interest') }}</h3>
                         <p class="text-gray-500 font-medium text-lg">
                            {{ $t('landing.for_plan', { plan: selectedPlan?.name }) }}
                         </p>
                    </div>

                    <form @submit.prevent="submitInterest" class="space-y-4">
                         <div>
                            <Input 
                                v-model="form.name"
                                :label="$t('landing.full_name')"
                                required
                                :error="form.errors.name"
                            />
                        </div>
                         <div>
                            <Input 
                                v-model="form.email"
                                :label="$t('landing.email_address')"
                                type="email"
                                required
                                :error="form.errors.email"
                            />
                        </div>
                        <div>
                            <Input 
                                v-model="form.phone"
                                :label="$t('landing.phone_number')"
                                type="tel"
                                required
                                :error="form.errors.phone"
                            />
                        </div>
                         <div>
                            <Input 
                                v-model="form.restaurant_name"
                                :label="$t('landing.restaurant_name')"
                                required
                                :error="form.errors.restaurant_name"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('landing.message') }}</label>
                            <textarea 
                                v-model="form.message"
                                rows="3"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            ></textarea>
                        </div>

                        <div class="pt-4">
                             <Button class="w-full justify-center py-3 text-lg" :loading="form.processing">
                                {{ $t('landing.submit') }}
                            </Button>
                        </div>
                    </form>
                 </div>
            </Modal>

            <!-- Success Notification -->
            <Transition
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="successMessage" class="fixed bottom-0 right-0 p-6 z-50">
                    <div class="bg-green-600 rounded-xl shadow-lg p-4 flex items-center gap-3 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="font-bold">{{ successMessage }}</p>
                    </div>
                </div>
            </Transition>

        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps<{
    plans: any[];
}>();

const { locale } = useI18n();
const route = (window as any).route;

const billingCycle = ref<'monthly' | 'yearly'>('monthly');
const showRegisterModal = ref(false);
const selectedPlan = ref<any>(null);
const successMessage = ref('');

const form = useForm({
    plan_id: '',
    plan_name: '',
    name: '',
    email: '',
    phone: '',
    restaurant_name: '',
    message: ''
});

const toggleLanguage = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    
    // Quick URL patch for LaravelLocalization
    const currentPath = window.location.pathname; 
    const segments = currentPath.split('/'); 
    if (segments[1] && (segments[1] === 'en' || segments[1] === 'ar')) {
        segments[1] = newLocale;
        window.location.href = segments.join('/');
    } else {
            // If no locale in URL (default), append or redirect
            window.location.href = `/${newLocale}`;
    }
};

const openRegisterModal = (plan: any) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    form.plan_name = plan.name;
    showRegisterModal.value = true;
};

const submitInterest = () => {
    form.post(route('register.interest'), {
        preserveScroll: true,
        onSuccess: () => {
            showRegisterModal.value = false;
            form.reset();
            successMessage.value = 'We received your request! We will contact you shortly.'; // Or use t() key if available
            setTimeout(() => successMessage.value = '', 5000);
        }
    });
};
</script>

<style scoped>
html {
    scroll-behavior: smooth;
}
</style>

