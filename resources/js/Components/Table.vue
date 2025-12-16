<template>
    <div class="glass-card rounded-2xl overflow-hidden shadow-lg">
        <!-- Table Header (Optional Title/Actions) -->
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 v-if="title" class="text-lg font-bold text-gray-900">{{ title }}</h3>
                <slot name="header"></slot>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Table Head -->
                <thead class="bg-gradient-to-r from-gray-100 to-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:bg-gray-200 transition-colors' : '',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : ''
                            ]"
                            @click="column.sortable ? handleSort(column.key) : null"
                        >
                            <div class="flex items-center gap-2"
                                :class="{
                                    'justify-center': column.align === 'center',
                                    'justify-end': column.align === 'right'
                                }"
                            >
                                <span>{{ column.label }}</span>
                                <svg v-if="column.sortable && sortKey === column.key" class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg v-else-if="column.sortable" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th v-if="$slots.actions" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-100">
                    <!-- Empty State -->
                    <tr v-if="!data || data.length === 0">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-gray-500 font-medium">{{ emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr 
                        v-else
                        v-for="(row, index) in sortedData"
                        :key="row[rowKey] || index"
                        class="hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-primary/5': highlightRow && highlightRow(row) }"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 whitespace-nowrap text-sm',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : ''
                            ]"
                        >
                            <!-- Custom Cell Slot -->
                            <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)">
                                <!-- Default Cell Rendering -->
                                <span v-if="column.format === 'currency'" class="font-semibold text-gray-900">
                                    {{ currency }} {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'number'" class="font-medium text-gray-700">
                                    {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'date'" class="text-gray-600">
                                    {{ formatDate(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'datetime'" class="text-gray-600">
                                    {{ formatDateTime(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'badge'" :class="getBadgeClass(getCellValue(row, column.key))">
                                    {{ getCellValue(row, column.key) }}
                                </span>
                                <span v-else class="text-gray-700">
                                    {{ getCellValue(row, column.key) }}
                                </span>
                            </slot>
                        </td>

                        <!-- Actions Column -->
                        <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <slot name="actions" :row="row"></slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer (Pagination, etc.) -->
        <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    format?: 'text' | 'currency' | 'number' | 'date' | 'datetime' | 'badge';
    align?: 'left' | 'center' | 'right';
}

const props = withDefaults(defineProps<{
    columns: Column[];
    data: any[];
    title?: string;
    emptyMessage?: string;
    rowKey?: string;
    currency?: string;
    highlightRow?: (row: any) => boolean;
}>(), {
    emptyMessage: 'No data available',
    rowKey: 'id',
    currency: 'AED'
});

const emit = defineEmits<{
    (e: 'sort', key: string, direction: 'asc' | 'desc'): void;
}>();

const sortKey = ref<string>('');
const sortDirection = ref<'asc' | 'desc'>('asc');

const sortedData = computed(() => {
    if (!sortKey.value) return props.data;

    return [...props.data].sort((a, b) => {
        const aVal = getCellValue(a, sortKey.value);
        const bVal = getCellValue(b, sortKey.value);

        if (aVal === bVal) return 0;

        const comparison = aVal > bVal ? 1 : -1;
        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

const handleSort = (key: string) => {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
    
    emit('sort', key, sortDirection.value);
};

const getCellValue = (row: any, key: string) => {
    return key.split('.').reduce((obj, k) => obj?.[k], row);
};

const formatNumber = (value: any): string => {
    if (value === null || value === undefined) return '0.00';
    return Number(value).toFixed(2);
};

const formatDate = (value: any): string => {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (value: any): string => {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const getBadgeClass = (value: string): string => {
    const baseClasses = 'px-3 py-1 rounded-full text-xs font-bold inline-block';
    
    switch (value?.toLowerCase()) {
        case 'active':
        case 'completed':
        case 'paid':
        case 'success':
            return `${baseClasses} bg-green-100 text-green-800`;
        case 'pending':
        case 'processing':
            return `${baseClasses} bg-yellow-100 text-yellow-800`;
        case 'cancelled':
        case 'failed':
        case 'inactive':
            return `${baseClasses} bg-red-100 text-red-800`;
        case 'preparing':
        case 'ready':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};
</script>
