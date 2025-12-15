<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                 <div class="flex justify-center mb-6">
                     <Logo class="h-20 w-20" iconClass="w-20 h-20" :showText="true" />
                 </div>
                 <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
                     {{ step === 1 ? 'Select Your Plan' : 'Create Your Account' }}
                 </h1>
                 <p class="mt-4 text-xl text-gray-500 max-w-2xl mx-auto">
                     {{ step === 1 ? 'Choose the plan that fits your restaurant best.' : 'Finalize your setup for ' + selectedPlan?.name }}
                 </p>
            </div>

            <!-- Step 1: Plan Selection -->
            <div v-show="step === 1" class="transition-opacity duration-300">
                
                <!-- Billing Toggle -->
                <div class="flex justify-center mb-12">
                    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-200 inline-flex">
                        <button
                            type="button"
                            @click="form.billing_cycle = 'monthly'"
                            class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200"
                            :class="form.billing_cycle === 'monthly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                        >
                            Monthly
                        </button>
                        <button
                            type="button"
                            @click="form.billing_cycle = 'yearly'"
                            class="px-8 py-3 text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2"
                            :class="form.billing_cycle === 'yearly' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900'"
                        >
                            Yearly
                            <span :class="form.billing_cycle === 'yearly' ? 'bg-white/20 text-white' : 'bg-green-100 text-green-700'" class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">Save 20%</span>
                        </button>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="selectPlan(plan)"
                        class="bg-white rounded-3xl p-8 border-2 transition-all duration-300 relative group flex flex-col h-full cursor-pointer overflow-hidden"
                        :class="[
                            selectedPlan?.id === plan.id 
                                ? 'border-primary shadow-2xl ring-4 ring-primary-100 transform -translate-y-2' 
                                : 'border-gray-200 shadow-lg hover:border-primary-300 hover:shadow-xl hover:-translate-y-1'
                        ]"
                    >
                        <!-- Selected Indicator -->
                        <div v-if="selectedPlan?.id === plan.id" class="absolute top-0 right-0 p-4">
                            <div class="bg-primary rounded-full p-1 shadow-lg animate-pulse-glow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>



                        <div class="mb-6 relative z-10">
                            <h3 class="text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors duration-300">{{ plan.name }}</h3>
                            <p class="text-gray-500 text-sm mt-2 min-h-[40px]">{{ getPlanDescription(plan.slug) }}</p>
                        </div>

                        <div class="mb-8 p-6 bg-gray-50 rounded-2xl group-hover:bg-primary-50 transition-colors duration-300">
                             <div class="flex items-baseline justify-center">
                                <span class="text-4xl font-extrabold text-gray-900 group-hover:text-primary transition-colors duration-300">
                                    {{ plan.currency }} {{ Math.floor(form.billing_cycle === 'yearly' ? plan.price_yearly : plan.price_monthly) }}
                                </span>
                                <span class="text-gray-500 ml-1 font-medium">/{{ form.billing_cycle === 'yearly' ? 'year' : 'month' }}</span>
                            </div>
                        </div>

                        <div class="flex-grow mb-8 relative z-10">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 group-hover:text-primary-400">What's Included</h4>
                            <ul class="space-y-4">
                                <li v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 flex items-center justify-center mt-0.5 group-hover:bg-primary-100 transition-colors">
                                        <svg class="w-3 h-3 text-green-600 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600 text-sm font-medium group-hover:text-gray-900 transition-colors">{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <button
                            class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-300 shadow-md"
                            :class="[
                                selectedPlan?.id === plan.id
                                    ? 'bg-primary text-white shadow-primary/30'
                                    : 'bg-white text-gray-900 border-2 border-gray-100 group-hover:border-primary group-hover:text-primary'
                            ]"
                        >
                            {{ selectedPlan?.id === plan.id ? 'Selected' : 'Select Plan' }}
                        </button>
                    </div>
                </div>

                <!-- Continue Button (Only visible when plan selected) -->
                <div v-if="selectedPlan" class="fixed bottom-0 left-0 right-0 p-6 bg-white/80 backdrop-blur-xl border-t border-gray-200 flex justify-center z-50 animate-slide-up">
                    <div class="max-w-7xl w-full flex justify-between items-center px-4 sm:px-6">
                        <div class="hidden sm:block">
                            <p class="text-sm text-gray-500">Selected Plan:</p>
                            <p class="text-lg font-bold text-gray-900">{{ selectedPlan.name }} <span class="text-primary">({{ planPriceDisplay(selectedPlan) }})</span></p>
                        </div>
                        <button 
                            @click="continueToSetup"
                            class="bg-primary hover:bg-primary-hover text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:shadow-primary/30 transition-all transform hover:-translate-y-1 flex items-center gap-2"
                        >
                            Continue to Setup
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Registration Form -->
            <div v-show="step === 2" class="max-w-2xl mx-auto transition-opacity duration-300">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                    <div class="p-8 md:p-10">
                        <button 
                            @click="step = 1" 
                            class="mb-6 flex items-center text-sm text-gray-500 hover:text-gray-900 font-medium transition-colors"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back to Plans
                        </button>

                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900">Finish Setting Up</h2>
                            <p class="text-gray-500 mt-1">You selected the <span class="text-indigo-600 font-bold">{{ selectedPlan?.name }}</span> plan.</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                <Input
                                    v-model="form.restaurant_name"
                                    label="Restaurant Name"
                                    type="text"
                                    placeholder="e.g. My Great Bistro"
                                    required
                                    :error="form.errors.restaurant_name"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Input
                                    v-model="form.name"
                                    label="Full Name"
                                    type="text"
                                    required
                                    :error="form.errors.name"
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

                            <div>
                                <Input
                                    v-model="form.email"
                                    label="Email"
                                    type="email"
                                    required
                                    :error="form.errors.email"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Input
                                    v-model="form.password"
                                    label="Password"
                                    type="password"
                                    required
                                    :error="form.errors.password"
                                />
                                <Input
                                    v-model="form.password_confirmation"
                                    label="Confirm Password"
                                    type="password"
                                    required
                                    :error="form.errors.password_confirmation"
                                />
                            </div>

                            <!-- Loyalty Setup -->
                            <div class="pt-6 border-t border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Loyalty Program Setup</h3>
                                <div class="space-y-4">
                                    <label class="block text-sm font-medium text-gray-700">How should customers earn points?</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div 
                                            @click="form.earning_method_type = 'order_total'"
                                            class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                            :class="form.earning_method_type === 'order_total' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-full" :class="form.earning_method_type === 'order_total' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
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
                                            :class="form.earning_method_type === 'visit' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-full" :class="form.earning_method_type === 'visit' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900">Per Visit</div>
                                                    <div class="text-xs text-gray-500">Fixed points per order/visit</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 bg-gray-50 p-4 rounded-xl">
                                         <Input
                                            v-model="form.earning_points"
                                            :label="form.earning_method_type === 'order_total' ? 'Points per 1 AED Spent' : 'Points per Visit'"
                                            type="number"
                                            min="1"
                                            required
                                            :error="(form.errors as any).earning_points"
                                        />
                                        <p class="text-xs text-gray-500 mt-2">
                                            {{ form.earning_method_type === 'order_total' ? 'Example: If set to 1, a 100 AED order earns 100 points.' : 'Example: If set to 10, every visit earns 10 points regardless of spend.' }}
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
                                    class="w-full text-lg font-bold py-4 rounded-xl"
                                >
                                    Create Account
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

defineProps<{
    plans: any[];
}>();

const step = ref(1);
const selectedPlan = ref<any>(null);

const form = useForm({
    restaurant_name: '',
    plan_id: null as number | null,
    billing_cycle: 'monthly',
    name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
    earning_method_type: 'order_total',
    earning_points: 1,
});

const getPlanDescription = (slug: string) => {
    switch(slug) {
        case 'basic': return 'Perfect for getting started.';
        case 'pro': return 'Everything you need to grow.';
        case 'enterprise': return 'Advanced control for chains.';
        default: return 'Business plan.';
    }
};

const planPriceDisplay = (plan: any) => {
    const price = Math.floor(form.billing_cycle === 'yearly' ? plan.price_yearly : plan.price_monthly);
    const period = form.billing_cycle === 'yearly' ? 'year' : 'month';
    return `${plan.currency} ${price}/${period}`;
};

const selectPlan = (plan: any) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    // Don't auto-advance, let user click Continue
};

const continueToSetup = () => {
    step.value = 2;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submit = () => {
    form.post((window as any).route('onboard.store'));
};
</script>

