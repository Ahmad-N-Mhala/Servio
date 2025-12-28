<template>
    <MainLayout title="Monthly Expenses">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Monthly Expenses</h1>
                    <p class="mt-2 text-gray-600">Track and manage your monthly operating costs</p>
                </div>
                <button
                    @click="showAddModal = true"
                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-hover transition-all duration-200 shadow-lg hover:shadow-xl font-semibold"
                >
                    + Add Expense
                </button>
            </div>

            <!-- Month Selector & Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- Month Selector -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Month</label>
                    <select
                        v-model="selectedMonth"
                        @change="filterByMonth"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                        <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                </div>

                <!-- Summary Cards -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">Total Expenses</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.total) }}</div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">Paid</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.paid) }}</div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-sm font-medium opacity-90">Pending</div>
                    <div class="text-3xl font-bold mt-2">{{ formatCurrency(summary.pending) }}</div>
                </div>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Paid Date</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
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
                                        Inventory Purchases
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 italic">Auto-calculated from inventory stock additions</div>
                                    <div class="text-xs text-gray-500 mt-1">This value updates automatically when you add stock</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-lg font-bold text-purple-900">{{ formatCurrency(inventoryPurchases) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Auto
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
                                        {{ expense.payment_status === 'paid' ? 'Paid' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ expense.paid_at || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        @click="editExpense(expense)"
                                        class="text-blue-600 hover:text-blue-800 font-medium mr-3 transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteExpense(expense.id)"
                                        class="text-red-600 hover:text-red-800 font-medium transition-colors"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="expenses.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-lg font-medium">No expenses for this month</div>
                                    <p class="text-sm mt-2">Click "Add Expense" to get started</p>
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
                            {{ editingExpense ? 'Edit Expense' : 'Add New Expense' }}
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                    <select
                                        v-model="form.category"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                    >
                                        <option value="">Select category</option>
                                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount ({{ currency }}) *</label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <input
                                    v-model="form.description"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Brief description"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Status *</label>
                                    <select
                                        v-model="form.payment_status"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>

                                <div v-if="form.payment_status === 'paid'">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Paid Date</label>
                                    <input
                                        v-model="form.paid_at"
                                        type="date"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Additional notes..."
                                ></textarea>
                            </div>

                            <div class="flex justify-end gap-4 pt-4">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-semibold transition-all"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-hover font-semibold transition-all shadow-lg"
                                >
                                    {{ editingExpense ? 'Update' : 'Add' }} Expense
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
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
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
    if (confirm('Are you sure you want to delete this expense?')) {
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
