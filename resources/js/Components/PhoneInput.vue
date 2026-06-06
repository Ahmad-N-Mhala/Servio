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
                error 
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
                v-model="localValue"
                class="block flex-1 border-0 bg-transparent py-3 pl-3 text-gray-900 dark:text-slate-200 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-r-xl"
                :class="[error ? 'pr-10' : 'pr-4']"
                :placeholder="placeholder"
                :disabled="disabled"
                @input="emitUpdate"
                @blur="$emit('blur', $event)"
            />

            <div v-if="error" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>
        <p v-if="help" class="mt-1 text-sm text-gray-500">{{ help }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

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

// Parse initial value
watch(() => props.modelValue, (newVal) => {
    // If localValue combined with prefix matches newVal, do nothing (loopback)
    const currentFull = prefix.value + localValue.value;
    if (newVal === currentFull) return;

    if (newVal && prefix.value && newVal.startsWith(prefix.value)) {
        localValue.value = newVal.slice(prefix.value.length);
    } else if (newVal) {
        // Fallback: Model has value but doesn't match prefix. 
        // Could happen if country changed in parent but model not updated, OR loading existing data with different format.
        // We'll trust the model usually, but if we assume prefix is authority:
        // Let's just set localValue to raw if it doesn't match, unless we want to force strip?
        // Better: If I change country US -> UAE. Prefix +1 -> +971.
        // Model was +1555.
        // New Prefix +971.
        // Does +1555 start with +971? No.
        // So localValue becomes "+1555".
        // Emit -> "+971+1555". Weird.
        
        // We rely on the `watch(prefix)` block to handle country swaps. 
        // This watch is mainly for external updates or initial load.
        localValue.value = newVal; // Just show raw if mismatch
    } else {
        localValue.value = '';
    }
}, { immediate: true });

// If country changes, we want to Keep local number but Swap prefix.
watch(prefix, (newPrefix, oldPrefix) => {
    // Re-emit immediately with new prefix + existing local
    emitUpdate();
});

function emitUpdate() {
    emit('update:modelValue', prefix.value + localValue.value);
}
</script>

<script lang="ts">
export default {
    inheritAttrs: false,
};
</script>
