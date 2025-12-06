<template>
    <div :class="$attrs.class">
        <label v-if="label" :for="id" class="block text-sm font-semibold text-gray-700 mb-2 ml-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                :id="id"
                ref="input"
                v-bind="{ ...$attrs, class: null }"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3 px-4 transition-all hover:border-gray-400 disabled:bg-gray-100 disabled:text-gray-500"
                :class="[
                    error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '',
                    inputClass
                ]"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />
            <div v-if="error" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="help" class="mt-1 text-sm text-gray-500">{{ help }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    modelValue: string | number;
    label?: string;
    error?: string;
    help?: string;
    id?: string;
    required?: boolean;
    inputClass?: string;
}>();

defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

defineExpose({ focus: () => input.value?.focus() });
</script>

<script lang="ts">
export default {
    inheritAttrs: false,
};
</script>
