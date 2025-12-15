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
                                            <button @click="openAddStockModal(item)" class="text-green-600 hover:text-green-900 mr-3">Add Stock</button>
                                            <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-900">Edit</button>
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

    </MainLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const route = (window as any).route;

const props = defineProps<{
    ingredients: any[];
}>();

const showCreateModal = ref(false);
const showStockModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref<any>(null);

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
</script>
