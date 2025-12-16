<template>
    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $t('inventory.title', 'Inventory Management') }}
                </h2>
                <Button @click="openCreateModal">
                    {{ $t('inventory.add_item', 'Add Raw Item') }}
                </Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Data Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cost/Unit</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in ingredients" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ getLocaleName(item.name) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold" :class="{'text-red-500': item.current_stock <= (item.reorder_level || 0)}">
                                            {{ item.current_stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(item.cost) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="relative inline-block text-left">
                                                <button @click="toggleDropdown(item.id)" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Actions
                                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>

                                                <div v-if="activeDropdown === item.id" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                                        <button @click="openAddStockModal(item); closeDropdown()" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Add Stock</button>
                                                        <button @click="openHistoryModal(item); closeDropdown()" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">History</button>
                                                        <button @click="openEditModal(item); closeDropdown()" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Edit</button>
                                                        <button @click="deleteItem(item); closeDropdown()" class="w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900" role="menuitem">Delete</button>
                                                    </div>
                                                </div>
                                                <!-- Click overlay to close -->
                                                <div v-if="activeDropdown === item.id" @click="closeDropdown()" class="fixed inset-0 z-40 bg-transparent cursor-default"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="ingredients.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No ingredients found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { locale } = useI18n();
const route = (window as any).route;

const props = defineProps<{
    ingredients: any[];
}>();

const showCreateModal = ref(false);
const showStockModal = ref(false);
const showHistoryModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref<any>(null);
const historyLogs = ref<any[]>([]);
const activeDropdown = ref<number | null>(null);

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

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AE', { style: 'currency', currency: 'AED' }).format(amount);
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

const toggleDropdown = (id: number) => {
    if (activeDropdown.value === id) {
        activeDropdown.value = null;
    } else {
        activeDropdown.value = id;
    }
};

const closeDropdown = () => {
    activeDropdown.value = null;
};
</script>
