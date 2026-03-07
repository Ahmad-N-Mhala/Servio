<template>
    <MainLayout>
        <div class="w-full px-2 sm:px-4 lg:px-6 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('orders.title') }}</h1>
                <div class="flex flex-wrap gap-4">
                     <a v-if="hasPermission('export_reports')" :href="exportUrl" target="_blank" class="inline-flex">
                        <Button class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>{{ $t('common.export') }}</Button>
                    </a>

                    <Link v-if="hasPermission('create_order')" :href="route('orders.create')">
                        <Button>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ $t('orders.new_order') }}
                        </Button>
                    </Link>
                </div>
            </div>
            
            <DateRangePicker
                :initial-start-date="params.start_date"
                :initial-end-date="params.end_date"
                @update="onDateRangeUpdate"
                class="mb-6"
            />
            
            <Table
                :columns="columns"
                :data="ordersList"
                :pagination="paginationMeta"
                v-model:search="params.search"
                :title="$t('orders.title')"
                :empty-message="$t('orders.no_orders')"
                @sort="handleSort"
                allow-overflow
            >
                <!-- Order Number & Delivery Provider -->
                <template #cell-order_number="{ row }">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ row.order_number || $t('common.na') || 'N/A' }}</span>
                        <span v-if="row.delivery_provider" 
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium w-fit mt-1"
                            :class="{
                                'bg-yellow-100 text-yellow-800': row.delivery_provider === 'noon',
                                'bg-orange-100 text-orange-800': row.delivery_provider === 'talabat',
                                'bg-cyan-100 text-cyan-800': row.delivery_provider === 'deliveroo',
                                'bg-green-100 text-green-800': row.delivery_provider === 'careem',
                                'bg-gray-100 text-gray-800': !['noon', 'talabat', 'deliveroo', 'careem'].includes(row.delivery_provider)
                            }"
                        >
                            {{ row.delivery_provider }}
                        </span>
                    </div>
                </template>

                <!-- Table Column -->
                <template #cell-table="{ row }">
                    <span class="text-sm text-gray-900 dark:text-white">{{ row.table?.name || '-' }}</span>
                </template>

                <!-- Customer Column -->
                <template #cell-customer_name="{ row }">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ row.customer_name || $t('orders.guest') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ row.customer_phone || '-' }}</div>
                </template>

                <!-- Waiter -->
                 <template #cell-waiter="{ row }">
                    <span class="text-sm text-gray-900 dark:text-white">{{ row.waiter?.name || '-' }}</span>
                </template>

                 <!-- Payment -->
                <template #cell-payment_method="{ row }">
                    <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                        {{ row.payment_method?.replace('_', ' ') || '-' }}
                    </div>
                    <span v-if="row.payment_status" 
                          class="text-xs font-medium uppercase"
                          :class="row.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'"
                    >
                        {{ row.payment_status === 'paid' ? $t('pos.paid') : $t('pos.unpaid') }}
                    </span>
                </template>

                 <!-- Status -->
                <template #cell-status="{ row }">
                     <div class="flex items-center gap-1">
                        <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getStatusClass(row.status)]">
                            {{ getStatusLabel(row.status) }}
                            <span v-if="['cancelled', 'deleted'].includes(row.status)" class="block text-[10px] font-normal opacity-80">
                                {{ new Date(row.updated_at).toLocaleDateString() }}
                            </span>
                        </span>
                        <div v-if="row.status === 'cancelled' && row.notes" class="group relative">
                            <svg class="w-4 h-4 text-red-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 text-center">
                                {{ row.notes }}
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Total -->
                <template #cell-total="{ row }">
                     <span class="text-sm font-bold text-primary">{{ currencyCode }} {{ formatMoney(row.total) }}</span>
                </template>

                 <!-- Duration -->
                <template #cell-duration="{ row }">
                    {{ calculateDuration(row.created_at, row.completed_at) }}
                </template>

                <!-- Actions -->
                <template #actions="{ row, index }">
                    <div class="relative inline-block text-start">
                        <button 
                            @click.stop="toggleDropdown(row.id)"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 font-medium shadow-sm text-sm whitespace-nowrap"
                        >
                            {{ $t('orders.actions') }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div 
                            v-if="openDropdown === row.id"
                            @click.stop
                            :class="[
                                'fixed lg:absolute end-0 w-48 rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.15)] bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-700 ring-opacity-10 z-[100] transform transition-all',
                                (index !== undefined && ordersList && index >= ordersList.length - 2) ? 'bottom-full mb-2 ltr:origin-bottom-right rtl:origin-bottom-left lg:translate-y-0 lg:-translate-x-0 -translate-x-[75%]' : 'mt-2 ltr:origin-top-right rtl:origin-top-left lg:translate-y-0 lg:-translate-x-0 -translate-x-[75%]'
                            ]"
                        >
                            <div class="py-1">
                                <!-- Approve (Delivery/Online) Action -->
                                <button 
                                    v-if="row.status === 'pending_approval' && hasPermission('edit_order')"
                                    @click="handleAction(row.id, 'pending')"
                                    class="w-full text-start px-4 py-2.5 text-sm text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $t('orders.approve_order') }}
                                </button>

                                <!-- Process Action -->
                                <button 
                                    v-if="row.status === 'pending' && hasPermission('edit_order')"
                                    @click="handleAction(row.id, 'processing')"
                                    class="w-full text-start px-4 py-2.5 text-sm text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ $t('orders.mark_as_processing') }}
                                </button>
                                
                                <!-- Complete Action -->
                                <button 
                                    v-if="row.status === 'processing' && hasPermission('edit_order')"
                                    @click="handleAction(row.id, 'completed')"
                                    class="w-full text-start px-4 py-2.5 text-sm text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $t('orders.mark_as_completed') }}
                                </button>

                                <!-- View Receipt Action (Thermal) -->
                                <button 
                                    v-if="row.status !== 'cancelled' && row.status !== 'deleted' && hasPermission('print_bill')"
                                    @click="fetchAndPrint(row.id); closeDropdown()"
                                    class="w-full text-start px-4 py-2.5 text-sm text-indigo-700 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    {{ $t('orders.view_receipt') }}
                                </button>
                                

                                <!-- Cancel Action -->
                                <button 
                                    v-if="(row.status === 'pending' || row.status === 'processing') && hasPermission('cancel_order')"
                                    @click="handleAction(row.id, 'cancelled', $t('orders.confirm_cancel'))"
                                    class="w-full text-start px-4 py-2.5 text-sm text-orange-700 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ $t('orders.cancel_order') }}
                                </button>
                                
                                <!-- Divider before delete -->
                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                                
                                <!-- Delete Action -->
                                <button 
                                    v-if="hasPermission('delete_order')"
                                    @click="handleAction(row.id, 'deleted', $t('orders.confirm_delete'))"
                                    class="w-full text-start px-4 py-2.5 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ $t('orders.delete_order') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </Table>
        </div>
        <Teleport to="body">
            <div id="orders-receipt-preview" class="print-overlay">
                <ReceiptPreview 
                    v-if="receiptPreviewOrder"
                    :template="(page.props.current_restaurant as any)?.receipt_template || {}" 
                    :order="receiptPreviewOrder" 
                    :logo="(page.props.current_restaurant as any)?.logo" 
                    :restaurant-name="(page.props.current_restaurant as any)?.name"
                    :google-map-location="(page.props.current_restaurant as any)?.google_map_location"
                />
            </div>
        </Teleport>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import Table from '@/Components/Table.vue';
import { usePermissions } from '@/Composables/usePermissions';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import { printReceiptPreview } from '@/Utils/printReceipt';

const { hasPermission } = usePermissions();
const { t } = useI18n();
const page = usePage();

const receiptPreviewOrder = ref<any>(null);

const props = withDefaults(defineProps<{
    orders?: any;
    currency?: string;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
        start_date?: string;
        end_date?: string;
    };
}>(), {
    orders: () => ({ data: [] }),
    currency: 'AED',
    filters: () => ({})
});

const params = ref({
    search: props.filters?.search || '',
    sort_field: props.filters?.sort_field || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || ''
});

const columns = computed(() => [
    { key: 'order_number', label: t('orders.order_number'), sortable: true },
    { key: 'delivery_order_id', label: 'External Order ID' },
    { key: 'customer_name', label: t('orders.customer'), sortable: true },
    { key: 'table', label: t('common.table') || 'Table' },
    { key: 'waiter', label: t('orders.waiter') || 'Waiter' },
    { key: 'payment_method', label: t('orders.payment') },
    { key: 'status', label: t('orders.status'), sortable: true },
    { key: 'total', label: t('orders.total'), sortable: true, align: 'right' as const },
    { key: 'created_at', label: t('orders.date'), sortable: true, format: 'datetime' as const },
    { key: 'duration', label: t('orders.duration') },
]);

const openDropdown = ref<number | null>(null);

const toggleDropdown = (orderId: number) => {
    openDropdown.value = openDropdown.value === orderId ? null : orderId;
};

const closeDropdown = () => {
    openDropdown.value = null;
};

let refreshInterval: any = null;

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    refreshInterval = setInterval(() => {
        router.reload({ only: ['orders'] });
    }, 3000); // 3s auto refresh
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    if (refreshInterval) clearInterval(refreshInterval);
});

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    params.value.start_date = range.startDate;
    params.value.end_date = range.endDate;
};

watch(
    () => params.value,
    debounce((value: any) => {
        router.get(route('orders.index'), { ...value }, {
            preserveState: true,
            replace: true
        });
    }, 300),
    { deep: true }
);

const handleSort = (key: string, direction: 'asc' | 'desc') => {
    params.value.sort_field = key;
    params.value.sort_direction = direction;
    
    router.get(route('orders.index'), params.value, {
        preserveState: true,
        replace: true
    });
};

const exportUrl = computed(() => {
    try {
        const baseUrl = route('orders.export');
        const url = new URL(baseUrl, window.location.origin);
        
        if (params.value.search) url.searchParams.append('search', params.value.search);
        if (params.value.start_date) url.searchParams.append('start_date', params.value.start_date);
        if (params.value.end_date) url.searchParams.append('end_date', params.value.end_date);
        
        return url.toString();
    } catch (e) {
        return '#';
    }
});

const route = (window as any).route;

const ordersList = computed(() => {
    try {
        return props.orders?.data || [];
    } catch {
        return [];
    }
});

// Helper functions for formatting (currency, date, duration, labels)
const currencyCode = computed(() => (page.props.current_restaurant as any)?.currency || props.currency || 'AED');

const paginationMeta = computed(() => {
    if (props.orders?.meta) return props.orders.meta;
    return {
        current_page: props.orders?.current_page || 1,
        last_page: props.orders?.last_page || 1,
        per_page: props.orders?.per_page || 10,
        total: props.orders?.total || 0,
        from: props.orders?.from || 0,
        to: props.orders?.to || 0
    };
});

const formatMoney = (amount: number | null | undefined): string => {
    if (amount == null || isNaN(amount)) return '0.00';
    return Number(amount).toFixed(2);
};

const calculateDuration = (created: string, completed?: string): string => {
    if (!completed) return t('orders.in_progress');
    const start = new Date(created).getTime();
    const end = new Date(completed).getTime();
    const diff = end - start;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const hLabel = t('common.hours_short') || 'h';
    const mLabel = t('common.minutes_short') || 'm';
    if (hours > 0) return `${hours}${hLabel} ${minutes}${mLabel}`;
    return `${minutes}${mLabel}`;
};

const getStatusLabel = (status: string | null | undefined): string => {
    const statusMap: Record<string, string> = {
        pending_approval: t('orders.pending_approval') || 'Pending Approval',
        pending: t('orders.pending'),
        processing: t('orders.processing'),
        completed: t('orders.completed'),
        cancelled: t('orders.cancelled'),
        deleted: t('orders.deleted')
    };
    return statusMap[status || 'pending'] || status || t('orders.pending');
};

const getStatusClass = (status: string | null | undefined): string => {
    const classes: Record<string, string> = {
        pending_approval: 'bg-purple-100 text-purple-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        deleted: 'bg-gray-100 text-gray-800'
    };
    return classes[status || 'pending'] || 'bg-gray-100 text-gray-800';
};

const updateStatus = (orderId: number, status: string) => {
    router.put(route('orders.status.update', orderId), { status });
};

// Printing Logic
const fetchAndPrint = async (orderId: number) => {
    try {
        const response = await axios.get(route('orders.show', orderId));
        await printReceipt(response.data);
    } catch (error) {
        console.error('Error fetching order for print:', error);
        alert(t('common.error') || 'Failed to fetch order details');
    }
};

// Enhanced Receipt Function using Component
const printReceipt = async (order: any) => {
    try {
        receiptPreviewOrder.value = order;
        await nextTick();
        
        const restaurant = (page.props.current_restaurant as any) || {};
        const template = restaurant.receipt_template || {};
        const paperWidth = template.paper_width || '80';

        if (template.show_qr_code && restaurant.google_map_location) {
             await new Promise(resolve => setTimeout(resolve, 800)); // Wait for QR generation
        } else {
             await new Promise(resolve => setTimeout(resolve, 200)); // Wait for render
        }

        // Use the snapshot printing method
        printReceiptPreview('orders-receipt-preview', paperWidth);

    } catch (error) {
        console.error('Print Error:', error);
        alert('Failed to print receipt.');
    }
};

const handleAction = (orderId: number, status: string, confirmMessage?: string) => {
    openDropdown.value = null;
    if (confirmMessage) {
        if (confirm(confirmMessage)) updateStatus(orderId, status);
    } else {
        updateStatus(orderId, status);
    }
};
</script>

<style>
@media screen {
    .print-overlay {
        display: none;
    }
}

@media print {
    /* Hide everything in body except the print overlay */
    body > *:not(.print-overlay) {
        display: none !important;
    }
    
    /* Ensure print overlay is visible */
    .print-overlay {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        background: white;
        z-index: 9999;
    }

    /* Reset page margins */
    @page {
        margin: 0;
        size: auto;
    }
}
</style>

