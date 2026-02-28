<template>
    <div class="glass-card rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20 dark:border-gray-700/50 mb-6 group transition-all duration-300">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex p-2.5 rounded-xl bg-primary/10 text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset.key"
                        @click="selectPreset(preset)"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all duration-300 tracking-wider uppercase',
                            selectedPresetKey === preset.key
                                ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-105'
                                : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-white dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-300 border border-gray-100 dark:border-gray-700'
                        ]"
                    >
                        {{ preset.label }}
                    </button>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/50 p-1.5 rounded-xl border border-gray-100 dark:border-gray-800">
                    <input
                        type="date"
                        v-model="startDate"
                        @change="onDateChange"
                        class="px-3 py-1.5 bg-transparent border-none rounded-lg text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-0 w-36"
                    />
                    <div class="w-4 h-px bg-gray-300 dark:bg-gray-700"></div>
                    <input
                        type="date"
                        v-model="endDate"
                        @change="onDateChange"
                        class="px-3 py-1.5 bg-transparent border-none rounded-lg text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-0 w-36"
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
