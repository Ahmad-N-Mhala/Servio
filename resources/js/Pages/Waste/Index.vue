<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Waste Tracking</h1>
                    <p class="mt-1 text-sm text-gray-500">Track production and minimize food waste.</p>
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
                        Add Stock
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
                     <p class="text-sm font-medium text-gray-500">Items Tracked</p>
                     <p class="text-2xl font-bold text-gray-900 mt-1">{{ logsList.length }}</p>
                 </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Added Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waste / Remaining</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loss</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="logsList.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No records found for this date.
                            </td>
                        </tr>
                        <tr v-for="log in logsList" :key="log.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ getLocaleName(log.menu_item?.name) || 'Unknown Item' }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ formatCurrency(log.cost_per_unit) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ log.added_amount }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="log.waste_amount > 0" class="flex items-center gap-2">
                                     <span class="font-bold text-red-600">{{ log.waste_amount }}</span>
                                     <span class="text-xs text-gray-400">(Remaining)</span>
                                </div>
                                <span v-else class="text-gray-400 italic">Not set</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-red-600">
                                {{ formatCurrency(log.total_loss) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Button size="sm" variant="secondary" @click="openUpdateModal(log)">
                                    {{ log.waste_amount > 0 ? 'Update Waste' : 'Log Waste' }}
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Stock Modal -->
        <Modal :show="showAddModal" @close="closeAddModal" title="Add Daily Stock">
            <form @submit.prevent="submitAdd" class="space-y-4">
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Select Item</label>
                     <select v-model="addForm.menu_item_id" class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary">
                         <option v-for="item in menuItems" :key="item.id" :value="item.id">
                             {{ getLocaleName(item.name) }} ({{ item.price }})
                         </option>
                     </select>
                </div>
                <Input 
                    v-model="addForm.added_amount"
                    label="Quantity Added"
                    type="number"
                    min="1"
                    required
                />
                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeAddModal">Cancel</Button>
                    <Button type="submit" :loading="addForm.processing">Add Stock</Button>
                </div>
            </form>
        </Modal>

        <!-- Update Waste Modal -->
        <Modal :show="showUpdateModal" @close="closeUpdateModal" title="Log Daily Waste">
            <form @submit.prevent="submitUpdate" class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-xl mb-4">
                    <p class="text-sm text-gray-500">Item: <span class="font-bold text-gray-900">{{ getLocaleName(selectedLog?.menu_item?.name) }}</span></p>
                    <p class="text-sm text-gray-500">Stock Added: <span class="font-bold text-gray-900">{{ selectedLog?.added_amount }}</span></p>
                </div>

                <Input 
                    v-model="updateForm.waste_amount"
                    label="Remaining Quantity (Waste)"
                    type="number"
                    min="0"
                    required
                    :max="selectedLog?.added_amount"
                />
                <p class="text-xs text-gray-500">Enter the amount remaining at the end of the day. This is considered waste.</p>

                <div class="flex justify-end gap-3 mt-6">
                    <Button type="button" variant="secondary" @click="closeUpdateModal">Cancel</Button>
                    <Button type="submit" :loading="updateForm.processing" variant="danger">Log Waste</Button>
                </div>
            </form>
        </Modal>

    </MainLayout>
</template>

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
    menuItems: any[];
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
    menu_item_id: '',
    added_amount: '',
    log_date: params.value.date
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

</script>
