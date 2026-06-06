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
                    <h1 class="text-3xl font-bold text-gray-900">{{ customer.name || $t('loyalty.unknown_member') }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                         <span class="text-sm text-gray-500">{{ customer.phone }}</span>
                         <span class="text-gray-300">•</span>
                         <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider">{{ $t('loyalty.' + (customer.loyalty_tier || 'bronze').toLowerCase()) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Stats & Profile -->
                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-50 pb-2">{{ $t('loyalty.points_balance') }}</h3>
                        <div class="flex items-end gap-2">
                            <span class="text-5xl font-black text-primary font-display leading-none">{{ customer.loyalty_points?.balance || 0 }}</span>
                            <span class="text-sm font-bold text-gray-400 mb-1">{{ $t('loyalty.points') }}</span>
                        </div>
                        
                        <div class="mt-8 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ $t('loyalty.total_earned') }}</span>
                                <span class="font-bold text-gray-900">{{ customer.loyalty_points?.total_earned || 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ $t('loyalty.total_redeemed') }}</span>
                                <span class="font-bold text-gray-900 text-red-500">{{ customer.loyalty_points?.total_redeemed || 0 }}</span>
                            </div>
                            <div class="pt-4 border-t border-gray-50 flex justify-between text-sm">
                                <span class="text-gray-500">{{ $t('loyalty.total_spent') }}</span>
                                <span class="font-bold text-gray-900">{{ currency }} {{ customer.total_spent || '0.00' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Available Rewards for this customer -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-bold text-gray-900">{{ $t('loyalty.available_rewards') }}</h2>
                        <div v-if="availableRewards.length > 0" class="space-y-3">
                            <div v-for="reward in availableRewards" :key="reward.id" 
                                class="glass-card p-4 rounded-xl border border-gray-100 flex justify-between items-center group hover:border-primary/50 transition-all cursor-pointer shadow-sm"
                                @click="prepareRedemption(reward)"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ getLocaleName(reward.name) }}</h4>
                                    <p class="text-xs text-primary font-bold">{{ reward.points_required }} {{ $t('loyalty.points_required') }}</p>
                                </div>
                                <div class="bg-gray-100 group-hover:bg-primary group-hover:text-white p-2 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 italic p-4 text-center bg-gray-50 rounded-xl">
                            {{ $t('loyalty.no_rewards') }}
                        </div>
                    </div>
                </div>

                <!-- Right Column: Transactions & History -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Point Transactions -->
                    <div class="glass-card rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-900 font-display">{{ $t('loyalty.point_transactions') }}</h2>
                        </div>
                        
                        <Table
                            :columns="tableColumns"
                            :data="customer.point_transactions || []"
                            :emptyMessage="$t('loyalty.no_transactions')"
                        >
                            <template #cell-created_at="{ row }">
                                <span class="text-xs text-gray-500">
                                    {{ new Date(row.created_at).toLocaleDateString() }}
                                </span>
                            </template>
                            <template #cell-description="{ row }">
                                <p class="text-sm font-medium text-gray-900">{{ row.description }}</p>
                                <p v-if="row.reference_type" class="text-xs text-gray-400">Ref: {{ row.reference_type }}</p>
                            </template>
                            <template #cell-points="{ row }">
                                <span class="text-sm font-bold" :class="row.points > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ row.points > 0 ? '+' : '' }}{{ row.points }}
                                </span>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>

            <!-- Redemption Modal -->
            <Modal :show="showRedeemModal" @close="showRedeemModal = false" :title="$t('loyalty.redeem_reward')" size="md">
                <div class="space-y-6">
                    <div class="text-center p-6 bg-primary/5 rounded-2xl border border-primary/10">
                        <p class="text-sm text-gray-500 uppercase tracking-widest font-bold mb-2">{{ $t('loyalty.you_are_redeeming') }}</p>
                        <h3 class="text-2xl font-black text-gray-900 mb-1">{{ getLocaleName(selectedReward?.name) }}</h3>
                        <p class="text-primary font-bold text-lg">{{ selectedReward?.points_required }} {{ $t('loyalty.points') }}</p>
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
                            <p>{{ $t('loyalty.security_code_msg') }}: <strong>{{ customer.phone }}</strong>.</p>
                        </div>
                        <Button class="w-full" @click="requestOtp" :loading="loading">{{ $t('loyalty.send_verification') }}</Button>
                    </div>

                    <!-- Verify Phase -->
                    <div v-if="redemptionPhase === 'verify'" class="space-y-6">
                        <div v-if="errorMessage" class="p-4 bg-red-50 text-red-700 rounded-xl text-sm border border-red-100 mb-4 animate-shake">
                            {{ errorMessage }}
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-4">{{ $t('loyalty.enter_code_msg', { phone: customer.phone }) }}</p>
                            
                            <!-- 6 Digit Input Grid -->
                            <div class="flex justify-center gap-2" dir="ltr">
                                <template v-for="(_, index) in 6" :key="index">
                                    <input 
                                        :ref="el => otpInputs[index] = el"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="1"
                                        class="w-12 h-14 text-2xl font-bold text-center border-2 rounded-xl focus:border-primary focus:ring-primary transition-all shadow-sm"
                                        :class="errorMessage ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                                        placeholder="-"
                                        @input="handleOtpInput($event, index)"
                                        @keydown="handleOtpKeydown($event, index)"
                                        @paste="handleOtpPaste"
                                    >
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3">
                            <Button class="w-full" @click="verifyAndRedeem" :loading="loading" :disabled="otpCode.length < 6">{{ $t('loyalty.verify_redeem') }}</Button>
                            
                            <div class="text-center">
                                <button 
                                    @click="requestOtp" 
                                    class="text-sm font-bold hover:underline transition-colors"
                                    :class="resendTimer > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-primary'"
                                    :disabled="loading || resendTimer > 0"
                                >
                                    {{ resendTimer > 0 ? $t('loyalty.resend_code_in', { seconds: resendTimer }) : $t('loyalty.resend_code') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button @click="showRedeemModal = false" class="block w-full text-center text-sm text-gray-400 hover:text-gray-600 transition-colors">{{ $t('common.cancel') }}</button>
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
import Table from '@/Components/Table.vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    customer: any;
    rewards: any[];
}>();

const { locale, t } = useI18n();
const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const route = (window as any).route;

const tableColumns = computed(() => [
    { key: 'created_at', label: t('orders.date'), sortable: true },
    { key: 'description', label: t('loyalty.activity'), sortable: true },
    { key: 'points', label: t('loyalty.points'), sortable: true, align: 'right' as const }
]);

const showRedeemModal = ref(false);
const selectedReward = ref<any>(null);
const redemptionPhase = ref<'request' | 'verify'>('request');
const otpCode = ref('');
const loading = ref(false);
const errorMessage = ref('');

const resendTimer = ref(0);
const otpInputs = ref<any[]>([]);

const availableRewards = computed(() => {
    const balance = props.customer.loyalty_points?.balance || 0;
    return (props.rewards || []).filter(r => r.points_required <= balance && r.is_active);
});

const getLocaleName = (name: any) => {
    if (!name) return t('common.unknown') || 'Unknown';
    if (typeof name === 'string') return name;
    return name[locale.value] || Object.values(name)[0] || '';
};

const prepareRedemption = (reward: any) => {
    selectedReward.value = reward;
    redemptionPhase.value = 'request';
    otpCode.value = '';
    errorMessage.value = '';
    showRedeemModal.value = true;
};

const startResendTimer = () => {
    resendTimer.value = 60;
    const interval = setInterval(() => {
        if (resendTimer.value > 0) {
            resendTimer.value--;
        } else {
            clearInterval(interval);
        }
    }, 1000);
};

const requestOtp = async () => {
    loading.value = true;
    errorMessage.value = '';
    try {
        await axios.post(route('loyalty.customers.request-otp', props.customer.id));
        redemptionPhase.value = 'verify';
        startResendTimer();
        // Clear OTP inputs on fresh request
        otpCode.value = '';
        if (otpInputs.value[0]) {
             // slight delay to allow v-if render
            setTimeout(() => otpInputs.value[0]?.focus(), 100);
        }
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || t('loyalty.otp_send_failed');
    } finally {
        loading.value = false;
    }
};

const handleOtpInput = (event: Event, index: number) => {
    const input = event.target as HTMLInputElement;
    const value = input.value;

    if (!/^\d*$/.test(value)) {
        input.value = '';
        return;
    }

    // Update main model
    const currentOtp = otpCode.value.split('');
    // Ensure array size
    while(currentOtp.length < 6) currentOtp.push('');
    
    currentOtp[index] = value.slice(-1); // Take last char if multiple
    otpCode.value = currentOtp.join('').substring(0, 6);
    
    // Auto-advance focus
    if (value && index < 5) {
        otpInputs.value[index + 1]?.focus();
    }
    
    // If all filled, verify? Optional.
};

const handleOtpKeydown = (event: KeyboardEvent, index: number) => {
    if (event.key === 'Backspace') {
         if (!(event.target as HTMLInputElement).value && index > 0) {
            // If empty and backspace, move previous and focus
            otpInputs.value[index - 1]?.focus();
        } else {
            // Standard backspace clears current model index too via input update logic usually,
            // but let's handle clearing manually to be safe for mapped model
             // we let the input event handle the clearing of the value in the box
             // but if we are moving back we might want to ensure previous input is focused
        }
    }
};

const handleOtpPaste = (event: ClipboardEvent) => {
    const pasteData = event.clipboardData?.getData('text') || '';
    if (!/^\d{6}$/.test(pasteData)) return; // Only accept 6 digits

    event.preventDefault();
    otpCode.value = pasteData;
    
    // Fill inputs visually
    pasteData.split('').forEach((char, idx) => {
        if (otpInputs.value[idx]) otpInputs.value[idx].value = char;
    });
    
    otpInputs.value[5]?.focus();
};


const verifyAndRedeem = async () => {
    loading.value = true;
    errorMessage.value = '';
    
    // Ensure code is fully collected if using multi-input
    // The v-model otpCode might desync if we rely solely on separate inputs without binding them back carefully.
    // Let's re-gather from inputs just to be safe or rely on handleOtpInput updates.
    // Our handleOtpInput updates otpCode.value.
    
    try {
        await axios.post(route('loyalty.customers.verify-redeem', props.customer.id), {
            reward_id: selectedReward.value.id,
            otp: otpCode.value
        });
        
        // Success feedback
        errorMessage.value = '';
        window.location.reload(); // Quick refresh to update points balance
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || t('loyalty.verification_failed');
    } finally {
        loading.value = false;
    }
};
</script>
