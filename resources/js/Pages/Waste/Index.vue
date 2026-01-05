<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Stats Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 <div class="glass-card rounded-2xl p-6 card-hover">
                     <p class="text-sm font-medium text-gray-500">{{ $t('common.total') }} Loss Today</p>
                     <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency(totalLoss) }}</p>
                 </div>
                 <div class="glass-card rounded-2xl p-6 card-hover">
                     <p class="text-sm font-medium text-gray-500">Records</p>
                     <p class="text-2xl font-bold text-gray-900 mt-1">{{ logsList.length }}</p>
                 </div>
            </div>

            <!-- Table -->
            <Table
                :columns="columns"
                :data="filteredLogs"
                :pagination="props.logs"
                v-model:search="search"
                title="Waste Tracking"
            >
                <template #header-actions>
                    <div class="flex items-center gap-4">
                        <Input 
                            type="date" 
                            v-model="params.date"
                            placeholder="Select date"
                        />
                        <Button @click="openAddModal" variant="primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Log Waste
                        </Button>
                    </div>
                </template>

                <!-- Custom Slots -->
                <template #cell-user.name="{ row }">
                    <span class="font-medium text-gray-900">{{ row.user?.name || '-' }}</span>
                </template>

                <template #cell-ingredient.name="{ row }">
                    <div>
                        <span class="font-medium text-gray-900 block">{{ getLocaleName(row.ingredient?.name) || 'Unknown' }}</span>
                        <span class="text-xs text-gray-400" v-if="row.ingredient?.unit">({{ row.ingredient.unit }})</span>
                    </div>
                </template>

                <template #cell-stock_before="{ row }">
                    {{ row.stock_before !== null ? Number(row.stock_before).toFixed(2) : '-' }}
                </template>

                <template #cell-stock_after="{ row }">
                    {{ row.stock_after !== null ? Number(row.stock_after).toFixed(2) : '-' }}
                </template>

                <template #cell-waste_amount="{ row }">
                    <span class="font-bold text-red-600">{{ row.waste_amount }}</span>
                </template>

                <template #cell-total_loss="{ row }">
                    <span class="font-bold text-red-600">{{ formatCurrency(row.total_loss) }}</span>
                </template>

                <template #cell-status="{ row }">
                    <span v-if="row.deleted_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Deleted
                    </span>
                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $t('common.active') }}</span>
                </template>

                <template #actions="{ row }">
                    <div class="flex justify-end gap-2" v-if="!row.deleted_at">
                        <Button size="sm" variant="secondary" @click="openUpdateModal(row)">{{ $t('common.edit') }}</Button>
                        <Button size="sm" variant="danger" @click="deleteLog(row)">{{ $t('common.delete') }}</Button>
                    </div>
                    <div class="flex justify-end gap-2" v-else>
                         <Button size="sm" variant="primary" @click="restoreLog(row)">
                            Restore
                        </Button>
                    </div>
                </template>
            </Table>
        </div>

        <!-- Add Waste Modal -->
        <Modal :show="showAddModal" @close="closeAddModal" title="Log Ingredient Waste">
            <form @submit.prevent="submitAdd" class="space-y-4">
                <Select
                    v-model="addForm.ingredient_id"
                    @update:modelValue="addForm.ingredient_batch_id = ''"
                    label="Select Ingredient"
                    placeholder="Select an ingredient"
                    required
                >
                    <option v-for="item in ingredients" :key="item.id" :value="item.id">
                        {{ getLocaleName(item.name) }} (Total: {{ item.current_stock }} {{ item.unit }})
                        <span v-if="!item.is_active"> - Inactive</span>
                    </option>
                </Select>
                <p v-if="ingredients.length === 0" class="mt-2 text-sm text-red-600">
                    No ingredients found for this restaurant. Please add ingredients in the 
                    <a :href="route('inventory.index')" class="font-semibold underline hover:text-red-800">Inventory page</a> first.
                </p>

                <div v-if="addForm.ingredient_id">
                    <Select
                        v-model="addForm.ingredient_batch_id"
                        label="Select Batch"
                        placeholder="Select a batch (FIFO)"
                        required
                    >
                        <option v-for="batch in availableBatches" :key="batch.id" :value="batch.id">
                            {{ batch.batch_number }} — Qty: {{ Number(batch.quantity_remaining) }} — Cost: {{ formatCurrency(batch.cost_per_unit) }}
                        </option>
                    </Select>
                    <p v-if="availableBatches.length === 0" class="text-xs text-red-500 mt-1">No active batches available for this ingredient.</p>
                </div>

                <Input 
                    v-model="addForm.waste_amount"
                    label="Quantity Wasted"
                    type="number"
                    min="0.01"
                    step="0.01"
                    required
                />
                <Input
                    v-model="addForm.notes"
                    label="Notes (Optional)"
                    type="textarea"
                    rows="2"
                />
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeAddModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="addForm.processing" variant="danger">Log Waste</Button>
                </div>
            </form>
        </Modal>

        <!-- Update Waste Modal -->
        <Modal :show="showUpdateModal" @close="closeUpdateModal" title="Update Waste Log">
            <form @submit.prevent="submitUpdate" class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-xl mb-4">
                    <p class="text-sm text-gray-500">Ingredient: <span class="font-bold text-gray-900">{{ getLocaleName(selectedLog?.ingredient?.name) }}</span></p>
                    <p class="text-sm text-gray-500">Cost Basis: <span class="font-bold text-gray-900">{{ selectedLog?.cost_per_unit }}</span></p>
                </div>

                <Input 
                    v-model="updateForm.waste_amount"
                    label="Quantity Wasted"
                    type="number"
                    min="0.01"
                    step="0.01"
                    required
                />

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeUpdateModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="updateForm.processing" variant="primary">{{ $t('common.update') }}</Button>
                </div>
            </form>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Table from '@/Components/Table.vue';

const props = defineProps<{
    logs: any;
    ingredients: any[];
    filters: any;
}>();

const { locale } = useI18n();
const route = (window as any).route;
const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = [
    { key: 'log_date', label: 'Date', sortable: true, format: 'date' as const },
    { key: 'user.name', label: 'User', sortable: true },
    { key: 'ingredient.name', label: 'Ingredient', sortable: true },
    { key: 'stock_before', label: 'Before', align: 'right' as const },
    { key: 'waste_amount', label: 'Wasted', align: 'right' as const },
    { key: 'stock_after', label: 'After', align: 'right' as const },
    { key: 'total_loss', label: 'Loss', align: 'right' as const },
    { key: 'status', label: 'Status', align: 'center' as const },
];

const search = ref('');

// Formatting
const getLocaleName = (name: any) => {
    if (!name) return '';
    if (typeof name === 'string') return name;
    return name[locale.value] || name[$i18n.locale] || 'Unknown';
};

const formatCurrency = (amount: any) => {
    return new Intl.NumberFormat('en-AE', { style: 'currency', currency: currency.value }).format(Number(amount));
};

// State
const params = ref({
    date: props.filters.date || new Date().toISOString().split('T')[0]
});

// Watch Date
watch(() => params.value.date, (newDate) => {
    router.get(route('waste.index'), { date: newDate }, { preserveState: true });
});

const logsList = computed(() => props.logs.data || []);
const totalLoss = computed(() => logsList.value.reduce((sum: number, log: any) => sum + Number(log.total_loss), 0));

const filteredLogs = computed(() => {
    if (!search.value) return logsList.value;
    const q = search.value.toLowerCase();
    return logsList.value.filter((log: any) => {
        const iName = getLocaleName(log.ingredient?.name).toLowerCase();
        const uName = (log.user?.name || '').toLowerCase();
        return iName.includes(q) || uName.includes(q);
    });
});

// Add Modal
const showAddModal = ref(false);
const addForm = useForm({
    ingredient_id: '',
    ingredient_batch_id: '', 
    waste_amount: '',
    notes: '',
    log_date: params.value.date
});

const availableBatches = computed(() => {
    if (!addForm.ingredient_id) return [];
    // Handle both string and number IDs (MongoDB returns strings)
    const selectedId = String(addForm.ingredient_id);
    const ingredient = props.ingredients.find((i: any) => String(i.id) === selectedId);
    return ingredient?.batches || [];
});

const openAddModal = () => {
    addForm.reset();
    addForm.log_date = params.value.date;
    showAddModal.value = true;
};
    
const closeAddModal = () => showAddModal.value = false;

const submitAdd = () => {
    addForm.post(route('waste.store'), {
        onSuccess: () => closeAddModal()
    });
};

// Update Modal
const showUpdateModal = ref(false);
const selectedLog = ref<any>(null);
const updateForm = useForm({
    waste_amount: ''
});

const openUpdateModal = (log: any) => {
    selectedLog.value = log;
    updateForm.waste_amount = log.waste_amount;
    showUpdateModal.value = true;
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    selectedLog.value = null;
};

const submitUpdate = () => {
    if (!selectedLog.value) return;
    updateForm.put(route('waste.update', selectedLog.value.id), {
        onSuccess: () => closeUpdateModal()
    });
};

const deleteLog = (log: any) => {
    if (confirm('Are you sure you want to delete this waste record? This will restore the stock to inventory.')) {
        router.delete(route('waste.destroy', log.id));
    }
};

const restoreLog = (log: any) => {
    if (confirm('Are you sure you want to restore this waste record? This will deduct the stock from inventory again.')) {
        router.post(route('waste.restore', log.id));
    }
};
</script>
