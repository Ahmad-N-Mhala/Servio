<template>
    <div :class="$attrs.class">
        <label v-if="label" :for="id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>
        <div class="relative group">
            <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                <slot name="prefix" />
            </div>
            
            <textarea
                v-if="type === 'textarea'"
                :id="id"
                ref="input"
                v-bind="{ ...$attrs, class: null }"
                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all hover:border-slate-300 dark:hover:border-slate-600 disabled:bg-slate-100 disabled:text-slate-500"
                :class="[
                    displayError ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/10' : '',
                    $slots.prefix ? 'pl-10' : '',
                    inputClass
                ]"
                :value="modelValue"
                @input="handleInput"
                @blur="handleBlur"
            ></textarea>
            <input
                v-else
                :id="id"
                ref="input"
                :type="type"
                v-bind="{ ...$attrs, class: null }"
                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all hover:border-slate-300 dark:hover:border-slate-600 disabled:bg-slate-100 disabled:text-slate-500"
                :class="[
                    displayError ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/10' : '',
                    $slots.prefix ? 'pl-10' : '',
                    inputClass
                ]"
                :value="modelValue"
                @input="handleInput"
                @blur="handleBlur"
            />
            
            <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <slot name="suffix" />
            </div>

            <div v-if="displayError" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" :class="type === 'textarea' ? 'top-3 bottom-auto' : ''">
                <svg class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <p v-if="displayError" class="mt-1 text-sm text-red-600">{{ displayError }}</p>
        <p v-if="help" class="mt-1 text-sm text-gray-500">{{ help }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    modelValue: string | number | null;
    label?: string;
    error?: string;
    help?: string;
    id?: string;
    required?: boolean;
    inputClass?: string;
    type?: string;
}>();

const emit = defineEmits(['update:modelValue', 'blur']);

const input = ref<HTMLInputElement | null>(null);
const localError = ref<string | null>(null);

const displayError = computed(() => props.error || localError.value);

defineExpose({ focus: () => input.value?.focus() });

const validateEmail = (val: string) => {
    if (!val) {
        localError.value = props.required ? 'Email is required' : null;
        return;
    }

    // Standard email pattern
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    
    if (!emailRegex.test(val)) {
        localError.value = 'Please enter a valid email address (e.g. name@domain.com)';
    } else {
        localError.value = null;
    }
};

const handleInput = (e: Event) => {
    const val = (e.target as HTMLInputElement | HTMLTextAreaElement).value;
    emit('update:modelValue', val);

    if (props.type === 'email' && localError.value) {
        validateEmail(val);
    }
};

const handleBlur = (e: FocusEvent) => {
    const val = (e.target as HTMLInputElement | HTMLTextAreaElement).value;
    if (props.type === 'email') {
        validateEmail(val);
    }
    emit('blur', e);
};

// Watch modelValue to clear errors if empty and not required
watch(() => props.modelValue, (newVal) => {
    if (props.type === 'email' && localError.value) {
        validateEmail(String(newVal || ''));
    }
});
</script>

<script lang="ts">
export default {
    inheritAttrs: false,
};
</script>
