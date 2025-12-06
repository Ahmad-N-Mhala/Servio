<template>
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-gray-50 to-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full">
            <div class="text-center mb-10 flex flex-col items-center justify-center">
                <div class="flex justify-center w-full mb-6">
                    <Logo class="h-20 w-20" iconClass="w-20 h-20" :showText="true" />
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ $t('onboarding.title') }}</h1>
                <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">Choose the perfect plan for your restaurant and get started in minutes.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-10 border border-white/50 transition-all duration-300">
                <!-- Billing Cycle Toggle -->
                <div class="flex justify-center mb-12">
                    <div class="bg-gray-100/80 p-1.5 rounded-xl inline-flex relative shadow-inner">
                        <div
                            class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-white rounded-lg shadow-sm transition-all duration-300 ease-out"
                            :class="form.billing_cycle === 'monthly' ? 'left-1.5' : 'left-[calc(50%+3px)]'"
                        ></div>
                        <button
                            type="button"
                            @click="form.billing_cycle = 'monthly'"
                            class="relative z-10 px-8 py-2.5 text-sm font-semibold rounded-lg transition-colors duration-200 focus:outline-none min-w-[140px]"
                            :class="form.billing_cycle === 'monthly' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Monthly
                        </button>
                        <button
                            type="button"
                            @click="form.billing_cycle = 'yearly'"
                            class="relative z-10 px-8 py-2.5 text-sm font-semibold rounded-lg transition-colors duration-200 focus:outline-none min-w-[140px] flex items-center justify-center gap-2"
                            :class="form.billing_cycle === 'yearly' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Yearly
                            <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold tracking-wide shadow-sm">SAVE 20%</span>
                        </button>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">
                        {{ $t('onboarding.subdomain') }}
                    </label>
                    <div class="flex group">
                        <input
                            v-model="form.subdomain"
                            type="text"
                            class="flex-1 rounded-l-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3.5 px-4 text-gray-900 placeholder-gray-400 transition-all group-hover:border-gray-400"
                            placeholder="myrestaurant"
                            required
                        />
                        <span class="inline-flex items-center px-5 rounded-r-xl border border-l-0 border-gray-300 bg-gray-50/50 text-gray-500 text-sm font-medium backdrop-blur-sm">
                            .{{ baseDomain }}
                        </span>
                    </div>
                </div>

                <div class="mb-10">
                    <label class="block text-sm font-semibold text-gray-700 mb-4 ml-1">
                        {{ $t('onboarding.choose_plan') }}
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            v-for="plan in plans"
                            :key="plan.id"
                            @click="form.plan_id = plan.id"
                            class="relative border-2 rounded-2xl p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 bg-white"
                            :class="[
                                form.plan_id === plan.id 
                                    ? 'border-primary bg-primary-50/30 ring-1 ring-primary shadow-md' 
                                    : 'border-gray-100 hover:border-gray-300'
                            ]"
                        >
                            <div v-if="form.plan_id === plan.id" class="absolute -top-3 right-4 bg-primary text-white text-[10px] uppercase tracking-wider font-bold px-3 py-1 rounded-full shadow-md transform transition-transform scale-100">
                                Selected
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                            <div class="mt-3 flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-gray-900 tracking-tight">
                                    {{ plan.currency }} {{ form.billing_cycle === 'yearly' ? plan.price_yearly : plan.price_monthly }}
                                </span>
                                <span class="text-sm font-medium text-gray-500">/{{ form.billing_cycle === 'yearly' ? 'year' : 'mo' }}</span>
                            </div>
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <ul class="space-y-3 text-sm text-gray-600">
                                    <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 flex items-center justify-center mt-0.5">
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <span class="leading-tight">{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <Input
                        v-model="form.name"
                        label="Name"
                        type="text"
                        required
                        :error="form.errors.name"
                    />
                    <Input
                        v-model="form.email"
                        label="Email"
                        type="email"
                        required
                        :error="form.errors.email"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
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

                <Button
                    type="submit"
                    :loading="form.processing"
                    block
                    size="lg"
                >
                    {{ form.processing ? 'Processing...' : 'Continue to Payment' }}
                </Button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const { t } = useI18n();

defineProps<{
    plans: any[];
    baseDomain: string;
}>();

const form = useForm({
    subdomain: '',
    plan_id: null as number | null,
    billing_cycle: 'monthly',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('onboard.store'));
};
</script>

