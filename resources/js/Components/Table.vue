<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100" :class="allowOverflow ? 'overflow-visible' : 'overflow-hidden'">
        <!-- Table Header (Title, Search, Actions) -->
        <div v-if="title || search !== undefined || $slots['header-actions']" class="px-6 py-5 border-b border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Left Side: Title and Search -->
                <div class="flex items-center gap-4 flex-1">
                    <h3 v-if="title" class="text-xl font-bold text-gray-900 whitespace-nowrap">{{ title }}</h3>
                    
                    <!-- Search Bar -->
                    <div v-if="search !== undefined" class="relative w-full sm:max-w-xs group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-primary">
                            <svg class="h-4 w-4 text-gray-400 group-focus-within:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            :value="search"
                            @input="$emit('update:search', ($event.target as HTMLInputElement).value)"
                            type="text"
                            placeholder="Search..."
                            class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200"
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
        <div :class="allowOverflow ? 'overflow-visible' : 'overflow-x-auto'">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Table Head -->
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:bg-gray-100 transition-colors group select-none' : '',
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
                                <span :class="{ 'text-gray-700 font-semibold': column.sortable && sortKey === column.key }">{{ column.label }}</span>
                                <!-- Sort Icons -->
                                <div v-if="column.sortable" class="flex flex-col">
                                    <svg v-if="sortKey !== column.key" class="w-3 h-3 text-gray-300 group-hover:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                         <path d="M7 10l5-5 5 5M7 14l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg v-else-if="sortDirection === 'asc'" class="w-3 h-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 14l5-5 5 5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg v-else class="w-3 h-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 10l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </th>
                        <th v-if="$slots.actions" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $t('common.actions') || 'Actions' }}
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Empty State -->
                    <tr v-if="!data || data.length === 0">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">{{ $t('common.no_data') }}</h3>
                                <p class="text-sm text-gray-500">{{ emptyMessage === 'No items to display' ? $t('common.no_items') : emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr 
                        v-else
                        v-for="(row, index) in sortedData"
                        :key="row[rowKey] || index"
                        class="hover:bg-gray-50 transition-colors duration-150"
                        :class="[
                            { 'bg-primary/5': highlightRow && highlightRow(row) },
                            rowClass ? rowClass(row) : ''
                        ]"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 whitespace-nowrap text-sm text-gray-900',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : ''
                            ]"
                        >
                            <!-- Custom Cell Slot -->
                            <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)">
                                <!-- Default Cell Rendering -->
                                <span v-if="column.format === 'currency'" class="font-medium font-mono">
                                    {{ currency }} {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'number'" class="font-mono">
                                    {{ formatNumber(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'date'" class="text-gray-500">
                                    {{ formatDateValue(getCellValue(row, column.key)) }}
                                </span>
                                <span v-else-if="column.format === 'datetime'" class="text-gray-500">
                                    {{ formatDateTimeValue(getCellValue(row, column.key)) }}
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
                        <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <slot name="actions" :row="row" :index="index"></slot>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer (Pagination) -->
        <div v-if="pagination || $slots.footer" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <slot name="footer">
                <Pagination v-if="pagination" :meta="pagination" />
            </slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import { formatDateTime, formatDate } from '@/Utils/dateHelper';

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
    rowClass?: (row: any) => string | object;
    serverSide?: boolean;
    allowOverflow?: boolean;
}>(), {
    emptyMessage: 'No items to display',
    rowKey: 'id',
    currency: 'AED',
    serverSide: false,
    allowOverflow: false
});

const emit = defineEmits<{
    (e: 'sort', key: string, direction: 'asc' | 'desc'): void;
    (e: 'update:search', value: string): void;
}>();

const sortKey = ref<string>('');
const sortDirection = ref<'asc' | 'desc'>('asc');

const sortedData = computed(() => {
    // If server-side sorting is enabled or no sort key, return original data
    if (props.serverSide || !sortKey.value) return props.data;

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

const formatDateValue = (value: any): string => {
    return formatDate(value);
};

const formatDateTimeValue = (value: any): string => {
    return formatDateTime(value);
};

const getBadgeClass = (value: string): string => {
    const baseClasses = 'px-3 py-1 rounded-full text-xs font-bold inline-block';
    
    const normalized = typeof value === 'string' ? value.toLowerCase() : '';
    
    if (normalized.includes('completed') || normalized.includes('paid') || normalized.includes('success') || normalized === 'active') {
        return `${baseClasses} bg-green-100 text-green-800`;
    }
    if (normalized.includes('pending') || normalized.includes('processing')) {
        return `${baseClasses} bg-yellow-100 text-yellow-800`;
    }
    if (normalized.includes('cancelled') || normalized.includes('failed') || normalized.includes('deleted') || normalized.includes('inactive')) {
        return `${baseClasses} bg-red-100 text-red-800`;
    }
    if (normalized.includes('preparing') || normalized.includes('ready')) {
        return `${baseClasses} bg-blue-100 text-blue-800`;
    }
    return `${baseClasses} bg-gray-100 text-gray-800`;
};
</script>
