<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Table Component -->
            <Table
                :columns="columns"
                :data="filteredIngredients"
                v-model:search="search"
                :title="$t('inventory.title', 'Inventory Management')"
            >
                <template #header-actions>
                    <Button @click="openCreateModal">
                        {{ $t('inventory.add_item', 'Add Raw Item') }}
                    </Button>
                </template>

                <!-- Name Column -->
                <template #cell-name="{ row }">
                    <button 
                        @click="openBatchesModal(row)"
                        class="font-medium text-primary hover:text-primary-hover hover:underline text-left transition-all"
                    >
                        {{ getLocaleName(row.name) }}
                    </button>
                </template>

                <!-- Stock Column -->
                <template #cell-current_stock="{ row }">
                    <div class="flex items-center gap-2">
                        <span 
                            class="font-bold" 
                            :class="{'text-red-600': parseFloat(row.current_stock) <= parseFloat(row.reorder_level || 0)}"
                        >
                            {{ row.current_stock }}
                        </span>
                        <span 
                            v-if="parseFloat(row.current_stock) <= parseFloat(row.reorder_level || 0)" 
                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 border border-red-200"
                        >
                            Low
                        </span>
                    </div>
                </template>

                <!-- Total Value Column -->
                <template #cell-total_value="{ row }">
                    <span class="font-bold text-gray-900">
                        {{ formatCurrency(row.current_stock * row.cost) }}
                    </span>
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-2">
                        <button 
                            @click="openAddStockModal(row)" 
                            class="text-green-600 hover:text-green-800 p-1 rounded-md hover:bg-green-50 transition-colors"
                            title="Add Stock"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </button>
                        <button 
                            @click="openHistoryModal(row)" 
                            class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-50 transition-colors"
                            title="History"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <button 
                            @click="openEditModal(row)" 
                            class="text-gray-600 hover:text-gray-800 p-1 rounded-md hover:bg-gray-50 transition-colors"
                            title="Edit"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button 
                            @click="deleteItem(row)" 
                            class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-50 transition-colors"
                            title="Delete"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
            </Table>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal" @close="closeCreateModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ isEditing ? 'Edit Ingredient' : 'Add New Ingredient' }}
                </h3>
                <form @submit.prevent="submitCreate">
                    <div class="mb-4">
                        <Input id="name" type="text" v-model="form.name" label="Name" required :error="form.errors.name" />
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <Input id="stock" type="number" step="0.0001" v-model="form.current_stock" label="Initial Stock" required :disabled="isEditing" :error="form.errors.current_stock" />
                            <p v-if="isEditing" class="text-xs text-gray-500 mt-1">Use 'Add Stock' to update inventory.</p>
                        </div>
                        <div>
                            <Input id="unit" type="text" v-model="form.unit" label="Unit (e.g. kg, pcs)" required :error="form.errors.unit" />
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                         <div>
                            <Input id="cost" type="number" step="0.01" v-model="form.cost" label="Cost per Unit" required :error="form.errors.cost" />
                        </div>
                        <div>
                            <Input id="reorder" type="number" step="0.0001" v-model="form.reorder_level" label="Low Stock Threshold" placeholder="e.g. 5" />
                        </div>
                    </div>
                    <div class="mb-4">
                        <Input id="expiry" type="date" v-model="form.expiration_date" label="Expiry Date (Optional)" />
                    </div>
                    <div class="flex justify-end mt-6">
                        <Button type="submit" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save
                        </Button>
                        <Button type="button" variant="secondary" @click="closeCreateModal" class="ml-2">
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Stock Modal -->
        <Modal :show="showStockModal" @close="closeStockModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                   Add Stock: {{ getLocaleName(selectedItem?.name) }}
                </h3>
                <form @submit.prevent="submitAddStock">
                    <div class="mb-4">
                        <Input id="add_amount" type="number" step="0.0001" v-model="stockForm.add_stock" :label="`Amount to Add (${selectedItem?.unit})`" required :error="stockForm.errors.add_stock" />
                    </div>
                    <div class="mb-4">
                        <Input 
                            id="added_cost" 
                            type="number" 
                            step="0.01" 
                            v-model="stockForm.added_cost" 
                            label="Incoming / Batch Unit Cost" 
                            :error="stockForm.errors.added_cost" 
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            This will create a new batch with the specified cost. Using FIFO (First-In, First-Out) for usage.
                        </p>
                    </div>
                    <div class="mb-4">
                        <Input id="stock_expiry" type="date" v-model="stockForm.expiration_date" label="Batch Expiry Date (Optional)" />
                    </div>
                    <div class="flex justify-end mt-6">
                        <Button type="submit" class="ml-3" :class="{ 'opacity-25': stockForm.processing }" :disabled="stockForm.processing">
                            Add Stock
                        </Button>
                         <Button type="button" variant="secondary" @click="closeStockModal" class="ml-2">
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- History Modal -->
        <Modal :show="showHistoryModal" @close="closeHistoryModal" maxWidth="7xl">
            <div class="p-6">
                 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                   History: {{ getLocaleName(selectedItem?.name) }}
                </h3>
                <div class="overflow-x-auto max-h-[70vh]">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User/Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="log in historyLogs" :key="log.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ new Date(log.created_at).toLocaleString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 capitalize">{{ log.action.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" :class="log.quantity_change > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ log.quantity_change > 0 ? '+' : '' }}{{ log.quantity_change }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ log.new_stock_level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ log.user ? log.user.name : 'System' }}
                                    <span v-if="log.notes" class="block text-xs text-gray-400">{{ log.notes }}</span>
                                </td>
                            </tr>
                            <tr v-if="historyLogs.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No history found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end mt-6">
                    <Button type="button" variant="secondary" @click="closeHistoryModal">
                        Close
                    </Button>
                </div>
            </div>
        </Modal>

        <!-- Batches Modal -->
        <Modal :show="showBatchesModal" @close="closeBatchesModal" maxWidth="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Stock Batches: {{ getLocaleName(selectedItem?.name) }}
                    </h3>
                    <div class="text-sm px-3 py-1 bg-primary/10 text-primary rounded-full font-semibold">
                        Total Stock: {{ selectedItem?.current_stock }} {{ selectedItem?.unit }}
                    </div>
                </div>

                <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Batch #</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Initial Qty</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Remaining</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Cost/Unit</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Received At</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Expiry</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="batch in selectedIngredientBatches" :key="batch.id" :class="{'bg-red-50/30': batch.quantity_remaining <= 0}">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ batch.batch_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-600">
                                        {{ batch.quantity_initial }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span 
                                            class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="batch.quantity_remaining > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                        >
                                            {{ batch.quantity_remaining }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                                        {{ formatCurrency(batch.cost_per_unit) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        {{ new Date(batch.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        {{ batch.expiration_date ? new Date(batch.expiration_date).toLocaleDateString() : '-' }}
                                    </td>
                                </tr>
                                <tr v-if="selectedIngredientBatches.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                        No batches found for this ingredient.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button variant="secondary" @click="closeBatchesModal" class="px-6">Close</Button>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Table from '@/Components/Table.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { locale } = useI18n();
const route = (window as any).route;

const props = defineProps<{
    ingredients: any[];
}>();

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'current_stock', label: 'Current Stock', sortable: true },
    { key: 'unit', label: 'Unit', sortable: true },
    { key: 'cost', label: 'Cost/Unit', sortable: true, format: 'currency' as const, currency: 'AED' },
    { key: 'total_value', label: 'Total Value', sortable: true, format: 'currency' as const, currency: 'AED' },
];

const search = ref('');

const filteredIngredients = computed(() => {
    if (!search.value) return props.ingredients;
    const q = search.value.toLowerCase();
    return props.ingredients.filter((item: any) => {
        const name = getLocaleName(item.name).toLowerCase();
        return name.includes(q);
    });
});

const showCreateModal = ref(false);
const showStockModal = ref(false);
const showHistoryModal = ref(false);
const showBatchesModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref<any>(null);
const historyLogs = ref<any[]>([]);
const selectedIngredientBatches = ref<any[]>([]);

const form = useForm({
    id: null,
    name: '',
    unit: '',
    current_stock: 0,
    cost: 0,
    reorder_level: 0,
    expiration_date: '',
});

const stockForm = useForm({
    add_stock: 0,
    added_cost: 0,
    expiration_date: '',
});

const getLocaleName = (name: any) => {
    if (!name) return '';
    if (typeof name === 'string') return name;
    return name[locale.value] || name['en'] || name['ar'] || 'Unknown';
};

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.current_stock = 0;
    showCreateModal.value = true;
};

const openEditModal = (item: any) => {
    isEditing.value = true;
    form.id = item.id;
    form.name = getLocaleName(item.name);
    form.unit = item.unit;
    form.current_stock = item.current_stock;
    form.cost = item.cost;
    form.reorder_level = item.reorder_level;
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    form.reset();
};

const submitCreate = () => {
    const options = {
        onSuccess: () => closeCreateModal(),
        onError: (errors) => {
            if (errors.name) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: errors.name,
                        type: 'error',
                        title: 'Validation Error'
                    }
                }));
            }
        }
    };

    if (isEditing.value) {
        form.put(route('inventory.update', form.id), options);
    } else {
        form.post(route('inventory.store'), options);
    }
};

const openAddStockModal = (item: any) => {
    selectedItem.value = item;
    stockForm.reset();
    stockForm.added_cost = item.cost; // Prefill with current cost
    showStockModal.value = true;
};

const closeStockModal = () => {
    showStockModal.value = false;
    stockForm.reset();
    selectedItem.value = null;
};

const submitAddStock = () => {
    stockForm.put(route('inventory.update', selectedItem.value.id), {
        onSuccess: () => closeStockModal(),
    });
};

const openHistoryModal = async (item: any) => {
    selectedItem.value = item;
    showHistoryModal.value = true;
    historyLogs.value = [];
    try {
        const response = await axios.get(route('inventory.history', item.id));
        historyLogs.value = response.data;
    } catch (error) {
        console.error("Failed to load history", error);
    }
};

const closeHistoryModal = () => {
    showHistoryModal.value = false;
    selectedItem.value = null;
    historyLogs.value = [];
};

const deleteItem = (item: any) => {
    // Check for active menu associations first
    const menuItems = item.menu_items || item.menuItems || [];
    
    if (menuItems.length > 0) {
        const names = menuItems.map((m: any) => getLocaleName(m.name)).join(', ');
        
        window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                message: `Cannot delete this ingredient. It is currently used in menu items: ${names}. Please remove it from these items first.`,
                title: 'Constraint Error',
                type: 'error'
            }
        }));
        
        return;
    }

    if (confirm('Are you sure you want to delete this specific ingredient?')) {
        router.delete(route('inventory.destroy', item.id), {
            preserveState: true,
            preserveScroll: true,
            onError: (errors) => {
                console.log('Delete error:', errors);
            }
        });
    }
};

const openBatchesModal = (item: any) => {
    selectedItem.value = item;
    selectedIngredientBatches.value = item.batches || [];
    showBatchesModal.value = true;
};

const closeBatchesModal = () => {
    showBatchesModal.value = false;
    selectedItem.value = null;
    selectedIngredientBatches.value = [];
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: 'AED',
    }).format(amount);
};
</script>
