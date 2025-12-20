<template>
    <div class="relative group">
        <label v-if="label" :for="id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>

        <div class="relative">
            <!-- Icon (Left) -->
            <div v-if="$slots.icon || icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <slot name="icon">
                    <svg v-if="icon === 'search'" class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg v-else-if="icon === 'dropdown'" class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </slot>
            </div>

            <!-- Select Element -->
            <select
                :id="id"
                :value="modelValue"
                @change="handleChange"
                :disabled="disabled"
                :required="required"
                :class="[
                    'w-full rounded-xl border appearance-none transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-primary/10',
                    error 
                        ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/10' 
                        : 'border-slate-200 dark:border-slate-700 focus:border-primary',
                    disabled ? 'bg-slate-100 cursor-not-allowed text-slate-500' : 'bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm',
                    $slots.icon || icon ? 'pl-10 pr-10 py-3' : 'px-4 py-3',
                    sizeClass
                ]"
            >
                <!-- Placeholder Option -->
                <option v-if="placeholder" value="" disabled :selected="!modelValue">
                    {{ placeholder }}
                </option>

                <!-- Options via Slot -->
                <slot></slot>

                <!-- Options via Props -->
                <option 
                    v-for="option in options" 
                    :key="getOptionValue(option)" 
                    :value="getOptionValue(option)"
                    :disabled="option.disabled"
                    class="dark:bg-slate-800"
                >
                    {{ getOptionLabel(option) }}
                </option>
            </select>

            <!-- Dropdown Arrow -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-rose-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>

        <!-- Help Text -->
        <p v-if="hint && !error" class="mt-2 text-sm text-slate-500">
            {{ hint }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<{
    modelValue: string | number | null;
    label?: string;
    id?: string;
    placeholder?: string;
    options?: SelectOption[] | string[] | number[];
    error?: string;
    hint?: string;
    disabled?: boolean;
    required?: boolean;
    size?: 'sm' | 'md' | 'lg';
    icon?: 'search' | 'dropdown' | null;
}>(), {
    size: 'md',
    icon: null
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const sizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'text-sm';
        case 'lg':
            return 'text-base py-3';
        default:
            return 'text-sm';
    }
});

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
};

const getOptionValue = (option: any): string | number => {
    if (typeof option === 'object' && option !== null) {
        return option.value;
    }
    return option;
};

const getOptionLabel = (option: any): string => {
    if (typeof option === 'object' && option !== null) {
        return option.label;
    }
    return String(option);
};
</script>
