<template>
    <div :class="$attrs.class">
        <label v-if="label" :for="id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>
        <div 
            dir="ltr"
            class="relative flex flex-row rounded-xl border bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm transition-all" 
            :class="[
                displayError 
                    ? 'border-rose-300 focus-within:border-rose-500 focus-within:ring-4 focus-within:ring-rose-500/10' 
                    : 'border-slate-200 dark:border-slate-700 focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 hover:border-slate-300 dark:hover:border-slate-600',
                disabled ? 'opacity-60 cursor-not-allowed bg-slate-100' : ''
            ]"
        >
            <span class="flex select-none items-center text-gray-500 dark:text-slate-400 sm:text-sm bg-slate-50 dark:bg-slate-900/50 rounded-l-xl border-r border-slate-200 dark:border-slate-700 px-3 min-w-[60px] justify-center font-mono">
                {{ prefix }}
            </span>
            <input
                :id="id"
                ref="input"
                type="tel"
                :value="localValue"
                class="block flex-1 border-0 bg-transparent py-3 pl-3 text-gray-900 dark:text-slate-200 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-r-xl"
                :class="[displayError ? 'pr-10' : 'pr-4']"
                :placeholder="placeholder"
                :disabled="disabled"
                @input="handleInput"
                @blur="handleBlur"
            />

            <div v-if="displayError" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <p v-if="displayError" class="mt-1 text-sm text-rose-600">{{ displayError }}</p>
        <p v-if="help" class="mt-1 text-sm text-gray-500">{{ help }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    modelValue: string | null;
    country: string;
    label?: string;
    error?: string;
    help?: string;
    id?: string;
    required?: boolean;
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits(['update:modelValue', 'blur']);

const countryPhonePrefixMap: Record<string, string> = {
    'United Arab Emirates': '+971',
    'Saudi Arabia': '+966',
    'Qatar': '+974',
    'Kuwait': '+965',
    'Bahrain': '+973',
    'Oman': '+968',
    'United States': '+1',
    'United Kingdom': '+44',
    'Canada': '+1',
    'Australia': '+61',
    'India': '+91',
    'Pakistan': '+92',
    'Egypt': '+20',
    'Jordan': '+962',
    'Lebanon': '+961',
    'Germany': '+49',
    'France': '+33',
    'Italy': '+39',
    'Spain': '+34'
};

const prefix = computed(() => {
    return countryPhonePrefixMap[props.country] || '';
});

const localValue = ref('');
const localError = ref<string | null>(null);

const displayError = computed(() => props.error || localError.value);

// Parse initial value
watch(() => props.modelValue, (newVal) => {
    const currentFull = prefix.value + localValue.value;
    if (newVal === currentFull) return;

    if (newVal && prefix.value && newVal.startsWith(prefix.value)) {
        localValue.value = newVal.slice(prefix.value.length).replace(/\D/g, '');
    } else if (newVal) {
        localValue.value = newVal.replace(/\D/g, '');
    } else {
        localValue.value = '';
    }

    if (localError.value) {
        validateLocalValue();
    }
}, { immediate: true });

// If country changes, keep the local number but swap the prefix
watch(prefix, () => {
    validateLocalValue();
    emitUpdate();
});

const validateLocalValue = () => {
    if (!localValue.value) {
        localError.value = props.required ? 'Phone number is required' : null;
        return;
    }

    const cleaned = localValue.value;

    if (!/^\d+$/.test(cleaned)) {
        localError.value = 'Phone number must contain only digits';
        return;
    }

    if (prefix.value === '+971') {
        // UAE: 9 digits starting with 5 (mobile) or 8-9 digits (landline)
        if (!/^[2-9]\d{7,8}$/.test(cleaned)) {
            localError.value = 'UAE phone number must be 8 or 9 digits (e.g. 501234567)';
            return;
        }
    } else if (prefix.value === '+966') {
        // Saudi: 9 digits starting with 5
        if (!/^5\d{8}$/.test(cleaned)) {
            localError.value = 'Saudi phone number must be 9 digits and start with 5';
            return;
        }
    } else if (prefix.value === '+965' || prefix.value === '+974' || prefix.value === '+973' || prefix.value === '+968') {
        // Kuwait, Qatar, Bahrain, Oman: 8 digits
        if (!/^\d{8}$/.test(cleaned)) {
            localError.value = `${props.country} phone number must be exactly 8 digits`;
            return;
        }
    } else if (prefix.value === '+1') {
        // US/Canada: 10 digits
        if (!/^\d{10}$/.test(cleaned)) {
            localError.value = 'US/Canada phone number must be exactly 10 digits';
            return;
        }
    } else if (prefix.value === '+44') {
        // UK: 10 digits
        if (!/^\d{10}$/.test(cleaned)) {
            localError.value = 'UK phone number must be exactly 10 digits';
            return;
        }
    } else if (prefix.value === '+91') {
        // India: 10 digits starting with 6-9
        if (!/^[6-9]\d{9}$/.test(cleaned)) {
            localError.value = 'India phone number must be 10 digits and start with 6-9';
            return;
        }
    } else {
        // General fallback: 7 to 12 digits
        if (cleaned.length < 7 || cleaned.length > 12) {
            localError.value = 'Phone number must be between 7 and 12 digits';
            return;
        }
    }

    localError.value = null;
};

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    localValue.value = target.value.replace(/\D/g, ''); // Restrict to numbers only
    
    if (localError.value) {
        validateLocalValue();
    }
    emitUpdate();
};

const handleBlur = (e: FocusEvent) => {
    validateLocalValue();
    emit('blur', e);
};

function emitUpdate() {
    // If phone number is empty, emit null or empty string to ensure validation triggers
    if (!localValue.value) {
        emit('update:modelValue', '');
        return;
    }
    emit('update:modelValue', prefix.value + localValue.value);
}
</script>

<script lang="ts">
export default {
    inheritAttrs: false,
};
</script>
