<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Table Component -->
            <Table
                :columns="columns"
                :data="filteredIngredients"
                v-model:search="search"
                :title="$t('inventory.title', 'Inventory Management')"
                :currency="currency"
            >
                <template #header-actions>
                    <div class="flex gap-2">
                        <Button variant="secondary" @click="openExportModal">
                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ $t('common.export') }} {{ $t('inventory.history') }}
                        </Button>
                        <Button @click="openCreateModal">
                            {{ $t('inventory.add_item') }}
                        </Button>
                    </div>
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
                            {{ $t('inventory_page.low_stock') }}
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
                            v-if="hasPermission('add_stock')"
                            @click="openAddStockModal(row)" 
                            class="text-green-600 hover:text-green-800 p-1 rounded-md hover:bg-green-50 transition-colors"
                            :title="$t('inventory_page.add_stock')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </button>
                        <button 
                            @click="openHistoryModal(row)" 
                            class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-50 transition-colors"
                            :title="$t('inventory.history')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <button 
                            @click="openEditModal(row)" 
                            class="text-gray-600 hover:text-gray-800 p-1 rounded-md hover:bg-gray-50 transition-colors"
                            :title="$t('common.edit')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button 
                            v-if="hasPermission('delete_inventory')"
                            @click="deleteItem(row)" 
                            class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-50 transition-colors"
                            :title="$t('common.delete')"
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
                    {{ isEditing ? $t('inventory.edit_item') : $t('inventory.add_item') }}
                </h3>
                <form @submit.prevent="submitCreate">
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <Input id="name_en" type="text" v-model="form.name_en" :label="$t('common.name') + ' (EN)'" required :error="form.errors['name.en']" />
                        </div>
                        <div>
                            <Input id="name_ar" type="text" v-model="form.name_ar" :label="$t('common.name') + ' (AR)'" required :error="form.errors['name.ar']" />
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <Input id="stock" type="number" step="0.0001" v-model="form.current_stock" :label="$t('inventory_page.current_stock')" required :disabled="isEditing" :error="form.errors.current_stock" />
                            <p v-if="isEditing" class="text-xs text-gray-500 mt-1">Use 'Add Stock' to update inventory.</p>
                        </div>
                        <div>
                            <Select
                                id="unit"
                                v-model="form.unit"
                                :label="$t('inventory_page.unit')"
                                required
                                :error="form.errors.unit"
                                :options="unitOptions"
                                :placeholder="$t('inventory.select_unit')"
                            />
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                         <div>
                            <Input id="cost" type="number" step="0.01" v-model="form.cost" :label="$t('inventory.cost_unit')" required :error="form.errors.cost" />
                        </div>
                        <div>
                            <Input id="reorder" type="number" step="0.0001" v-model="form.reorder_level" :label="$t('inventory_page.low_stock_threshold')" placeholder="e.g. 5" />
                        </div>
                    </div>
                    <div class="mb-4">
                        <Select
                            id="notification_recipient"
                            v-model="form.reminder_user_id"
                            :label="$t('inventory.recipient_label', 'Alert Recipient (Expiry & Low Stock)')"
                            :options="recipientOptions"
                            :placeholder="$t('common.select_user')"
                        />
                        <p class="text-xs text-gray-500 mt-1">This user will receive emails for expiry warnings and low stock alerts.</p>
                    </div>

                    <div class="mb-4">
                        <Input id="expiry" type="date" v-model="form.expiration_date" :label="$t('inventory.expiry_date') + ' (' + $t('common.optional') + ')'" />
                    </div>

                    <div v-if="form.expiration_date" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">{{ $t('inventory.expiry_reminder') }}</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <Input 
                                    id="reminder_days" 
                                    type="number" 
                                    min="1" 
                                    v-model="form.reminder_days" 
                                    :label="$t('inventory.days_before_expiry')" 
                                    placeholder="e.g. 7" 
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">{{ $t('inventory.notes_optional') }}</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all hover:border-slate-300 dark:hover:border-slate-600 disabled:bg-slate-100 disabled:text-slate-500"
                        ></textarea>
                    </div>
                    <div class="mb-4">
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('inventory.bill_invoice') }}</label>
                         <input 
                            type="file" 
                            @change="(e) => form.bill = (e.target as HTMLInputElement).files?.[0] || null"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary/10 file:text-primary
                                hover:file:bg-primary/20
                            "
                            accept=".pdf,.jpg,.jpeg,.png"
                        />
                        <p v-if="form.errors.bill" class="text-xs text-red-600 mt-1">{{ form.errors.bill }}</p>
                    </div>
                    <div class="flex justify-end mt-6">
                         <Button type="submit" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ $t('common.save') }}
                        </Button>
                        <Button type="button" variant="secondary" @click="closeCreateModal" class="ml-2">
                            {{ $t('common.cancel') }}
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Stock Modal -->
        <Modal :show="showStockModal" @close="closeStockModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ $t('inventory_page.add_stock') }}: {{ getLocaleName(selectedItem?.name) }}
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
                            :label="$t('inventory.incoming_cost')" 
                            :error="stockForm.errors.added_cost" 
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $t('inventory.fifo_note') || 'This will create a new batch with the specified cost. Using FIFO (First-In, First-Out) for usage.' }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <Input id="stock_expiry" type="date" v-model="stockForm.expiration_date" :label="$t('inventory.batch_expiry')" />
                    </div>

                    <div v-if="stockForm.expiration_date" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">{{ $t('inventory.expiry_reminder') }}</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Input 
                                    id="stock_reminder_days" 
                                    type="number" 
                                    min="1" 
                                    v-model="stockForm.reminder_days" 
                                    :label="$t('inventory.days_before_expiry')" 
                                    placeholder="e.g. 7" 
                                />
                            </div>
                            <div>
                                <Select
                                    id="stock_reminder_user"
                                    v-model="stockForm.reminder_user_id"
                                    :label="$t('inventory.recipient')"
                                    :options="recipientOptions"
                                    :placeholder="$t('common.select_user')"
                                />
                                <p class="text-[10px] text-gray-500 mt-1">Updates the primary recipient for this ingredient.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">{{ $t('inventory.notes_optional') }}</label>
                        <textarea
                            v-model="stockForm.notes"
                            rows="2"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all hover:border-slate-300 dark:hover:border-slate-600 disabled:bg-slate-100 disabled:text-slate-500"
                        ></textarea>
                    </div>
                    <div class="mb-4">
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('inventory.bill_invoice') }}</label>
                         <input 
                            type="file" 
                            @change="(e) => stockForm.bill = (e.target as HTMLInputElement).files?.[0] || null"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary/10 file:text-primary
                                hover:file:bg-primary/20
                            "
                            accept=".pdf,.jpg,.jpeg,.png"
                        />
                        <p v-if="stockForm.errors.bill" class="text-xs text-red-600 mt-1">{{ stockForm.errors.bill }}</p>
                    </div>
                    <div class="flex justify-end mt-6">
                        <Button type="submit" class="ml-3" :class="{ 'opacity-25': stockForm.processing }" :disabled="stockForm.processing">
                            {{ $t('inventory_page.add_stock') }}
                        </Button>
                         <Button type="button" variant="secondary" @click="closeStockModal" class="ml-2">{{ $t('common.cancel') }}</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- History Modal -->
        <Modal :show="showHistoryModal" @close="closeHistoryModal" maxWidth="7xl">
            <div class="p-6">
                 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                   {{ $t('inventory.history') }}: {{ getLocaleName(selectedItem?.name) }}
                </h3>
                <Table
                    :columns="historyColumns"
                    :data="historyLogs"
                    :emptyMessage="$t('inventory.no_history')"
                >
                    <template #cell-created_at="{ row }">
                        <span class="text-sm text-gray-900 dark:text-gray-100 font-mono">
                            {{ formatDateTime(row.created_at) }}
                        </span>
                    </template>
                    <template #cell-action="{ row }">
                        <span class="text-sm text-gray-900 dark:text-gray-100 capitalize">
                            {{ row.action.replace('_', ' ') }}
                        </span>
                    </template>
                    <template #cell-quantity_change="{ row }">
                        <span class="text-sm font-bold font-mono" :class="row.quantity_change > 0 ? 'text-green-600' : 'text-red-600'">
                            {{ row.quantity_change > 0 ? '+' : '' }}{{ row.quantity_change }}
                        </span>
                    </template>
                    <template #cell-new_stock_level="{ row }">
                        <span class="text-sm text-gray-900 dark:text-gray-100 font-mono">
                            {{ row.new_stock_level }}
                        </span>
                    </template>
                    <template #cell-user_notes="{ row }">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ row.user ? row.user.name : 'System' }}
                            <span v-if="row.notes" class="block text-xs text-gray-400">{{ row.notes }}</span>
                        </div>
                    </template>
                    <template #cell-docs="{ row }">
                        <a 
                            v-if="row.bill_path" 
                            :href="`/storage/${row.bill_path}`" 
                            target="_blank"
                            class="text-primary hover:text-primary-hover underline text-xs flex items-center gap-1"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            {{ $t('inventory.download') }}
                        </a>
                    </template>
                </Table>
                <div class="flex justify-end mt-6">
                    <Button type="button" variant="secondary" @click="closeHistoryModal">
                        {{ $t('common.close') }}
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
                        {{ $t('inventory.stock_batches') }}: {{ getLocaleName(selectedItem?.name) }}
                    </h3>
                    <div class="text-sm px-3 py-1 bg-primary/10 text-primary rounded-full font-semibold">
                        {{ $t('inventory.total_stock') }}: {{ selectedItem?.current_stock }} {{ selectedItem?.unit }}
                    </div>
                </div>

                <Table
                    :columns="batchesColumns"
                    :data="selectedIngredientBatches"
                    :emptyMessage="$t('inventory.no_batches')"
                    :rowClass="(row) => row.quantity_remaining <= 0 ? 'bg-red-50/30' : ''"
                >
                    <template #cell-quantity_initial="{ row }">
                        <span class="font-mono text-gray-600">
                            {{ row.quantity_initial }}
                        </span>
                    </template>
                    <template #cell-quantity_remaining="{ row }">
                        <span 
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono"
                            :class="row.quantity_remaining > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        >
                            {{ row.quantity_remaining }}
                        </span>
                    </template>
                    <template #cell-created_at="{ row }">
                        <span class="text-sm text-gray-500 font-mono">
                            {{ formatDate(row.created_at) }}
                        </span>
                    </template>
                    <template #cell-expiration_date="{ row }">
                        <span class="text-sm text-gray-500 font-mono">
                            {{ row.expiration_date ? formatDate(row.expiration_date) : '-' }}
                        </span>
                    </template>
                </Table>

                <div class="mt-8 flex justify-end">
                    <Button variant="secondary" @click="closeBatchesModal" class="px-6">{{ $t('common.close') }}</Button>
                </div>
            </div>
        </Modal>

        <!-- Export Modal -->
        <Modal :show="showExportModal" @close="closeExportModal">
            <div class="p-6">
                 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $t('inventory.export_report') }}</h3>
                 <form @submit.prevent="submitExport">
                     <p class="text-sm text-gray-500 mb-4">{{ $t('inventory.select_duration') }}</p>
                     <div class="mb-4 grid grid-cols-2 gap-4">
                         <div>
                             <Input id="start_date" type="date" v-model="exportForm.start_date" :label="$t('reports.start_date')" required />
                         </div>
                         <div>
                             <Input id="end_date" type="date" v-model="exportForm.end_date" :label="$t('reports.end_date')" required />
                         </div>
                     </div>
                     <div class="flex justify-end mt-6">
                         <Button type="submit" class="ml-3">
                             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                             {{ $t('inventory.download_excel') }}
                         </Button>
                         <Button type="button" variant="secondary" @click="closeExportModal" class="ml-2">{{ $t('common.cancel') }}</Button>
                     </div>
                 </form>
            </div>
        </Modal>

    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import Table from '@/Components/Table.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { usePermissions } from '@/Composables/usePermissions';
import { formatDate, formatDateTime } from '@/Utils/dateHelper';

const { hasPermission } = usePermissions();
const { locale, t } = useI18n();
const route = (window as any).route;
const page = usePage();

const props = defineProps<{
    ingredients: any[];
    users: any[];
    defaultOwnerId: string | null;
}>();

const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = computed(() => [
    { key: 'name', label: t('common.name'), sortable: true },
    { key: 'current_stock', label: t('inventory_page.current_stock'), sortable: true },
    { key: 'unit', label: t('inventory_page.unit'), sortable: true },
    { key: 'cost', label: t('inventory.cost_unit'), sortable: true, format: 'currency' as const, currency: currency.value },
    { key: 'total_value', label: t('common.total'), sortable: true, format: 'currency' as const, currency: currency.value },
]);

const historyColumns = computed(() => [
    { key: 'created_at', label: t('common.date'), sortable: true },
    { key: 'action', label: t('inventory.action'), sortable: true },
    { key: 'quantity_change', label: t('inventory.change'), sortable: true },
    { key: 'new_stock_level', label: t('inventory.new_level'), sortable: true },
    { key: 'user_notes', label: t('inventory.user_notes'), sortable: true },
    { key: 'docs', label: t('inventory.docs') }
]);

const batchesColumns = computed(() => [
    { key: 'batch_number', label: t('inventory.batch_number'), sortable: true },
    { key: 'quantity_initial', label: t('inventory.initial_qty'), sortable: true, align: 'center' as const },
    { key: 'quantity_remaining', label: t('inventory.remaining'), sortable: true, align: 'center' as const },
    { key: 'cost_per_unit', label: t('inventory.cost_unit'), sortable: true, align: 'right' as const, format: 'currency' as const, currency: currency.value },
    { key: 'created_at', label: t('inventory.received_at'), sortable: true, align: 'center' as const },
    { key: 'expiration_date', label: t('inventory.expiry_date'), sortable: true, align: 'center' as const }
]);

const search = ref('');
const showCreateModal = ref(false);
const showStockModal = ref(false);
const showHistoryModal = ref(false);
const showBatchesModal = ref(false);
const showExportModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref<any>(null);
const historyLogs = ref<any[]>([]);
const selectedIngredientBatches = ref<any[]>([]);

const unitOptions = computed(() => [
    { label: `${t('inventory.mass')} - ${t('inventory.kg')}`, value: 'kg' },
    { label: `${t('inventory.mass')} - ${t('inventory.g')}`, value: 'g' },
    { label: `${t('inventory.mass')} - ${t('inventory.mg')}`, value: 'mg' },
    { label: `${t('inventory.volume')} - ${t('inventory.l')}`, value: 'l' },
    { label: `${t('inventory.volume')} - ${t('inventory.ml')}`, value: 'ml' },
    { label: `${t('inventory.count')} - ${t('inventory.pcs')}`, value: 'pcs' },
    { label: `${t('inventory.count')} - ${t('inventory.box')}`, value: 'box' },
    { label: `${t('inventory.count')} - ${t('inventory.pack')}`, value: 'pack' },
    { label: `${t('inventory.count')} - ${t('inventory.can')}`, value: 'can' },
    { label: `${t('inventory.count')} - ${t('inventory.bottle')}`, value: 'bottle' },
    { label: `${t('inventory.count')} - ${t('inventory.dozen')}`, value: 'dozen' },
]);

const recipientOptions = computed(() => [
    { label: t('inventory.no_alert_recipient', 'No notifications needed'), value: null },
    ...props.users.map((u: any) => ({ label: u.name, value: u.id }))
]);

const form = useForm<any>({
    id: null,
    name_en: '',
    name_ar: '',
    current_stock: 0,
    unit: '',
    cost: 0,
    reorder_level: null,
    expiration_date: null,
    bill: null,
    notes: '',
    reminder_days: 7,
    reminder_user_id: null,
});

const stockForm = useForm<any>({
    id: null,
    add_stock: 0,
    added_cost: 0,
    expiration_date: null,
    bill: null,
    notes: '',
    reminder_days: 7,
    reminder_user_id: null,
});

const exportForm = ref({
    start_date: new Date().toISOString().substr(0, 10),
    end_date: new Date().toISOString().substr(0, 10)
});

const getLocaleName = (name: any) => {
    if (typeof name === 'object' && name !== null) {
        return name[locale.value] || Object.values(name)[0] || '';
    }
    return name;
};

const getRecipientName = (userId: string | null) => {
    if (!userId) return t('inventory.no_alert_recipient', 'No notifications needed');
    const user = props.users?.find(u => u.id === userId);
    return user ? user.name : '-';
};

const filteredIngredients = computed(() => {
    if (!search.value) return props.ingredients;
    const query = search.value.toLowerCase();
    return props.ingredients.filter((item: any) => {
        const name = getLocaleName(item.name).toLowerCase();
        return name.includes(query);
    });
});

const formatCurrency = (amount: number) => {
    // Use a generic locale or derive from currency
    const localeMap: Record<string, string> = {
        'AED': 'en-AE',
        'USD': 'en-US',
        'EUR': 'en-EU',
        'GBP': 'en-GB',
        'SAR': 'ar-SA',
    };
    const locale = localeMap[currency.value] || 'en-US';
    
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency.value,
    }).format(amount);
};

// Modal Actions
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.bill = null;
    form.notes = '';
    form.name_en = '';
    form.name_ar = '';
    form.reminder_days = 7;
    form.reminder_user_id = props.defaultOwnerId;
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    form.reset();
};

const openExportModal = () => {
    showExportModal.value = true;
};

const closeExportModal = () => {
    showExportModal.value = false;
};

const submitExport = () => {
    const url = route('inventory.export', {
        start_date: exportForm.value.start_date,
        end_date: exportForm.value.end_date
    });
    window.location.href = url;
    closeExportModal();
};

const openEditModal = (item: any) => {
    isEditing.value = true;
    selectedItem.value = item;
    form.clearErrors();
    form.id = item.id;
    if (typeof item.name === 'object' && item.name !== null) {
        form.name_en = item.name.en || '';
        form.name_ar = item.name.ar || '';
    } else {
        form.name_en = item.name;
        form.name_ar = item.name;
    }
    form.current_stock = item.current_stock;
    form.unit = item.unit;
    form.cost = item.cost;
    form.reorder_level = item.reorder_level;
    form.bill = null;
    form.notes = '';
    form.reminder_user_id = item.notification_user_id || props.defaultOwnerId;
    showCreateModal.value = true;
};

const submitCreate = () => {
    const payload = {
        ...form,
        name: {
            en: form.name_en,
            ar: form.name_ar
        }
    };
    
    if (isEditing.value) {
        form.transform((data: any) => ({
            ...data,
            name: { en: form.name_en, ar: form.name_ar },
            _method: 'PUT',
        })).post(route('inventory.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeCreateModal(),
        });
    } else {
        form.transform((data: any) => ({
            ...data,
            name: { en: form.name_en, ar: form.name_ar }
        })).post(route('inventory.store'), {
            preserveScroll: true,
            onSuccess: () => closeCreateModal(),
        });
    }
};

const deleteItem = (item: any) => {
    if (confirm(t('common.confirm'))) {
        router.delete(route('inventory.destroy', item.id), {
            preserveScroll: true,
        });
    }
};

const openAddStockModal = (item: any) => {
    selectedItem.value = item;
    stockForm.reset();
    stockForm.clearErrors();
    stockForm.id = item.id;
    stockForm.added_cost = item.cost;
    stockForm.bill = null; 
    stockForm.notes = '';
    stockForm.reminder_days = 7;
    // Pre-fill with existing notification user or default owner
    stockForm.reminder_user_id = item.notification_user_id || props.defaultOwnerId;
    showStockModal.value = true;
};

const closeStockModal = () => {
    showStockModal.value = false;
    stockForm.reset();
};

const submitAddStock = () => {
    // IMPORTANT: File uploads with PUT require POST + _method: 'PUT' in Laravel/Inertia
    stockForm.transform((data: any) => ({
        ...data,
        _method: 'PUT',
    })).post(route('inventory.update', stockForm.id), {
        preserveScroll: true,
        onSuccess: () => closeStockModal(),
    });
};

const openHistoryModal = async (item: any) => {
    selectedItem.value = item;
    historyLogs.value = [];
    showHistoryModal.value = true;
    try {
        const response = await axios.get(route('inventory.history', item.id));
        historyLogs.value = response.data;
    } catch (e) {
        console.error("Failed to load history", e);
    }
};

const closeHistoryModal = () => {
    showHistoryModal.value = false;
};

const openBatchesModal = (item: any) => {
    selectedItem.value = item;
    // content is loaded via with('batches') in controller
    selectedIngredientBatches.value = item.batches || [];
    showBatchesModal.value = true;
};

const closeBatchesModal = () => {
    showBatchesModal.value = false;
};
</script>
