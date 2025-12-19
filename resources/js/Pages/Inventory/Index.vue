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
                    <span class="font-medium text-gray-900">{{ getLocaleName(row.name) }}</span>
                </template>

                 <!-- Stock Column -->
                <template #cell-current_stock="{ row }">
                    <span 
                        class="font-bold" 
                        :class="{'text-red-500': row.current_stock <= (row.reorder_level || 0)}"
                    >
                        {{ row.current_stock }}
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
                            <Input id="reorder" type="number" step="0.0001" v-model="form.reorder_level" label="Reorder Level" />
                        </div>
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
const isEditing = ref(false);
const selectedItem = ref<any>(null);
const historyLogs = ref<any[]>([]);

const form = useForm({
    id: null,
    name: '',
    unit: '',
    current_stock: 0,
    cost: 0,
    reorder_level: 0,
});

const stockForm = useForm({
    add_stock: 0,
    added_cost: 0,
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
    if (isEditing.value) {
        form.put(route('inventory.update', form.id), {
            onSuccess: () => closeCreateModal(),
        });
    } else {
        form.post(route('inventory.store'), {
            onSuccess: () => closeCreateModal(),
        });
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
    if (confirm('Are you sure you want to delete this specific ingredient?')) {
        router.delete(route('inventory.destroy', item.id));
    }
};
</script>
