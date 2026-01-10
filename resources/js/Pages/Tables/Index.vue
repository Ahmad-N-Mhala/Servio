<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('tables.title') }}</h1>
                <div class="flex gap-3">
                     <Button v-if="hasPermission('create_table')" @click="openZoneModal()" variant="secondary" class="bg-gray-100 hover:bg-gray-200 text-gray-800 border-none shadow-sm">
                        <span class="mr-2">🏢</span> Add Zone
                    </Button>
                    <Button v-if="hasPermission('create_table')" @click="openModal()">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $t('tables.add_table') }}
                    </Button>
                </div>
            </div>

            <!-- Zones List -->
            <div v-for="zone in zones" :key="zone.id" class="mb-10 last:mb-0">
                 <div class="flex items-center justify-between mb-4 border-b border-dashed border-gray-300 dark:border-gray-700 pb-2">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <span>🏷️</span> {{ zone.name }}
                        <span class="text-xs font-normal text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ zone.tables.length }} tables</span>
                    </h2>
                    <button v-if="hasPermission('delete_table')" @click="deleteZone(zone)" class="text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Zone
                    </button>
                </div>
                
                <div v-if="zone.tables.length === 0" class="text-gray-500 italic text-sm mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-center border border-gray-100 dark:border-gray-800">
                    No tables in this zone.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="table in zone.tables" :key="table.id" class="glass-card p-6 rounded-2xl relative group bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
                        <TableCardContent :table="table" :hasPermission="hasPermission" @viewQr="viewQrCode" @edit="openModal" @delete="deleteTable" />
                    </div>
                </div>
            </div>

            <!-- Unassigned Tables -->
            <div v-if="orphanTables.length > 0" class="mb-12 mt-8">
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-dashed border-gray-300 dark:border-gray-700">
                     <h2 class="text-xl font-bold text-gray-600 dark:text-gray-400">Unassigned Tables</h2>
                     <span class="text-xs font-normal text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ orphanTables.length }} tables</span>
                </div>
               
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                     <div v-for="table in orphanTables" :key="table.id" class="glass-card p-6 rounded-2xl relative group bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
                         <TableCardContent :table="table" :hasPermission="hasPermission" @viewQr="viewQrCode" @edit="openModal" @delete="deleteTable" />
                    </div>
                </div>
            </div>
            
            <div v-if="zones.length === 0 && orphanTables.length === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <p class="text-gray-500 font-medium">{{ $t('charts.no_data') }}</p>
                <p class="text-sm text-gray-400 mt-1">Start by adding a zone or a table.</p>
            </div>

            <!-- Edit/Create Table Modal -->
            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-6 text-gray-900 dark:text-white">{{ form.id ? $t('common.edit') + ' ' + $t('tables.table_name') : $t('tables.add_table') }}</h2>
                     <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zone</label>
                            <select v-model="form.zone_id" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option :value="null">None (Unassigned)</option>
                                <option v-for="zone in zones" :key="zone.id" :value="zone.id">{{ zone.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('common.name') }}</label>
                            <input v-model="form.name" type="text" placeholder="e.g. T-12" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('tables.capacity') }}</label>
                            <input v-model="form.capacity" type="number" min="1" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <!-- Deprecated Location Field, kept for backward compatibility if user wants manual override, but Zone is preferred -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('tables.location') }} <span class="text-gray-400 text-xs font-normal">(Optional description)</span></label>
                            <input v-model="form.location" type="text" placeholder="e.g. Near Window" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('common.status') }}</label>
                            <select v-model="form.status" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="available">{{ $t('tables.available') }}</option>
                                <option value="occupied">{{ $t('tables.occupied') }}</option>
                                <option value="reserved">{{ $t('tables.reserved') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button variant="secondary" @click="closeModal" class="bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $t('common.cancel') }}</Button>
                        <Button @click="submit" :disabled="form.processing">{{ $t('common.save') }}</Button>
                    </div>
                </div>
            </Modal>
            
             <!-- Create Zone Modal -->
            <Modal :show="showZoneModal" @close="closeZoneModal">
                 <div class="p-6">
                    <h2 class="text-lg font-bold mb-6 text-gray-900 dark:text-white">Add new Zone</h2>
                    <div class="mb-4">
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zone Name</label>
                         <input v-model="zoneForm.name" type="text" placeholder="e.g. Main Hall" class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                         <p v-if="zoneForm.errors.name" class="text-red-500 text-xs mt-1">{{ zoneForm.errors.name }}</p>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <Button variant="secondary" @click="closeZoneModal" class="bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $t('common.cancel') }}</Button>
                        <Button @click="submitZone" :disabled="zoneForm.processing">{{ $t('common.save') }}</Button>
                    </div>
                 </div>
            </Modal>

            <!-- QR Code Modal -->
            <Modal :show="showQrModal" @close="closeQrModal">
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-6 text-gray-900 dark:text-white">{{ $t('tables.qr_code') }} - {{ selectedTable?.name }}</h2>
                    <div class="text-center">
                        <div class="bg-white p-6 rounded-xl inline-block mb-6">
                            <img 
                                :src="qrCodeImage" 
                                v-if="qrCodeImage"
                                alt="QR Code" 
                                class="w-64 h-64 mx-auto"
                            />
                            <div v-else class="w-64 h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                                <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-6 text-left bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2"><span class="font-semibold">{{ $t('tables.table_name') }}:</span> {{ selectedTable?.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2"><span class="font-semibold">{{ $t('tables.capacity') }}:</span> {{ selectedTable?.capacity }} {{ $t('tables.capacity') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 break-all"><span class="font-semibold">URL:</span> {{ selectedTable?.qr_code_url }}</p>
                        </div>
                        <div class="flex gap-3 justify-center">
                            <Button @click="downloadQrCode" class="bg-green-600 hover:bg-green-700"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>{{ $t('common.download_png') }}</Button>
                            <Button v-if="hasPermission('edit_table')" @click="regenerateQrCode" variant="secondary" class="bg-orange-600 hover:bg-orange-700 text-white"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>{{ $t('common.regenerate') }}</Button>
                            <Button variant="secondary" @click="closeQrModal" class="bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $t('common.close') }}</Button>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, defineComponent, h } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import { usePermissions } from '@/Composables/usePermissions';

// Define a small internal component for the table card content to reuse it
const TableCardContent = defineComponent({
    props: ['table', 'hasPermission'],
    emits: ['viewQr', 'edit', 'delete'],
    setup(props, { emit }) {
        const { t } = useI18n();
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

        return () => h('div', [
             h('div', { class: 'absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity z-10' }, [
                 props.hasPermission('view_tables') && h('button', { onClick: () => emit('viewQr', props.table), class: 'text-purple-500 hover:text-purple-700 bg-gray-100 p-1.5 rounded-lg' }, [
                     h('svg', { class: 'w-4 h-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z' }))
                 ]),
                 props.hasPermission('edit_table') && h('button', { onClick: () => emit('edit', props.table), class: 'text-blue-500 hover:text-blue-700 bg-gray-100 p-1.5 rounded-lg' }, [
                     h('svg', { class: 'w-4 h-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' }))
                 ]),
                 props.hasPermission('delete_table') && h('button', { onClick: () => emit('delete', props.table), class: 'text-red-500 hover:text-red-700 bg-gray-100 p-1.5 rounded-lg' }, [
                     h('svg', { class: 'w-4 h-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' }))
                 ])
             ]),
             h('div', { class: 'flex flex-col items-center justify-center mb-4' }, [
                 h('div', { class: ['w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold mb-3 shadow-sm border-4', getStatusColor(props.table.status)] }, props.table.name),
                 h('span', { class: 'text-sm font-medium text-gray-500 dark:text-gray-400' }, props.table.location || t('tables.location')),
                 h('span', { class: 'text-xs text-gray-400 mt-1' }, `${props.table.capacity} ${t('tables.capacity')}`)
             ]),
             h('div', { class: 'text-center' }, [
                  h('span', { class: ['px-3 py-1 rounded-full text-xs font-semibold capitalize', getStatusBadge(props.table.status)] }, t('tables.' + props.table.status))
             ])
        ]);
    }
});

const { t } = useI18n();
const { hasPermission } = usePermissions();

const props = defineProps<{
    zones: any[];
    orphanTables: any[];
}>();

const showModal = ref(false);
const showZoneModal = ref(false);
const showQrModal = ref(false);
const selectedTable = ref<any>(null);
const qrCodeImage = ref('');

const form = useForm({
    id: null,
    name: '',
    capacity: 4,
    status: 'available',
    location: '',
    zone_id: null,
});

const zoneForm = useForm({
    name: '',
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
        form.zone_id = table.zone_id || null;
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
    if (confirm(t('common.confirm'))) {
        router.delete(route('tables.destroy', table.id));
    }
};

const openZoneModal = () => {
    zoneForm.reset();
    zoneForm.clearErrors();
    showZoneModal.value = true;
};

const closeZoneModal = () => {
    showZoneModal.value = false;
    zoneForm.reset();
};

const submitZone = () => {
    zoneForm.post(route('tables.zones.store'), {
        onSuccess: () => closeZoneModal(),
    });
};

const deleteZone = (zone: any) => {
     if (confirm('Are you sure? Tables in this zone will become unassigned.')) {
        router.delete(route('tables.zones.destroy', zone.id));
    }
};

const viewQrCode = async (table: any) => {
    selectedTable.value = table;
    showQrModal.value = true;
    qrCodeImage.value = '';

    try {
        // Dynamic import
        const QRCodeModule = await import('qrcode');
        // Handle both default export and named exports
        const QRCode = QRCodeModule.default || QRCodeModule;
        
        // Create a temporary canvas element
        const canvas = document.createElement('canvas');
        
        // Check if input is valid
        console.log('Generating QR for URL:', table.qr_code_url);
        
        if (!table.qr_code_url) {
            console.error('QR Code URL is missing for table:', table);
            return;
        }

        // Render QR code to the canvas
        await QRCode.toCanvas(canvas, table.qr_code_url, {
            width: 300,
            margin: 2,
            errorCorrectionLevel: 'H',
        });
        
        // Convert canvas to data URL (PNG)
        qrCodeImage.value = canvas.toDataURL('image/png');
        
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
};

const closeQrModal = () => {
    showQrModal.value = false;
    selectedTable.value = null;
    qrCodeImage.value = '';
};

const downloadQrCode = () => {
    if (selectedTable.value) {
        window.location.href = route('tables.qr-code', selectedTable.value.id);
    }
};

const regenerateQrCode = () => {
    if (selectedTable.value && confirm(t('common.confirm'))) {
        router.post(route('tables.regenerate-qr', selectedTable.value.id), {}, {
            onSuccess: () => {
                closeQrModal();
                router.reload();
            },
        });
    }
};

const route = (window as any).route;
</script>
