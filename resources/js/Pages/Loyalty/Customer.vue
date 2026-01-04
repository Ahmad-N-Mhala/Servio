<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('loyalty.index')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 shadow-sm">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ customer.name || 'Unknown Member' }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                         <span class="text-sm text-gray-500">{{ customer.phone }}</span>
                         <span class="text-gray-300">•</span>
                         <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider">{{ customer.loyalty_tier || 'Bronze' }} Member</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Stats & Profile -->
                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-50 pb-2">Points Balance</h3>
                        <div class="flex items-end gap-2">
                            <span class="text-5xl font-black text-primary font-display leading-none">{{ customer.loyalty_points?.balance || 0 }}</span>
                            <span class="text-sm font-bold text-gray-400 mb-1">PTS</span>
                        </div>
                        
                        <div class="mt-8 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Earned</span>
                                <span class="font-bold text-gray-900">{{ customer.loyalty_points?.total_earned || 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Redeemed</span>
                                <span class="font-bold text-gray-900 text-red-500">{{ customer.loyalty_points?.total_redeemed || 0 }}</span>
                            </div>
                            <div class="pt-4 border-t border-gray-50 flex justify-between text-sm">
                                <span class="text-gray-500">Total Spent</span>
                                <span class="font-bold text-gray-900">{{ currency }} {{ customer.total_spent || '0.00' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Available Rewards for this customer -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-bold text-gray-900">Available Rewards</h2>
                        <div v-if="availableRewards.length > 0" class="space-y-3">
                            <div v-for="reward in availableRewards" :key="reward.id" 
                                class="glass-card p-4 rounded-xl border border-gray-100 flex justify-between items-center group hover:border-primary/50 transition-all cursor-pointer shadow-sm"
                                @click="prepareRedemption(reward)"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ getLocaleName(reward.name) }}</h4>
                                    <p class="text-xs text-primary font-bold">{{ reward.points_required }} Points Required</p>
                                </div>
                                <div class="bg-gray-100 group-hover:bg-primary group-hover:text-white p-2 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 italic p-4 text-center bg-gray-50 rounded-xl">
                            No rewards available for this point balance.
                        </div>
                    </div>
                </div>

                <!-- Right Column: Transactions & History -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Point Transactions -->
                    <div class="glass-card rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-900 font-display">Point Transactions</h2>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Activity</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Points</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="tx in customer.point_transactions" :key="tx.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            {{ new Date(tx.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-gray-900">{{ tx.description }}</p>
                                            <p v-if="tx.reference_type" class="text-xs text-gray-400">Ref: {{ tx.reference_type }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <span class="text-sm font-bold" :class="tx.points > 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ tx.points > 0 ? '+' : '' }}{{ tx.points }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!customer.point_transactions?.length">
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 italic">No transactions found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Redemption Modal -->
            <Modal :show="showRedeemModal" @close="showRedeemModal = false" title="Redeem Reward" size="md">
                <div class="space-y-6">
                    <div class="text-center p-6 bg-primary/5 rounded-2xl border border-primary/10">
                        <p class="text-sm text-gray-500 uppercase tracking-widest font-bold mb-2">You are redeeming</p>
                        <h3 class="text-2xl font-black text-gray-900 mb-1">{{ getLocaleName(selectedReward?.name) }}</h3>
                        <p class="text-primary font-bold text-lg">{{ selectedReward?.points_required }} Points</p>
                    </div>

                    <!-- OTP Phase -->
                    <div v-if="redemptionPhase === 'request'" class="space-y-4">
                        <div v-if="errorMessage" class="p-4 bg-red-50 text-red-700 rounded-xl text-sm border border-red-100 mb-4 animate-shake">
                            {{ errorMessage }}
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-blue-50 text-blue-700 rounded-xl text-sm border border-blue-100">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>For security, we will send a 6-digit verification code to the customer's phone <strong>{{ customer.phone }}</strong>.</p>
                        </div>
                        <Button class="w-full" @click="requestOtp" :loading="loading">Send Verification SMS</Button>
                    </div>

                    <!-- Verify Phase -->
                    <div v-if="redemptionPhase === 'verify'" class="space-y-6">
                        <div v-if="errorMessage" class="p-4 bg-red-50 text-red-700 rounded-xl text-sm border border-red-100 mb-4 animate-shake">
                            {{ errorMessage }}
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-4">Enter the 6-digit code sent to {{ customer.phone }}</p>
                            <div class="flex justify-center gap-2">
                                <input 
                                    v-model="otpCode"
                                    type="text"
                                    maxlength="6"
                                    class="text-4xl font-bold tracking-[0.5em] text-center w-64 border-2 focus:border-primary focus:ring-primary rounded-xl py-3 transition-all"
                                    :class="errorMessage ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                                    placeholder="000000"
                                    autofocus
                                    @input="errorMessage = ''"
                                >
                            </div>
                        </div>
                        <div class="flex flex-col gap-3">
                            <Button class="w-full" @click="verifyAndRedeem" :loading="loading" :disabled="otpCode.length < 6">Verify & Complete Redemption</Button>
                            <button @click="requestOtp" class="text-sm text-primary font-bold hover:underline" :disabled="loading">Resend Code</button>
                        </div>
                    </div>
                    
                    <button @click="showRedeemModal = false" class="block w-full text-center text-sm text-gray-400 hover:text-gray-600 transition-colors">Cancel</button>
                </div>
            </Modal>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    customer: any;
    rewards: any[];
}>();

const { locale } = useI18n();
const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const route = (window as any).route;

const showRedeemModal = ref(false);
const selectedReward = ref<any>(null);
const redemptionPhase = ref<'request' | 'verify'>('request');
const otpCode = ref('');
const loading = ref(false);
const errorMessage = ref('');

const availableRewards = computed(() => {
    const balance = props.customer.loyalty_points?.balance || 0;
    return (props.rewards || []).filter(r => r.points_required <= balance && r.is_active);
});

const getLocaleName = (name: any) => {
    if (!name) return 'Unknown';
    if (typeof name === 'string') return name;
    return name[locale.value] || name['en'] || 'Unknown';
};

const prepareRedemption = (reward: any) => {
    selectedReward.value = reward;
    redemptionPhase.value = 'request';
    otpCode.value = '';
    errorMessage.value = '';
    showRedeemModal.value = true;
};

const requestOtp = async () => {
    loading.value = true;
    errorMessage.value = '';
    try {
        await axios.post(route('loyalty.customers.request-otp', props.customer.id));
        redemptionPhase.value = 'verify';
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || 'Failed to send OTP. Please try again.';
    } finally {
        loading.value = false;
    }
};

const verifyAndRedeem = async () => {
    loading.value = true;
    errorMessage.value = '';
    try {
        await axios.post(route('loyalty.customers.verify-redeem', props.customer.id), {
            reward_id: selectedReward.value.id,
            otp: otpCode.value
        });
        
        // Success feedback
        errorMessage.value = '';
        window.location.reload(); // Quick refresh to update points balance
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || 'Verification failed. Please check the code.';
    } finally {
        loading.value = false;
    }
};
</script>
