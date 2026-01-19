<template>
    <MainLayout :title="$t('expenses.title')">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('expenses.title') }}</h1>
                    <p class="mt-2 text-gray-600">{{ $t('expenses.manage_description') }}</p>
                </div>
                <button
                    @click="showAddModal = true"
                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-hover transition-all duration-200 shadow-lg hover:shadow-xl font-semibold"
                >
                    + {{ $t('expenses.add_expense') }}
                </button>
            </div>

            <!-- Month Selector & Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- Month Selector -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <Select
                        v-model="selectedMonth"
                        @update:modelValue="filterByMonth"
                        :label="$t('expenses.select_month')"
                        :options="availableMonths"
                    />
                </div>

                <!-- Summary Cards -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">{{ $t('expenses.total_expenses') }}</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.total) }}</div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">{{ $t('expenses.paid') }}</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.paid) }}</div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">{{ $t('expenses.pending') }}</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.pending) }}</div>
                </div>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.category') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.description') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.amount') }}</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.status') }}</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.paid_date') }}</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">{{ $t('expenses.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Fixed Inventory Purchases Row -->
                            <tr class="bg-purple-50 border-b-2 border-purple-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-sm font-semibold rounded-lg bg-purple-100 text-purple-800 flex items-center gap-2 w-fit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        {{ $t('expenses.inventory_purchases') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 italic">{{ $t('expenses.auto_calculated_inventory') }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $t('expenses.inventory_update_note') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-lg font-bold text-purple-900">{{ formatCurrency(inventoryPurchases) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $t('expenses.auto') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    -
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-400">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </td>
                            </tr>

                            <!-- Regular Expenses -->
                            <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-sm font-semibold rounded-lg bg-blue-100 text-blue-800">
                                        {{ expense.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ expense.description || '-' }}</div>
                                    <div v-if="expense.notes" class="text-xs text-gray-500 mt-1">{{ expense.notes }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-lg font-bold text-gray-900">{{ formatCurrency(expense.amount) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        :class="expense.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                        class="px-3 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ expense.payment_status === 'paid' ? $t('expenses.paid') : $t('expenses.pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ expense.paid_at || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        @click="editExpense(expense)"
                                        class="text-blue-600 hover:text-blue-800 font-medium mr-3 transition-colors"
                                    >{{ $t('common.edit') }}</button>
                                    <button
                                        @click="deleteExpense(expense.id)"
                                        class="text-red-600 hover:text-red-800 font-medium transition-colors"
                                    >{{ $t('common.delete') }}</button>
                                </td>
                            </tr>
                            <tr v-if="expenses.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-lg font-medium">{{ $t('expenses.no_expenses') }}</div>
                                    <p class="text-sm mt-2">{{ $t('expenses.click_add_to_start') }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Modal -->
            <div v-if="showAddModal || editingExpense" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">
                            {{ editingExpense ? $t('expenses.edit_expense') : $t('expenses.add_new_expense') }}
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <Select
                                        v-model="form.category"
                                        :label="$t('expenses.category') + ' *'"
                                        :options="categories.map(c => ({ label: c, value: c }))"
                                        :placeholder="$t('expenses.select_category')"
                                        required
                                    />
                                </div>

                                <div>
                                    <Input
                                        v-model="form.amount"
                                        :label="$t('expenses.amount').replace('AED', currency) + ' *'"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <Input
                                    v-model="form.description"
                                    :label="$t('expenses.description')"
                                    :placeholder="$t('expenses.brief_description')"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <Select
                                        v-model="form.payment_status"
                                        :label="$t('expenses.payment_status') + ' *'"
                                        :options="[
                                            { label: $t('expenses.pending'), value: 'pending' },
                                            { label: $t('expenses.paid'), value: 'paid' }
                                        ]"
                                        required
                                    />
                                </div>

                                <div v-if="form.payment_status === 'paid'">
                                    <Input
                                        v-model="form.paid_at"
                                        :label="$t('expenses.paid_date')"
                                        type="date"
                                    />
                                </div>
                            </div>

                            <div>
                                <Input
                                    v-model="form.notes"
                                    :label="$t('expenses.notes')"
                                    type="textarea"
                                    :rows="3"
                                    :placeholder="$t('expenses.additional_notes')"
                                />
                            </div>

                            <div class="flex justify-end gap-4 pt-4">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-semibold transition-all"
                                >
                                    {{ $t('expenses.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-hover font-semibold transition-all shadow-lg"
                                >
                                    {{ editingExpense ? $t('common.update') : $t('common.create') }} {{ $t('expenses.add_expense') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';

const page = usePage();
const { t } = useI18n();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    expenses: any[];
    selectedMonth: string;
    availableMonths: any[];
    summary: {
        total: number;
        paid: number;
        pending: number;
    };
    categories: string[];
    inventoryPurchases: number;
}>();

const showAddModal = ref(false);
const editingExpense = ref<any>(null);
const selectedMonth = ref(props.selectedMonth);

const form = reactive({
    category: '',
    description: '',
    amount: '',
    month: props.selectedMonth,
    payment_status: 'pending',
    paid_at: '',
    notes: '',
});

const filterByMonth = () => {
    router.get((window as any).route('monthly-expenses.index'), { month: selectedMonth.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const submitForm = () => {
    const data = { ...form, month: selectedMonth.value };
    
    if (editingExpense.value) {
        router.put((window as any).route('monthly-expenses.update', editingExpense.value.id), data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post((window as any).route('monthly-expenses.store'), data, {
            onSuccess: () => closeModal(),
        });
    }
};

const editExpense = (expense: any) => {
    editingExpense.value = expense;
    Object.assign(form, {
        category: expense.category,
        description: expense.description || '',
        amount: expense.amount,
        payment_status: expense.payment_status,
        paid_at: expense.paid_at || '',
        notes: expense.notes || '',
    });
};

const deleteExpense = (id: string) => {
    if (confirm(t('expenses.delete_confirm') || 'Are you sure you want to delete this expense?')) {
        router.delete((window as any).route('monthly-expenses.destroy', id));
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingExpense.value = null;
    Object.assign(form, {
        category: '',
        description: '',
        amount: '',
        payment_status: 'pending',
        paid_at: '',
        notes: '',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: currency.value,
    }).format(amount);
};
</script>
