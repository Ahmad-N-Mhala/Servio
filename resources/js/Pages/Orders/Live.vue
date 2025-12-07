<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Orders</h1>
                <div class="flex gap-4">
                    <div class="relative">
                        <input 
                            v-model="params.search"
                            type="text" 
                            placeholder="Search orders..." 
                            class="w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
                        >
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <Link :href="route('orders.create')">
                        <Button>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Order
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
            
            <div class="glass-card rounded-2xl overflow-hidden">
                <!-- Empty State -->
                <div v-if="ordersCount === 0" class="text-center py-16 px-6">
                    <div class="p-4 bg-gray-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">No orders yet</h4>
                    <p class="text-gray-500 mb-6">Create your first order to get started</p>
                    <Link :href="route('orders.create')">
                        <Button>Create First Order</Button>
                    </Link>
                </div>
                
                <!-- Table -->
                <div v-else>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('order_number')"
                                >
                                    <div class="flex items-center gap-1">
                                        Order #
                                        <span v-if="params.sort_field === 'order_number'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('customer_name')"
                                >
                                    <div class="flex items-center gap-1">
                                        Customer
                                        <span v-if="params.sort_field === 'customer_name'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('status')"
                                >
                                    <div class="flex items-center gap-1">
                                        Status
                                        <span v-if="params.sort_field === 'status'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('total')"
                                >
                                    <div class="flex items-center gap-1">
                                        Total
                                        <span v-if="params.sort_field === 'total'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('created_at')"
                                >
                                    <div class="flex items-center gap-1">
                                        Date
                                        <span v-if="params.sort_field === 'created_at'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Duration
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="order in ordersList" 
                                :key="order.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{ order.order_number || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ order.customer_name || 'Guest' }}</div>
                                    <div class="text-sm text-gray-500">{{ order.customer_phone || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getStatusClass(order.status)]">
                                        {{ order.status || 'pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-primary">{{ currencyCode }} {{ formatMoney(order.total) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ calculateDuration(order.created_at, order.completed_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="relative inline-block text-left">
                                        <button 
                                            @click="toggleDropdown(order.id)"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 font-medium shadow-sm"
                                        >
                                            Actions
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div 
                                            v-if="openDropdown === order.id"
                                            @click.stop
                                            class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10 overflow-hidden"
                                        >
                                            <div class="py-1">
                                                <!-- Process Action -->
                                                <button 
                                                    v-if="order.status === 'pending'"
                                                    @click="handleAction(order.id, 'processing')"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50 flex items-center gap-3 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                    Mark as Processing
                                                </button>
                                                
                                                <!-- Complete Action -->
                                                <button 
                                                    v-if="order.status === 'processing'"
                                                    @click="handleAction(order.id, 'completed')"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-green-700 hover:bg-green-50 flex items-center gap-3 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Mark as Completed
                                                </button>
                                                
                                                <!-- Cancel Action -->
                                                <button 
                                                    v-if="order.status === 'pending' || order.status === 'processing'"
                                                    @click="handleAction(order.id, 'cancelled', 'Are you sure you want to cancel this order?')"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-orange-700 hover:bg-orange-50 flex items-center gap-3 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Cancel Order
                                                </button>
                                                
                                                <!-- Divider before delete -->
                                                <div class="border-t border-gray-100 my-1"></div>
                                                
                                                <!-- Delete Action -->
                                                <button 
                                                    @click="handleAction(order.id, 'deleted', 'Are you sure you want to delete this order? This action will exclude it from all calculations.')"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 flex items-center gap-3 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete Order
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="border-t border-gray-200 bg-gray-50">
                        <Pagination :meta="paginationMeta" />
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Pagination from '@/Components/Pagination.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    status: string;
    total: number;
    created_at: string;
    completed_at?: string;
}

interface PaginatedOrders {
    data: Order[];
    meta?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number;
    to?: number;
}

const props = withDefaults(defineProps<{
    orders?: PaginatedOrders;
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

const openDropdown = ref<number | null>(null);

const toggleDropdown = (orderId: number) => {
    openDropdown.value = openDropdown.value === orderId ? null : orderId;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    openDropdown.value = null;
};

// Add click listener to close dropdown when clicking outside
if (typeof window !== 'undefined') {
    window.addEventListener('click', closeDropdown);
}

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

const sort = (field: string) => {
    params.value.sort_field = field;
    params.value.sort_direction = params.value.sort_direction === 'asc' ? 'desc' : 'asc';
    
    router.get(route('orders.index'), params.value, {
        preserveState: true,
        replace: true
    });
};

const route = (window as any).route;

const ordersList = computed(() => {
    try {
        return props.orders?.data || [];
    } catch {
        return [];
    }
});

const ordersCount = computed(() => ordersList.value.length);
const currencyCode = computed(() => props.currency || 'AED');

const paginationMeta = computed(() => {
    if (props.orders?.meta) {
        return props.orders.meta;
    }
    // Handle Laravel's default paginate format
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

const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return '-';
    try {
        return new Date(dateStr).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return '-';
    }
};

const calculateDuration = (created: string, completed?: string): string => {
    if (!completed) return 'In Progress';
    
    const start = new Date(created).getTime();
    const end = new Date(completed).getTime();
    const diff = end - start;
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
};

const getStatusClass = (status: string | null | undefined): string => {
    const classes: Record<string, string> = {
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

const handleAction = (orderId: number, status: string, confirmMessage?: string) => {
    // Close the dropdown
    openDropdown.value = null;
    
    // If there's a confirmation message, show it
    if (confirmMessage) {
        if (confirm(confirmMessage)) {
            updateStatus(orderId, status);
        }
    } else {
        // No confirmation needed, just update
        updateStatus(orderId, status);
    }
};
</script>
