<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tables</h1>
                <Button @click="openModal()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Table
                </Button>
            </div>

            <div v-if="tables.length === 0" class="text-center py-12">
                <p class="text-gray-500">No tables found. Create one to get started.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="table in tables" :key="table.id" class="glass-card p-6 rounded-2xl relative group bg-white dark:bg-gray-800">
                    <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openModal(table)" class="text-blue-500 hover:text-blue-700 bg-gray-100 p-1.5 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button @click="deleteTable(table)" class="text-red-500 hover:text-red-700 bg-gray-100 p-1.5 rounded-lg">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-col items-center justify-center mb-4">
                         <div :class="['w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold mb-3 shadow-sm border-4', getStatusColor(table.status)]">
                            {{ table.name }}
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ table.location || 'Main Hall' }}</span>
                        <span class="text-xs text-gray-400 mt-1">{{ table.capacity }} Seats</span>
                    </div>
                    <div class="text-center">
                         <span :class="['px-3 py-1 rounded-full text-xs font-semibold capitalize', getStatusBadge(table.status)]">
                            {{ table.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-6 text-gray-900 dark:text-white">{{ form.id ? 'Edit Table' : 'New Table' }}</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. T-12" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacity</label>
                            <input v-model="form.capacity" type="number" min="1" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                            <input v-model="form.location" type="text" placeholder="e.g. Patio" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select v-model="form.status" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button variant="secondary" @click="closeModal" class="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button @click="submit" :disabled="form.processing">Save Table</Button>
                    </div>
                </div>
            </Modal>

        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps<{
    tables: any[];
}>();

const showModal = ref(false);
const form = useForm({
    id: null,
    name: '',
    capacity: 4,
    status: 'available',
    location: '',
});

const openModal = (table: any = null) => {
    form.reset();
    form.clearErrors();
    if (table) {
        form.id = table.id;
        form.name = table.name;
        form.capacity = table.capacity;
        form.status = table.status;
        form.location = table.location;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (form.id) {
        form.put(route('tables.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('tables.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTable = (table: any) => {
    if (confirm('Are you sure you want to delete this table?')) {
        router.delete(route('tables.destroy', table.id));
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'available': return 'bg-green-100 text-green-700 border-green-200';
        case 'occupied': return 'bg-red-100 text-red-700 border-red-200';
        case 'reserved': return 'bg-orange-100 text-orange-700 border-orange-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'available': return 'bg-green-100 text-green-800';
        case 'occupied': return 'bg-red-100 text-red-800';
        case 'reserved': return 'bg-orange-100 text-orange-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const route = (window as any).route;
</script>
