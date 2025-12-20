<template>
    <div class="glass-card rounded-2xl overflow-hidden shadow-soft border border-slate-100 dark:border-slate-800">
        <!-- Table Header (Title, Search, Actions) -->
        <div v-if="title || search !== undefined || $slots['header-actions']" class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Left Side: Title and Search -->
                <div class="flex items-center gap-4 flex-1">
                    <h3 v-if="title" class="text-xl font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ title }}</h3>
                    
                    <!-- Search Bar -->
                    <div v-if="search !== undefined" class="relative w-full sm:max-w-xs group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-primary">
                            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            :value="search"
                            @input="$emit('update:search', ($event.target as HTMLInputElement).value)"
                            type="text"
                            placeholder="Search..."
                            class="block w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all duration-300"
                        >
                    </div>
                </div>

                <!-- Right Side: Actions -->
                <div v-if="$slots['header-actions']" class="flex items-center gap-3">
                    <slot name="header-actions"></slot>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <!-- Table Head -->
                <thead class="bg-gray-50/50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:bg-gray-100 transition-colors group' : '',
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
                                <span :class="{ 'text-gray-700': column.sortable && sortKey === column.key }">{{ column.label }}</span>
                                <!-- Sort Icons -->
                                <div v-if="column.sortable" class="flex flex-col">
                                    <svg class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors" 
                                         :class="{ 'text-primary': sortKey === column.key && sortDirection === 'asc' }"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </th>
                        <th v-if="$slots.actions" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
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
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">No data found</h3>
                                <p class="text-sm text-gray-500">{{ emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr 
                        v-else
                        v-for="(row, index) in sortedData"
                        :key="row[rowKey] || index"
                        class="hover:bg-gray-50/50 transition-colors duration-150"
                        :class="{ 'bg-primary/5': highlightRow && highlightRow(row) }"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 whitespace-nowrap text-sm text-gray-600',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : ''
                            ]"
                        >
                            <!-- Custom Cell Slot -->
                            <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)">
                                <!-- Default Cell Rendering -->
                                <span v-if="column.format === 'currency'" class="font-semibold text-gray-900 font-mono">
                                    {{ currency }} {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'number'" class="text-gray-900 font-mono">
                                    {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'date'" class="text-gray-500">
                                    {{ formatDate(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'datetime'" class="text-gray-500">
                                    {{ formatDateTime(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'badge'" :class="getBadgeClass(getCellValue(row, column.key))">
                                    {{ getCellValue(row, column.key) }}
                                </span>
                                <span v-else>
                                    {{ getCellValue(row, column.key) }}
                                </span>
                            </slot>
                        </td>

                        <!-- Actions Column -->
                        <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <slot name="actions" :row="row"></slot>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer (Pagination) -->
        <div v-if="pagination || $slots.footer" class="border-t border-gray-100 bg-gray-50/50">
            <slot name="footer">
                <Pagination v-if="pagination" :meta="pagination" />
            </slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

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
    pagination?: any; // Pagination meta/links object
    search?: string; // Search model value
    title?: string;
    emptyMessage?: string;
    rowKey?: string;
    currency?: string;
    highlightRow?: (row: any) => boolean;
}>(), {
    emptyMessage: 'No items to display',
    rowKey: 'id',
    currency: 'AED'
});

const emit = defineEmits<{
    (e: 'sort', key: string, direction: 'asc' | 'desc'): void;
    (e: 'update:search', value: string): void;
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
