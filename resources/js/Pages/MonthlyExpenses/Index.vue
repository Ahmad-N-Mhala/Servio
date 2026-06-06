<template>
    <MainLayout :title="$t('expenses.title')">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('expenses.title') }}</h1>
                    <p class="mt-2 text-gray-600">{{ $t('expenses.manage_description') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <Button
                        @click="exportExcel"
                        variant="outline"
                        size="md"
                    >
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </template>
                        {{ $t('common.export') || 'Export' }}
                    </Button>
                    <Button
                        @click="showAddModal = true"
                        variant="primary"
                        size="md"
                    >
                        + {{ $t('expenses.add_expense') }}
                    </Button>
                </div>
            </div>

            <!-- Month Selector & Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- Month/Year Selector -->
                <div class="bg-white rounded-2xl shadow-lg p-5 border border-gray-100 flex flex-col justify-center">
                    <div class="grid grid-cols-2 gap-2">
                        <Select
                            v-model="selectedMonthOnly"
                            @update:modelValue="filterByDate"
                            :label="$t('expenses.month') || 'Month'"
                            :options="monthOptions"
                            :clearable="false"
                        />
                        <Select
                            v-model="selectedYear"
                            @update:modelValue="filterByDate"
                            :label="$t('expenses.year') || 'Year'"
                            :options="yearOptions"
                            :clearable="false"
                        />
                    </div>
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

            <Table
                :columns="columns"
                :data="tableData"
                rowKey="id"
                :highlightRow="(row) => row.is_inventory_purchase"
                :emptyMessage="$t('expenses.no_expenses') || 'No expenses'"
            >
                <template #cell-category="{ row }">
                    <span 
                        :class="row.is_inventory_purchase 
                            ? 'px-3 py-1 text-sm font-semibold rounded-lg bg-purple-100 text-purple-800 flex items-center gap-2 w-fit' 
                            : 'px-3 py-1 text-sm font-semibold rounded-lg bg-blue-100 text-blue-800'"
                    >
                        <svg v-if="row.is_inventory_purchase" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ row.category }}
                    </span>
                </template>

                <template #cell-description="{ row }">
                    <div v-if="row.is_inventory_purchase">
                        <div class="text-sm text-gray-700 italic">{{ $t('expenses.auto_calculated_inventory') }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $t('expenses.inventory_update_note') }}</div>
                    </div>
                    <div v-else>
                        <div class="text-sm text-gray-900">{{ row.description || '-' }}</div>
                        <div v-if="row.notes" class="text-xs text-gray-500 mt-1">{{ row.notes }}</div>
                    </div>
                </template>

                <template #cell-amount="{ row }">
                    <span :class="row.is_inventory_purchase ? 'text-lg font-bold text-purple-900' : 'text-lg font-bold text-gray-900'">
                        {{ formatCurrency(row.amount) }}
                    </span>
                </template>

                <template #cell-payment_status="{ row }">
                    <span
                        v-if="row.is_inventory_purchase"
                        class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800"
                    >
                        {{ $t('expenses.auto') }}
                    </span>
                    <span
                        v-else
                        :class="row.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                        class="px-3 py-1 text-xs font-semibold rounded-full"
                    >
                        {{ row.payment_status === 'paid' ? $t('expenses.paid') : $t('expenses.pending') }}
                    </span>
                </template>

                <template #cell-paid_at="{ row }">
                    <span class="text-sm text-gray-600">{{ row.paid_at || '-' }}</span>
                </template>

                <template #cell-evidence_files="{ row }">
                    <div v-if="row.is_inventory_purchase" class="text-gray-400">-</div>
                    <div v-else-if="row.evidence_files && row.evidence_files.length > 0" class="flex flex-col gap-1 items-center">
                        <a v-for="(file, index) in row.evidence_files" :key="index" :href="file.url" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 px-2 py-1 rounded-full border border-blue-100">
                            Doc {{ Number(index) + 1 }}
                        </a>
                    </div>
                    <span v-else class="text-gray-400">-</span>
                </template>

                <template #actions="{ row }">
                    <div v-if="row.is_inventory_purchase" class="text-gray-400">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div v-else class="flex gap-2 justify-end">
                        <Button
                            @click="editExpense(row)"
                            variant="ghost"
                            size="sm"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            {{ $t('common.edit') }}
                        </Button>
                        <Button
                            @click="deleteExpense(row.id)"
                            variant="ghost"
                            size="sm"
                            class="text-red-600 hover:text-red-800"
                        >
                            {{ $t('common.delete') }}
                        </Button>
                    </div>
                </template>
            </Table>

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
                                        :label="$t('expenses.category')"
                                        :options="categories.map(c => ({ label: c, value: c }))"
                                        :placeholder="$t('expenses.select_category')"
                                        required
                                    />
                                </div>

                                <div>
                                    <Input
                                        v-model="form.amount"
                                        :label="$t('expenses.amount').replace('AED', currency)"
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
                                        :label="$t('expenses.payment_status')"
                                        :options="[
                                            { label: $t('expenses.pending'), value: 'pending' },
                                            { label: $t('expenses.paid'), value: 'paid' }
                                        ]"
                                        required
                                    />
                                </div>

                                <div>
                                    <Input
                                        v-model="form.paid_at"
                                        :label="$t('expenses.paid_date') || 'Paid Date'"
                                        type="date"
                                    />
                                </div>
                            </div>

                            <div>
                                <Input
                                    v-model="form.notes"
                                    :label="$t('expenses.notes') || 'Notes'"
                                    type="textarea"
                                    :rows="3"
                                    :placeholder="$t('expenses.additional_notes') || 'Additional notes...'"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('expenses.evidence_documents') || 'Evidence Documents' }}</label>
                                <input
                                    type="file"
                                    multiple
                                    @change="handleFileChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-hover mb-2"
                                />
                                <div v-if="editingExpense && editingExpense.evidence_files && editingExpense.evidence_files.length" class="mt-2 text-sm text-gray-600">
                                    Current files: <span class="font-bold border bg-gray-100 rounded-md px-1.5">{{ editingExpense.evidence_files.length }}</span>. Uploading new files will preserve existing ones.
                                </div>
                            </div>

                            <div class="flex justify-end gap-4 pt-4">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="md"
                                    @click="closeModal"
                                >
                                    {{ $t('expenses.cancel') }}
                                </Button>
                                <Button
                                    type="submit"
                                    variant="primary"
                                    size="md"
                                >
                                    {{ editingExpense ? $t('common.update') : $t('common.create') }} {{ $t('expenses.add_expense') }}
                                </Button>
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
import Table from '@/Components/Table.vue';
import Button from '@/Components/Button.vue';

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

const columns = [
    { key: 'category', label: t('expenses.category') },
    { key: 'description', label: t('expenses.description') },
    { key: 'amount', label: t('expenses.amount'), align: 'right' as const },
    { key: 'payment_status', label: t('expenses.status'), align: 'center' as const },
    { key: 'paid_at', label: t('expenses.paid_date'), align: 'center' as const },
    { key: 'evidence_files', label: t('expenses.evidence') || 'Evidence', align: 'center' as const }
];

const tableData = computed(() => {
    return [
        {
            id: 'inventory_purchases',
            is_inventory_purchase: true,
            category: t('expenses.inventory_purchases'),
            description: t('expenses.auto_calculated_inventory'),
            amount: props.inventoryPurchases,
            payment_status: 'auto',
            paid_at: '-',
            evidence_files: []
        },
        ...props.expenses
    ];
});

const showAddModal = ref(false);
const editingExpense = ref<any>(null);
const selectedMonth = ref(props.selectedMonth);

// Derive monthOnly and year
const [initYear, initMonth] = props.selectedMonth.split('-');
const selectedYear = ref(initYear);
const selectedMonthOnly = ref(initMonth);

const monthOptions = [
    { value: '01', label: t('months.jan', 'January') },
    { value: '02', label: t('months.feb', 'February') },
    { value: '03', label: t('months.mar', 'March') },
    { value: '04', label: t('months.apr', 'April') },
    { value: '05', label: t('months.may', 'May') },
    { value: '06', label: t('months.jun', 'June') },
    { value: '07', label: t('months.jul', 'July') },
    { value: '08', label: t('months.aug', 'August') },
    { value: '09', label: t('months.sep', 'September') },
    { value: '10', label: t('months.oct', 'October') },
    { value: '11', label: t('months.nov', 'November') },
    { value: '12', label: t('months.dec', 'December') }
];

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 5 }, (_, i) => {
    const y = (currentYear - 2 + i).toString();
    return { value: y, label: y };
});

const form = reactive({
    category: '',
    description: '',
    amount: '',
    month: props.selectedMonth,
    payment_status: 'pending',
    paid_at: '',
    notes: '',
    evidence_files: [] as File[],
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        form.evidence_files = Array.from(target.files);
    }
};

const filterByDate = () => {
    selectedMonth.value = `${selectedYear.value}-${selectedMonthOnly.value}`;
    router.get((window as any).route('monthly-expenses.index'), { month: selectedMonth.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportExcel = () => {
    window.location.href = (window as any).route('monthly-expenses.index', { 
        month: selectedMonth.value,
        export: 'excel'
    });
};

const submitForm = () => {
    const data: any = { ...form, month: selectedMonth.value };
    
    if (editingExpense.value) {
        data._method = 'put';
        router.post((window as any).route('monthly-expenses.update', editingExpense.value.id), data, {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        router.post((window as any).route('monthly-expenses.store'), data, {
            onSuccess: () => closeModal(),
            forceFormData: true,
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
        evidence_files: [],
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
        evidence_files: [],
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: currency.value,
    }).format(amount);
};
</script>
