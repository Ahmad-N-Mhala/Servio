<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cash Register</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Manage your daily cash register operations</p>
            </div>

            <!-- Current Register Status -->
            <div v-if="!currentRegister" class="glass-card rounded-2xl p-8 mb-6">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                        <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No Active Cash Register</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Open a new cash register to start accepting cash payments</p>
                    <Button @click="showOpenModal = true" variant="primary" size="lg">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </template>
                        Open Cash Register
                    </Button>
                </div>
            </div>

            <!-- Active Register -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Current Balance Card -->
                <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Current Session</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Opened {{ formatDateTime(currentRegister.opened_at) }}
                            </p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            OPEN
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Opening Balance</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(currentRegister.opening_balance) }}
                            </p>
                        </div>
                        <div class="bg-primary/10 rounded-xl p-4">
                            <p class="text-sm text-primary mb-1">Current Balance</p>
                            <p class="text-2xl font-bold text-primary">
                                {{ formatCurrency(currentBalance) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button @click="showWithdrawModal = true" variant="secondary" class="flex-1">
                            <template #icon>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </template>
                            Withdraw Cash
                        </Button>
                        <Button @click="showDepositModal = true" variant="success" class="flex-1">
                            <template #icon>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </template>
                            Add Cash
                        </Button>
                        <Button @click="showCloseModal = true" variant="danger" class="flex-1">
                            <template #icon>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </template>
                            Close Register
                        </Button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="space-y-4">
                    <div class="glass-card rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Sales</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(getTotalSales()) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Withdrawals</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(getTotalWithdrawals()) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Deposits</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(getTotalDeposits()) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions History -->
            <div v-if="currentRegister" class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
                
                <div v-if="currentRegister.transactions && currentRegister.transactions.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="transaction in currentRegister.transactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ formatTime(transaction.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold capitalize" :class="{
                                        'bg-green-100 text-green-700': transaction.type === 'sale' || transaction.type === 'deposit',
                                        'bg-red-100 text-red-700': transaction.type === 'withdrawal',
                                        'bg-blue-100 text-blue-700': transaction.type === 'opening',
                                        'bg-gray-100 text-gray-700': transaction.type === 'closing'
                                    }">
                                        {{ transaction.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :class="{
                                    'text-green-600': transaction.amount > 0,
                                    'text-red-600': transaction.amount < 0,
                                    'text-gray-600': transaction.amount === 0
                                }">
                                    {{ transaction.amount > 0 ? '+' : '' }}{{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(transaction.balance_after) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ transaction.notes || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No transactions yet
                </div>
            </div>

            <!-- Recent Closed Registers -->
            <div v-if="recentRegisters && recentRegisters.length > 0" class="glass-card rounded-2xl p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Closed Registers</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opening</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actual</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="register in recentRegisters" :key="register.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(register.closed_at) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ register.user?.name || 'Unknown' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(register.opening_balance) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(register.expected_balance) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ formatCurrency(register.closing_balance) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold" :class="{
                                    'text-green-600': register.difference > 0,
                                    'text-red-600': register.difference < 0,
                                    'text-gray-600': register.difference === 0
                                }">
                                    {{ register.difference > 0 ? '+' : '' }}{{ formatCurrency(register.difference) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Open Register Modal -->
        <Modal :show="showOpenModal" @close="closeOpenModal" title="Open Cash Register">
            <form @submit.prevent="submitOpen" class="space-y-4">
                <Input
                    v-model="openForm.opening_balance"
                    label="Opening Balance"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    :error="openForm.errors.opening_balance"
                    placeholder="0.00"
                />

                <Input
                    v-model="openForm.opening_notes"
                    label="Notes (Optional)"
                    type="textarea"
                    rows="3"
                    :error="openForm.errors.opening_notes"
                    placeholder="Any notes about the opening balance..."
                />

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeOpenModal">Cancel</Button>
                    <Button type="submit" :loading="openForm.processing">Open Register</Button>
                </div>
            </form>
        </Modal>

        <!-- Close Register Modal -->
        <Modal :show="showCloseModal" @close="closeCloseModal" title="Close Cash Register">
            <form @submit.prevent="submitClose" class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Expected Balance</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(currentBalance) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Difference</p>
                            <p class="text-xl font-bold" :class="{
                                'text-green-600': calculateDifference() > 0,
                                'text-red-600': calculateDifference() < 0,
                                'text-gray-600': calculateDifference() === 0
                            }">
                                {{ calculateDifference() > 0 ? '+' : '' }}{{ formatCurrency(calculateDifference()) }}
                            </p>
                        </div>
                    </div>
                </div>

                <Input
                    v-model="closeForm.closing_balance"
                    label="Actual Closing Balance"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    :error="closeForm.errors.closing_balance"
                    placeholder="Count the cash in the register..."
                />

                <Input
                    v-model="closeForm.closing_notes"
                    label="Closing Notes (Optional)"
                    type="textarea"
                    rows="3"
                    :error="closeForm.errors.closing_notes"
                    placeholder="Any notes about the closing..."
                />

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeCloseModal">Cancel</Button>
                    <Button type="submit" variant="danger" :loading="closeForm.processing">Close Register</Button>
                </div>
            </form>
        </Modal>

        <!-- Withdraw Modal -->
        <Modal :show="showWithdrawModal" @close="closeWithdrawModal" title="Withdraw Cash">
            <form @submit.prevent="submitWithdraw" class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-4">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Current Balance: <span class="font-bold">{{ formatCurrency(currentBalance) }}</span></p>
                </div>

                <Input
                    v-model="withdrawForm.amount"
                    label="Amount to Withdraw"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :max="currentBalance"
                    required
                    :error="withdrawForm.errors.amount"
                    placeholder="0.00"
                />

                <Input
                    v-model="withdrawForm.notes"
                    label="Reason for Withdrawal"
                    type="textarea"
                    rows="3"
                    required
                    :error="withdrawForm.errors.notes"
                    placeholder="e.g., Bank deposit, petty cash, etc..."
                />

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeWithdrawModal">Cancel</Button>
                    <Button type="submit" variant="danger" :loading="withdrawForm.processing">Withdraw</Button>
                </div>
            </form>
        </Modal>

        <!-- Deposit Modal -->
        <Modal :show="showDepositModal" @close="closeDepositModal" title="Add Cash">
            <form @submit.prevent="submitDeposit" class="space-y-4">
                <Input
                    v-model="depositForm.amount"
                    label="Amount to Add"
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                    :error="depositForm.errors.amount"
                    placeholder="0.00"
                />

                <Input
                    v-model="depositForm.notes"
                    label="Reason for Deposit"
                    type="textarea"
                    rows="3"
                    required
                    :error="depositForm.errors.notes"
                    placeholder="e.g., Change from bank, returned cash, etc..."
                />

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeDepositModal">Cancel</Button>
                    <Button type="submit" variant="success" :loading="depositForm.processing">Add Cash</Button>
                </div>
            </form>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';

const page = usePage();
const route = (window as any).route;

const props = defineProps<{
    currentRegister: any;
    currentBalance: number;
    recentRegisters: any[];
}>();

const showOpenModal = ref(false);
const showCloseModal = ref(false);
const showWithdrawModal = ref(false);
const showDepositModal = ref(false);

const openForm = useForm({
    opening_balance: '',
    opening_notes: '',
});

const closeForm = useForm({
    closing_balance: '',
    closing_notes: '',
});

const withdrawForm = useForm({
    amount: '',
    notes: '',
});

const depositForm = useForm({
    amount: '',
    notes: '',
});

const submitOpen = () => {
    openForm.post(route('cash-register.open'), {
        onSuccess: () => {
            closeOpenModal();
        },
    });
};

const submitClose = () => {
    closeForm.post(route('cash-register.close', props.currentRegister.id), {
        onSuccess: () => {
            closeCloseModal();
        },
    });
};

const submitWithdraw = () => {
    withdrawForm.post(route('cash-register.withdraw', props.currentRegister.id), {
        onSuccess: () => {
            closeWithdrawModal();
        },
    });
};

const submitDeposit = () => {
    depositForm.post(route('cash-register.deposit', props.currentRegister.id), {
        onSuccess: () => {
            closeDepositModal();
        },
    });
};

const closeOpenModal = () => {
    showOpenModal.value = false;
    openForm.reset();
    openForm.clearErrors();
};

const closeCloseModal = () => {
    showCloseModal.value = false;
    closeForm.reset();
    closeForm.clearErrors();
};

const closeWithdrawModal = () => {
    showWithdrawModal.value = false;
    withdrawForm.reset();
    withdrawForm.clearErrors();
};

const closeDepositModal = () => {
    showDepositModal.value = false;
    depositForm.reset();
    depositForm.clearErrors();
};

const calculateDifference = () => {
    if (!closeForm.closing_balance) return 0;
    return parseFloat(closeForm.closing_balance) - props.currentBalance;
};

const getTotalSales = () => {
    if (!props.currentRegister?.transactions) return 0;
    return props.currentRegister.transactions
        .filter((t: any) => t.type === 'sale')
        .reduce((sum: number, t: any) => sum + parseFloat(t.amount), 0);
};

const getTotalWithdrawals = () => {
    if (!props.currentRegister?.transactions) return 0;
    return Math.abs(props.currentRegister.transactions
        .filter((t: any) => t.type === 'withdrawal')
        .reduce((sum: number, t: any) => sum + parseFloat(t.amount), 0));
};

const getTotalDeposits = () => {
    if (!props.currentRegister?.transactions) return 0;
    return props.currentRegister.transactions
        .filter((t: any) => t.type === 'deposit')
        .reduce((sum: number, t: any) => sum + parseFloat(t.amount), 0);
};

const formatCurrency = (value: number) => {
    const currency = (page.props.current_restaurant as any)?.currency || 'AED';
    return new Intl.NumberFormat('en-AE', { 
        style: 'currency', 
        currency: currency 
    }).format(value || 0);
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
