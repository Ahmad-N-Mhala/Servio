<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Registration Interests</h1>
            </div>

            <Table 
                :columns="columns"
                :data="interests.data"
                :pagination="interests"
                v-model:search="search"
                title="Requests"
            >
                <template #header-actions>
                    <div class="flex items-center gap-3">
                        <div class="w-40">
                            <Select
                                v-model="statusFilter"
                                :options="[
                                    { label: 'All Status', value: '' },
                                    { label: 'Pending', value: 'pending' },
                                    { label: 'Not Started', value: 'not_started' },
                                    { label: 'In Progress', value: 'in_progress' },
                                    { label: 'Subscribed', value: 'subscribed' },
                                    { label: 'Not Subscribed', value: 'not_subscribed' }
                                ]"
                                placeholder="Status"
                                class="text-sm"
                            />
                        </div>

                        <a 
                            :href="route('admin.plan-interests.export', { search: search, status: statusFilter })"
                            class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 flex items-center gap-2"
                            target="_blank"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export
                        </a>
                    </div>
                </template>

                <!-- Contact Info -->
                <template #cell-contact="{ row }">
                    <div>
                        <div class="font-bold text-gray-900">{{ row.name }}</div>
                        <div class="text-sm text-gray-500">{{ row.email }}</div>
                        <div class="text-sm text-gray-500">{{ row.phone }}</div>
                    </div>
                </template>

                <!-- Business Info -->
                <template #cell-business="{ row }">
                    <div>
                        <div class="font-medium text-gray-900">{{ row.restaurant_name }}</div>
                        <div class="text-xs text-indigo-600 bg-indigo-50 inline-block px-1.5 py-0.5 rounded mt-0.5">
                            {{ row.plan_name }}
                        </div>
                    </div>
                </template>

                <!-- Message Column -->
                <template #cell-message="{ row }">
                     <span v-if="row.message" class="text-xs text-gray-500 italic truncate block max-w-[150px]" :title="row.message">
                        "{{ row.message }}"
                     </span>
                     <span v-else class="text-xs text-gray-300">-</span>
                </template>

                 <!-- Admin Notes Column -->
                 <template #cell-notes="{ row }">
                     <div @click="openNotesModal(row)" class="cursor-pointer hover:bg-gray-50 p-1.5 rounded -m-1.5 min-h-[1.5rem] group relative border border-transparent hover:border-gray-200 transition-all">
                        <span v-if="row.admin_notes" class="text-xs text-gray-800 block truncate max-w-[200px]" :title="row.admin_notes">
                            {{ row.admin_notes }}
                        </span>
                        <span v-else class="text-xs text-gray-400 italic flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Add note
                        </span>
                        
                        <!-- Edit Icon on Hover -->
                        <svg v-if="row.admin_notes" class="w-3 h-3 text-indigo-500 absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-white/50 backdrop-blur rounded" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                     </div>
                </template>

                <!-- Status Column -->
                <template #cell-status="{ row }">
                    <div class="relative w-36">
                        <select 
                            :value="row.status || 'pending'"
                            @change="updateStatus(row, ($event.target as HTMLSelectElement).value)"
                            class="appearance-none pl-3 pr-8 py-1 rounded-full text-xs font-bold border-0 focus:ring-2 cursor-pointer transition-colors w-full"
                            :class="{
                                'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 focus:ring-yellow-500': (!row.status || row.status === 'pending'),
                                'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500': row.status === 'not_started',
                                'bg-blue-100 text-blue-700 hover:bg-blue-200 focus:ring-blue-500': row.status === 'in_progress',
                                'bg-green-100 text-green-700 hover:bg-green-200 focus:ring-green-500': row.status === 'subscribed',
                                'bg-red-100 text-red-700 hover:bg-red-200 focus:ring-red-500': row.status === 'not_subscribed',
                            }"
                        >
                            <option value="pending">Pending</option>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="subscribed">Subscribed</option>
                            <option value="not_subscribed">Not Subscribed</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                            <svg class="h-3 w-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </template>

                <!-- Date Info -->
                <template #cell-date="{ row }">
                    <div class="text-sm text-gray-500" :title="row.created_at">
                        {{ new Date(row.created_at).toLocaleDateString() }}
                        <div class="text-xs">{{ new Date(row.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                    </div>
                </template>
            </Table>
        </div>

        <!-- Notes Modal -->
        <Modal :show="showNotesModal" @close="closeNotesModal" title="Request Notes">
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ editingInterest?.name }}</h3>
                    <p class="text-sm text-gray-500">{{ editingInterest?.restaurant_name }}</p>
                </div>
                
                <div v-if="editingInterest?.message" class="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">User Message</label>
                    <p class="text-sm text-gray-800 italic">"{{ editingInterest.message }}"</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <Input
                            v-model="notesForm.admin_notes"
                            label="Admin Notes"
                            type="textarea"
                            :rows="4"
                            placeholder="Add internal notes updates here..."
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <Button variant="secondary" @click="closeNotesModal">Cancel</Button>
                    <Button @click="saveNotes" :loading="notesForm.processing">Save Notes</Button>
                </div>
            </div>
        </Modal>

    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/Table.vue';
import Button from '@/Components/Button.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import { debounce } from 'lodash';

const props = defineProps<{
    interests: { data: any[], links: any[] };
    filters: { search?: string, status?: string };
}>();

const columns = [
    { key: 'contact', label: 'Contact Details', sortable: false },
    { key: 'business', label: 'Business Info', sortable: false },
    { key: 'message', label: 'Message', sortable: false },
    { key: 'notes', label: 'Admin Notes', sortable: false },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'date', label: 'Received At', sortable: true },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const route = (window as any).route;

const handleSearch = debounce(() => {
    router.get(route('admin.plan-interests.index'), {
        search: search.value,
        status: statusFilter.value
    }, { preserveState: true, replace: true });
}, 300);

watch([search, statusFilter], handleSearch);

const updateStatus = (row: any, newStatus: string) => {
    router.put(route('admin.plan-interests.update', row.id), {
        status: newStatus
    }, { preserveScroll: true });
};

// Notes Modal
const showNotesModal = ref(false);
const editingInterest = ref<any>(null);
const notesForm = useForm({
    admin_notes: '',
    status: '' // Included to satisfy validation if needed, but mainly for notes
});

const openNotesModal = (row: any) => {
    editingInterest.value = row;
    notesForm.admin_notes = row.admin_notes || '';
    notesForm.status = row.status;
    showNotesModal.value = true;
};

const closeNotesModal = () => {
    showNotesModal.value = false;
    editingInterest.value = null;
    notesForm.reset();
};

const saveNotes = () => {
    if (!editingInterest.value) return;

    notesForm.put(route('admin.plan-interests.update', editingInterest.value.id), {
        preserveScroll: true,
        onSuccess: () => closeNotesModal()
    });
};
</script>
