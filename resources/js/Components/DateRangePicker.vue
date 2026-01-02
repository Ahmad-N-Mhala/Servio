<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="preset in presets"
                    :key="preset.label"
                    @click="selectPreset(preset)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                        selectedPreset === preset.label
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ preset.label }}
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        v-model="startDate"
                        @change="onDateChange"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                    />
                    <span class="text-gray-500 dark:text-gray-400">to</span>
                    <input
                        type="date"
                        v-model="endDate"
                        @change="onDateChange"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const emit = defineEmits<{
    (e: 'update', value: { startDate: string; endDate: string }): void;
}>();

const props = defineProps<{
    initialStartDate?: string;
    initialEndDate?: string;
}>();

const startDate = ref('');
const endDate = ref('');
const selectedPreset = ref('Last 7 Days');

const presets = [
    { label: 'Today', days: 0 },
    { label: 'Yesterday', days: 1, isYesterday: true },
    { label: 'Last 7 Days', days: 7 },
    { label: 'Last 30 Days', days: 30 },
    { label: 'This Month', isMonth: true },
];

const formatDate = (date: Date): string => {
    // Use local time instead of UTC to avoid timezone issues (e.g. UTC picking previous day)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const selectPreset = (preset: typeof presets[0]) => {
    selectedPreset.value = preset.label;
    const today = new Date();
    
    if (preset.isYesterday) {
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        startDate.value = formatDate(yesterday);
        endDate.value = formatDate(yesterday);
    } else if (preset.isMonth) {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        startDate.value = formatDate(firstDay);
        endDate.value = formatDate(today);
    } else if (preset.days !== undefined && preset.days === 0) {
        startDate.value = formatDate(today);
        endDate.value = formatDate(today);
    } else {
        const pastDate = new Date(today);
        pastDate.setDate(pastDate.getDate() - (preset.days || 0));
        startDate.value = formatDate(pastDate);
        endDate.value = formatDate(today);
    }
    
    onDateChange();
};

const onDateChange = () => {
    if (startDate.value && endDate.value) {
        emit('update', {
            startDate: startDate.value,
            endDate: endDate.value,
        });
    }
};

onMounted(() => {
    if (props.initialStartDate && props.initialEndDate) {
        startDate.value = props.initialStartDate;
        endDate.value = props.initialEndDate;
    } else {
        // Default to Last 7 Days
        selectPreset(presets[2]);
    }
});
</script>
