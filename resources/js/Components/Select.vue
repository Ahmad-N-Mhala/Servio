<template>
    <div class="relative group">
        <label v-if="label" :for="id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>

        <div class="relative">
            <v-select
                :modelValue="modelValue"
                @update:modelValue="handleChange"
                :options="formattedOptions"
                :reduce="(opt: any) => opt.value"
                :label="'label'"
                :placeholder="placeholder"
                :disabled="disabled"
                :searchable="true"
                :clearable="!required"
                class="style-chooser"
                :class="{'has-error': error}"
            >
                <template #option="option">
                    <span :class="{'text-gray-400': option.disabled}">{{ option.label }}</span>
                </template>
                <template #no-options>
                    <span class="p-2 text-sm text-gray-500">{{ $t('common.no_matching_options') }}</span>
                </template>
            </v-select>
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
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

interface SelectOption {
    value: string | number | null;
    label: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<{
    modelValue: string | number | null;
    label?: string;
    id?: string;
    placeholder?: string;
    options?: any[]; // Allow flexibility as options are formatted
    error?: string;
    hint?: string;
    disabled?: boolean;
    required?: boolean;
    size?: 'sm' | 'md' | 'lg';
    icon?: 'search' | 'dropdown' | null;
}>(), {
    size: 'md',
    icon: null,
    options: () => []
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number | null): void;
}>();

const handleChange = (value: any) => {
    emit('update:modelValue', value);
};

// Format options to standard {label, value} format for v-select
const formattedOptions = computed(() => {
    if (!props.options) return [];
    
    // Check if it's already a clean array of objects
    return props.options.map((opt: any) => {
        if (typeof opt === 'object' && opt !== null) {
            // If it's a Vue (v-for) iteration that passed objects directly
            return {
                label: opt.label || opt.name || String(opt.value),
                value: opt.value !== undefined ? opt.value : opt.id,
                disabled: opt.disabled
            };
        }
        // Primitives
        return {
            label: String(opt),
            value: opt
        };
    });
});
</script>

<style>
/* Custom Styling for Vue Select to match App Theme */
.style-chooser {
  --vs-controls-color: #64748b;
  --vs-border-color: #e2e8f0;
  --vs-border-width: 1px;
  --vs-border-style: solid;
  --vs-border-radius: 0.75rem; /* rounded-xl */
  --vs-font-size: 0.875rem;
  --vs-line-height: 1.25rem;
  --vs-state-disabled-bg: #f1f5f9;
  --vs-state-disabled-color: #94a3b8;
  --vs-state-disabled-controls-color: #cbd5e1;
  --vs-state-disabled-cursor: not-allowed;
}

.dark .style-chooser {
  --vs-controls-color: #94a3b8;
  --vs-border-color: #334155;
  --vs-bg: rgba(30, 41, 59, 0.5);
  --vs-selected-color: #e2e8f0;
  --vs-dropdown-bg: #1e293b;
  --vs-dropdown-color: #e2e8f0;
  --vs-dropdown-option-color: #e2e8f0;
  --vs-dropdown-option--active-bg: #475569;
  --vs-dropdown-option--active-color: #f8fafc;
}

.style-chooser .vs__dropdown-toggle {
  background: var(--vs-bg, rgba(255, 255, 255, 0.5));
  backdrop-filter: blur(4px);
  padding: 0.5rem 0.25rem; /* Match py-3 roughly */
}

.style-chooser.has-error .vs__dropdown-toggle {
  border-color: #fca5a5;
  color: #e11d48;
}

.vs__search::placeholder {
    color: #94a3b8;
}

/* Ensure dropdown menu appears above other content */
.style-chooser .vs__dropdown-menu {
    z-index: 9999 !important;
}
</style>
