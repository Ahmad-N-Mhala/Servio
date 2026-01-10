<template>
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('landing.plans_pricing') || 'Subscription Plans' }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h3 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        {{ $t('landing.choose_plan') || 'Choose Your Perfect Plan' }}
                    </h3>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ $t('landing.choose_plan_desc') || "Select the plan that best fits your restaurant's needs" }}
                    </p>
                </div>

                <!-- Current Plan Banner -->
                <div v-if="currentSubscription" class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-primary/20 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ $t('plans.current_plan') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ currentSubscription.plan.name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-600">{{ $t('plans.expires_on') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ formatDate(currentSubscription.ends_at) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ daysRemaining(currentSubscription.ends_at) }} {{ $t('plans.days_remaining') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Billing Cycle Toggle -->
                <div class="flex justify-center mb-12">
                    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-200 inline-flex">
                        <button
                            type="button"
                            @click="billingCycle = 'monthly'"
                            class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200"
                            :class="billingCycle === 'monthly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                        >
                            {{ $t('landing.monthly') || 'Monthly' }}
                        </button>
                        <button
                            type="button"
                            @click="billingCycle = 'yearly'"
                            class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2"
                            :class="billingCycle === 'yearly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                        >
                            {{ $t('landing.yearly') || 'Yearly' }}
                            <span :class="billingCycle === 'yearly' ? 'bg-white/20 text-white' : 'bg-green-100 text-green-700'" class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $t('landing.discount_20') || 'Save 20%' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.id"
                        class="relative bg-white rounded-2xl shadow-lg border-2 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 flex flex-col"
                        :class="isCurrentPlan(plan.id) ? 'border-blue-500 ring-4 ring-blue-100' : 'border-gray-200 hover:border-primary/50'"
                    >
                        <!-- Featured Badge -->
                        <div v-if="plan.is_featured" class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="px-4 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg whitespace-nowrap">
                                {{ $t('landing.most_popular') || 'MOST POPULAR' }}
                            </span>
                        </div>

                        <!-- Current Plan Badge -->
                        <div v-if="isCurrentPlan(plan.id)" class="absolute -top-4 right-4">
                            <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-lg">
                                {{ $t('plans.current_plan').toUpperCase() }}
                            </span>
                        </div>

                        <div class="p-8 flex-grow">
                            <!-- Plan Header -->
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                     {{ plan.slug ? ($t('plans.' + plan.slug) !== 'plans.' + plan.slug ? $t('plans.' + plan.slug) : plan.name) : plan.name }}
                                </h3>
                                <p class="text-gray-600 text-sm min-h-[40px]">{{ plan.description }}</p>
                            </div>

                            <!-- Pricing -->
                            <div class="text-center mb-8">
                                <div class="flex items-baseline justify-center gap-2">
                                    <span class="text-4xl font-bold text-gray-900">
                                        {{ currency }}{{ convertPrice(billingCycle === 'monthly' ? plan.price_monthly : plan.price_yearly) }}
                                    </span>
                                    <span class="text-gray-600">/{{ billingCycle === 'monthly' ? ($t('landing.month') || 'month') : ($t('landing.year') || 'year') }}</span>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="space-y-4 mb-8">
                                <div v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700 text-sm">{{ $t(feature) !== feature ? $t(feature) : feature }}</span>
                                </div>
                            </div>

                            <!-- Limits -->
                            <div class="mb-8 p-4 bg-gray-50 rounded-xl space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $t('plans.max_restaurants') || 'Max Restaurants' }}:</span>
                                    <span class="font-semibold text-gray-900">{{ plan.max_restaurants || 'Unlimited' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $t('plans.max_users') || 'Max Users' }}:</span>
                                    <span class="font-semibold text-gray-900">{{ plan.max_users || 'Unlimited' }}</span>
                                </div>
                            </div>
                        </div>

                         <!-- Action Button (Footer) -->
                        <div class="p-8 pt-0 mt-auto">
                            <button
                                v-if="!isCurrentPlan(plan.id)"
                                @click="openRegisterModal(plan)"
                                :disabled="!plan.is_active"
                                class="w-full py-3 px-6 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="plan.is_featured 
                                    ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700' 
                                    : 'bg-gray-900 text-white hover:bg-gray-800'"
                            >
                                {{ getButtonText(plan) }}
                            </button>
                            <div v-else class="w-full py-3 px-6 rounded-xl font-semibold text-center bg-gray-100 text-gray-600 border border-gray-200 cursor-default">
                                {{ $t('plans.current_plan') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="mt-12 text-center p-8 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl border border-gray-200">
                    <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $t('landing.custom_plan_title') || 'Need a Custom Plan?' }}</h4>
                    <p class="text-gray-600 mb-4">{{ $t('landing.custom_plan_desc') || "Contact our sales team for enterprise solutions tailored to your needs" }}</p>
                    <a href="mailto:sales@servio.com" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800 transition-all shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $t('landing.contact_sales') || 'Contact Sales' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Registration Interest Modal -->
        <Modal :show="showRegisterModal" @close="showRegisterModal = false">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('landing.register_interest') || 'Register Interest' }}</h3>
                    <p class="text-gray-500">
                        {{ $t('landing.for_plan', { plan: selectedPlan?.slug ? ($t('plans.' + selectedPlan.slug) !== 'plans.' + selectedPlan.slug ? $t('plans.' + selectedPlan.slug) : selectedPlan.name) : selectedPlan?.name }) }}
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
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const page = usePage();
const { t } = useI18n();
const route = (window as any).route;

const props = defineProps<{
    plans: any[];
    currentSubscription: any;
}>();

const billingCycle = ref<'monthly' | 'yearly'>('monthly');
const showRegisterModal = ref(false);
const selectedPlan = ref<any>(null);
const successMessage = ref('');
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const currencyRate = computed(() => (page.props.current_restaurant as any)?.currency_rate || 1);

// Auth User Data for Prefill
const user = computed(() => (page.props.auth as any)?.user);
const currentRestaurant = computed(() => (page.props.current_restaurant as any));

const form = useForm({
    plan_id: '',
    plan_name: '',
    name: '',
    email: '',
    phone: '',
    restaurant_name: '',
    message: ''
});

const convertPrice = (amount: number) => {
    return (Number(amount) * currencyRate.value).toFixed(2);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const daysRemaining = (endDate: string) => {
    const end = new Date(endDate);
    const now = new Date();
    const diff = Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return Math.max(0, diff);
};

const isCurrentPlan = (planId: number) => {
    return props.currentSubscription?.plan_id === planId;
};

const getButtonText = (plan: any) => {
    if (!props.currentSubscription) return t('landing.select_plan') || 'Select Plan';
    
    // Compare prices based on current billing cycle (simplified)
    const currentPrice = parseFloat(props.currentSubscription.plan.price_monthly);
    const newPrice = parseFloat(plan.price_monthly);
    
    if (newPrice > currentPrice) return t('landing.upgrade') || 'Upgrade';
    if (newPrice < currentPrice) return t('landing.downgrade') || 'Downgrade';
    return t('landing.select_plan') || 'Select Plan';
};

const openRegisterModal = (plan: any) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    form.plan_name = plan.name;
    
    // Prefill form data
    if (user.value) {
        form.name = user.value.name || '';
        form.email = user.value.email || '';
        form.phone = user.value.phone || '';
    }
    
    if (currentRestaurant.value) {
        form.restaurant_name = currentRestaurant.value.name || '';
    }
    
    // Optional: Pre-fill message with intent
    if (isCurrentPlan(plan.id)) {
        form.message = "I have a question about my current plan.";
    } else {
        const action = getButtonText(plan);
        form.message = `I am interested in ${action.toLowerCase()} to the ${plan.name} plan.`;
    }

    showRegisterModal.value = true;
};

const submitInterest = () => {
    form.post(route('register.interest'), {
        preserveScroll: true,
        onSuccess: () => {
            showRegisterModal.value = false;
            form.reset();
            successMessage.value = t('landing.form_success') || 'Interest registered successfully! We will contact you soon.';
            setTimeout(() => successMessage.value = '', 5000);
        }
    });
};
</script>
