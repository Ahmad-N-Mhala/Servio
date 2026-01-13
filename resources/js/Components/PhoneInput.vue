<template>
    <div :class="$attrs.class">
        <label v-if="label" :for="id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>
        <div class="flex rounded-xl shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 bg-white dark:bg-slate-800 dark:ring-slate-700" :class="{'opacity-60 cursor-not-allowed bg-gray-50': disabled}">
            <span class="flex select-none items-center pl-3 text-gray-500 dark:text-slate-400 sm:text-sm bg-gray-50 dark:bg-slate-900 rounded-l-xl border-r dark:border-slate-700 px-3 min-w-[60px] justify-center font-mono">
                {{ prefix }}
            </span>
            <input
                :id="id"
                ref="input"
                type="tel"
                v-model="localValue"
                class="block flex-1 border-0 bg-transparent py-3 pl-3 text-gray-900 dark:text-slate-200 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-r-xl"
                :placeholder="placeholder"
                :disabled="disabled"
                @input="emitUpdate"
            />
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
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

const emit = defineEmits(['update:modelValue']);

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
