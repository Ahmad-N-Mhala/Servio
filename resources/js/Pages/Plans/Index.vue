<template>
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Subscription Plans
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h3 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Choose Your Perfect Plan
                    </h3>
                    <p class="mt-4 text-lg text-gray-600">
                        Select the plan that best fits your restaurant's needs
                    </p>
                </div>

                <!-- Current Plan Banner -->
                <div v-if="currentSubscription" class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-blue-200 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-600 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Current Plan</p>
                                <p class="text-2xl font-bold text-gray-900">{{ currentSubscription.plan.name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-600">Expires On</p>
                            <p class="text-lg font-semibold text-gray-900">{{ formatDate(currentSubscription.ends_at) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ daysRemaining(currentSubscription.ends_at) }} days remaining</p>
                        </div>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.id"
                        class="relative bg-white rounded-2xl shadow-lg border-2 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2"
                        :class="isCurrentPlan(plan.id) ? 'border-blue-500 ring-4 ring-blue-100' : 'border-gray-200 hover:border-blue-300'"
                    >
                        <!-- Featured Badge -->
                        <div v-if="plan.is_featured" class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="px-4 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                                POPULAR
                            </span>
                        </div>

                        <!-- Current Plan Badge -->
                        <div v-if="isCurrentPlan(plan.id)" class="absolute -top-4 right-4">
                            <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-lg">
                                CURRENT
                            </span>
                        </div>

                        <div class="p-8">
                            <!-- Plan Header -->
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                                <p class="text-gray-600 text-sm">{{ plan.description }}</p>
                            </div>

                            <!-- Pricing -->
                            <div class="text-center mb-8">
                                <div class="flex items-baseline justify-center gap-2">
                                    <span class="text-4xl font-bold text-gray-900">{{ plan.currency }}{{ plan.price_monthly }}</span>
                                    <span class="text-gray-600">/month</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">
                                    or {{ plan.currency }}{{ plan.price_yearly }}/year (Save {{ calculateYearlySavings(plan) }}%)
                                </p>
                            </div>

                            <!-- Features -->
                            <div class="space-y-4 mb-8">
                                <div v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700 text-sm">{{ feature }}</span>
                                </div>
                            </div>

                            <!-- Limits -->
                            <div class="mb-8 p-4 bg-gray-50 rounded-xl space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Max Restaurants:</span>
                                    <span class="font-semibold text-gray-900">{{ plan.max_restaurants || 'Unlimited' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Max Users:</span>
                                    <span class="font-semibold text-gray-900">{{ plan.max_users || 'Unlimited' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Orders/Month:</span>
                                    <span class="font-semibold text-gray-900">{{ plan.max_orders_per_month || 'Unlimited' }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button
                                v-if="!isCurrentPlan(plan.id)"
                                @click="selectPlan(plan)"
                                :disabled="!plan.is_active"
                                class="w-full py-3 px-6 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="plan.is_featured 
                                    ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700' 
                                    : 'bg-gray-900 text-white hover:bg-gray-800'"
                            >
                                {{ getButtonText(plan) }}
                            </button>
                            <div v-else class="w-full py-3 px-6 rounded-xl font-semibold text-center bg-gray-100 text-gray-600">
                                Current Plan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="mt-12 text-center p-8 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl">
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Need a Custom Plan?</h4>
                    <p class="text-gray-600 mb-4">Contact our sales team for enterprise solutions tailored to your needs</p>
                    <a href="mailto:sales@restofy.com" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps<{
    plans: any[];
    currentSubscription: any;
}>();

const route = (window as any).route;

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
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

const calculateYearlySavings = (plan: any) => {
    const monthlyCost = plan.price_monthly * 12;
    const yearlyCost = plan.price_yearly;
    const savings = ((monthlyCost - yearlyCost) / monthlyCost) * 100;
    return Math.round(savings);
};

const getButtonText = (plan: any) => {
    if (!props.currentSubscription) return 'Select Plan';
    
    const currentPrice = parseFloat(props.currentSubscription.plan.price_monthly);
    const newPrice = parseFloat(plan.price_monthly);
    
    if (newPrice > currentPrice) return 'Upgrade';
    if (newPrice < currentPrice) return 'Downgrade';
    return 'Select Plan';
};

const selectPlan = (plan: any) => {
    if (confirm(`Are you sure you want to switch to the ${plan.name} plan?`)) {
        router.post(route('plans.subscribe', plan.id));
    }
};
</script>
