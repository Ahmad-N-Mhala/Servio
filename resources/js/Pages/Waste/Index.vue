<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';

const props = defineProps<{
    logs: any;
    ingredients: any[];
    filters: any;
}>();

const { locale } = useI18n();
const route = (window as any).route;

// Formatting
const getLocaleName = (name: any) => {
    if (!name) return '';
    if (typeof name === 'string') return name;
    return name[locale.value] || name['en'] || 'Unknown';
};

const formatCurrency = (amount: any) => {
    return Number(amount).toFixed(2);
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

// Add Modal
const showAddModal = ref(false);
const addForm = useForm({
    ingredient_id: '',
    ingredient_batch_id: '', // Added 
    waste_amount: '',
    notes: '',
    log_date: params.value.date
});

const availableBatches = computed(() => {
    if (!addForm.ingredient_id) return [];
    // Ensure strict comparison works by matching types
    const selectedId = Number(addForm.ingredient_id);
    const ingredient = props.ingredients.find((i: any) => i.id === selectedId);
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

<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Waste Tracking (Ingredients)</h1>
                    <p class="mt-1 text-sm text-gray-500">Log wasted ingredients to track financial loss.</p>
                </div>
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <input 
                        type="date" 
                        v-model="params.date"
                        class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    >
                    <Button @click="openAddModal" variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Log Waste
                    </Button>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                 <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                     <p class="text-sm font-medium text-gray-500">Total Loss Today</p>
                     <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency(totalLoss) }}</p>
                 </div>
                 <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                     <p class="text-sm font-medium text-gray-500">Records</p>
                     <p class="text-2xl font-bold text-gray-900 mt-1">{{ logsList.length }}</p>
                 </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ingredient</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Before</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Wasted</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">After</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Loss</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="logsList.length === 0">
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                No waste records found for this date.
                            </td>
                        </tr>
                        <tr v-for="log in logsList" :key="log.id" class="hover:bg-gray-50" :class="{'bg-red-50': log.deleted_at}">
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ new Date(log.log_date).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                {{ log.user?.name || '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ getLocaleName(log.ingredient?.name) || 'Unknown' }}</span>
                                <span class="text-xs text-gray-400 block" v-if="log.ingredient?.unit">({{ log.ingredient.unit }})</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-500">
                                {{ log.stock_before !== null ? Number(log.stock_before).toFixed(2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-red-600">{{ log.waste_amount }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-500">
                                {{ log.stock_after !== null ? Number(log.stock_after).toFixed(2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-red-600">
                                {{ formatCurrency(log.total_loss) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="log.deleted_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Deleted
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div v-if="!log.deleted_at">
                                    <Button size="sm" variant="secondary" @click="openUpdateModal(log)" class="mr-2">
                                        Edit
                                    </Button>
                                    <Button size="sm" variant="danger" @click="deleteLog(log)">
                                        Delete
                                    </Button>
                                </div>
                                <div v-else>
                                    <Button size="sm" variant="primary" @click="restoreLog(log)">
                                        Restore
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Removed separate Activity Logs section as per request -->
        </div>

        <!-- Add Waste Modal -->
        <Modal :show="showAddModal" @close="closeAddModal" title="Log Ingredient Waste">
            <form @submit.prevent="submitAdd" class="space-y-4">
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Select Ingredient</label>
                     <select v-model="addForm.ingredient_id" @change="addForm.ingredient_batch_id = ''" class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary" required>
                         <option value="" disabled>Select an ingredient</option>
                         <option v-for="item in ingredients" :key="item.id" :value="item.id">
                             {{ getLocaleName(item.name) }} (Total: {{ item.current_stock }} {{ item.unit }})
                         </option>
                     </select>
                </div>

                <div v-if="addForm.ingredient_id">
                     <label class="block text-sm font-medium text-gray-700 mb-1">Select Batch</label>
                     <select v-model="addForm.ingredient_batch_id" class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary" required>
                         <option value="" disabled>Select a batch (FIFO)</option>
                         <option v-for="batch in availableBatches" :key="batch.id" :value="batch.id">
                             {{ batch.batch_number }} — Qty: {{ Number(batch.quantity_remaining) }} — Cost: {{ formatCurrency(batch.cost_per_unit) }}
                         </option>
                     </select>
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
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea v-model="addForm.notes" class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary" rows="2"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeAddModal">Cancel</Button>
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
                    <Button type="button" variant="secondary" @click="closeUpdateModal">Cancel</Button>
                    <Button type="submit" :loading="updateForm.processing" variant="primary">Update</Button>
                </div>
            </form>
        </Modal>

    </MainLayout>
</template>
