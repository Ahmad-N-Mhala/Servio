<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="preset in presets"
                    :key="preset.key"
                    @click="selectPreset(preset)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                        selectedPresetKey === preset.key
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
                    <span class="text-gray-500 dark:text-gray-400">{{ $t('reports.to') }}</span>
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
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const emit = defineEmits<{
    (e: 'update', value: { startDate: string; endDate: string }): void;
}>();

const props = defineProps<{
    initialStartDate?: string;
    initialEndDate?: string;
}>();

const startDate = ref('');
const endDate = ref('');
const selectedPresetKey = ref(''); // Store key instead of label

const presets = computed(() => [
    { key: 'today', label: t('dashboard_page.today'), days: 0 },
    { key: 'yesterday', label: t('common.yesterday'), days: 1, isYesterday: true },
    { key: 'last_7_days', label: t('common.last_7_days'), days: 7 },
    { key: 'last_30_days', label: t('common.last_30_days'), days: 30 },
    { key: 'this_month', label: t('dashboard_page.this_month'), isMonth: true },
]);

const formatDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getPresetDates = (preset: any) => {
    const today = new Date();
    let start = '';
    let end = '';
    
    if (preset.isYesterday) {
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        start = formatDate(yesterday);
        end = formatDate(yesterday);
    } else if (preset.isMonth) {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        start = formatDate(firstDay);
        end = formatDate(today);
    } else if (preset.days !== undefined && preset.days === 0) {
        start = formatDate(today);
        end = formatDate(today);
    } else {
        const pastDate = new Date(today);
        pastDate.setDate(pastDate.getDate() - (preset.days || 0));
        start = formatDate(pastDate);
        end = formatDate(today);
    }
    return { start, end };
};

const selectPreset = (preset: any) => {
    selectedPresetKey.value = preset.key;
    const { start, end } = getPresetDates(preset);
    startDate.value = start;
    endDate.value = end;
    emitUpdate(); // Directly emit
};

const onDateChange = () => {
    selectedPresetKey.value = ''; // Clear preset on manual change
    emitUpdate();
};

const emitUpdate = () => {
    if (startDate.value && endDate.value) {
        emit('update', {
            startDate: startDate.value,
            endDate: endDate.value,
        });
    }
};

const syncWithProps = () => {
    if (props.initialStartDate && props.initialEndDate) {
        startDate.value = props.initialStartDate;
        endDate.value = props.initialEndDate;
        
        // Try to match specific preset
        const match = presets.value.find(p => {
             const { start, end } = getPresetDates(p);
             return start === props.initialStartDate && end === props.initialEndDate;
        });
        
        if (match) {
            selectedPresetKey.value = match.key;
        } else {
             selectedPresetKey.value = '';
        }
    } else {
        // Default to Last 7 Days if no props provided (initial load only preferably)
         const defaultPreset = presets.value[2];
         if (defaultPreset) selectPreset(defaultPreset);
    }
};

onMounted(() => {
    syncWithProps();
});

watch(() => [props.initialStartDate, props.initialEndDate], () => {
    syncWithProps();
});
</script>
