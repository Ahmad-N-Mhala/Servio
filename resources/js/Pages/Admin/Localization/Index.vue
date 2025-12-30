<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Localization Management</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        
                        <Table
                            :columns="columns"
                            :data="translations.data"
                            :pagination="translations"
                            :search="search"
                            @update:search="val => { search = val; debouncedSearch() }"
                            title="Translations"
                            row-key="full_key"
                        >
                            <!-- Custom Cell: File / Key -->
                            <template #cell-file="{ row }">
                                <div>
                                    <span class="block font-bold text-gray-700">{{ row.file }}</span>
                                    <span class="text-xs text-gray-500">{{ row.key }}</span>
                                </div>
                            </template>

                            <!-- Custom Cell: English -->
                            <template #cell-en="{ row }">
                                <span class="block max-w-xs truncate" :title="row.en">{{ row.en }}</span>
                            </template>

                            <!-- Custom Cell: Arabic (Editable) -->
                            <template #cell-ar="{ row }">
                                <div v-if="editing === row.full_key">
                                    <input 
                                        v-model="editValue" 
                                        @keyup.enter="save(row)" 
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm p-1"
                                        dir="rtl"
                                        ref="editInput"
                                    >
                                </div>
                                <div v-else class="cursor-pointer hover:bg-gray-50 p-2 rounded group" @click="startEdit(row)">
                                    <span v-if="row.ar" dir="rtl" class="text-gray-900">{{ row.ar }}</span>
                                    <span v-else class="text-gray-400 italic text-xs">Click to translate...</span>
                                    <span class="hidden group-hover:inline ml-2 text-indigo-500 text-xs">✎</span>
                                </div>
                            </template>

                            <!-- Actions -->
                            <template #actions="{ row }">
                                <div v-if="editing === row.full_key" class="flex gap-2">
                                    <Button size="xs" variant="primary" @click="save(row)">Save</Button>
                                    <Button size="xs" variant="ghost" @click="cancelEdit">Cancel</Button>
                                </div>
                                <Button v-else size="xs" variant="ghost" @click="startEdit(row)">Edit</Button>
                            </template>
                        </Table>

                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Table from '@/Components/Table.vue';
import Button from '@/Components/Button.vue';

const route = (window as any).route;

const props = defineProps<{
    translations: any;
    filters: any;
}>();

const search = ref(props.filters.search || '');
const editing = ref<string | null>(null);
const editValue = ref('');
const editInput = ref<HTMLInputElement | null>(null);

const columns = [
    { key: 'file', label: 'File / Key', sortable: false },
    { key: 'en', label: 'English (Source)', sortable: false },
    { key: 'ar', label: 'Arabic (Target)', sortable: false, align: 'right' as const },
];

const debouncedSearch = debounce(() => {
    router.get(route('admin.localization.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300);

const startEdit = (item: any) => {
    editing.value = item.full_key;
    editValue.value = item.ar;
    nextTick(() => {
        // Simple focus attempt if ref is array or single
        // In loop refs might be arrays, but here we only render one input at a time
        const input = document.querySelector('input[dir="rtl"]') as HTMLInputElement;
        if(input) input.focus();
    });
};

const cancelEdit = () => {
    editing.value = null;
    editValue.value = '';
};

const save = (item: any) => {
    router.post(route('admin.localization.update'), {
        file: item.file,
        key: item.key,
        value: editValue.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        }
    });
};
</script>
