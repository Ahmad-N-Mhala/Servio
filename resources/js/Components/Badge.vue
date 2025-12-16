<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-full font-semibold transition-all duration-200',
            sizeClasses,
            variantClasses,
            dotPosition !== 'none' ? 'pl-2' : ''
        ]"
    >
        <!-- Status Dot -->
        <span 
            v-if="dotPosition === 'left'"
            :class="[
                'w-2 h-2 rounded-full animate-pulse',
                dotColorClass
            ]"
        ></span>

        <!-- Icon Slot -->
        <span v-if="$slots.icon" class="flex-shrink-0">
            <slot name="icon"></slot>
        </span>

        <!-- Badge Content -->
        <slot></slot>

        <!-- Status Dot (Right) -->
        <span 
            v-if="dotPosition === 'right'"
            :class="[
                'w-2 h-2 rounded-full animate-pulse',
                dotColorClass
            ]"
        ></span>

        <!-- Close Button -->
        <button
            v-if="removable"
            @click="$emit('remove')"
            type="button"
            class="flex-shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center hover:bg-black/10 focus:outline-none transition-colors"
        >
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    variant?: 'success' | 'warning' | 'danger' | 'info' | 'primary' | 'secondary' | 'default';
    size?: 'xs' | 'sm' | 'md' | 'lg';
    dot?: boolean;
    dotPosition?: 'left' | 'right' | 'none';
    removable?: boolean;
}>(), {
    variant: 'default',
    size: 'md',
    dot: false,
    dotPosition: 'none',
    removable: false
});

defineEmits<{
    (e: 'remove'): void;
}>();

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'success':
            return 'bg-green-100 text-green-800 border border-green-200';
        case 'warning':
            return 'bg-amber-100 text-amber-800 border border-amber-200';
        case 'danger':
            return 'bg-red-100 text-red-800 border border-red-200';
        case 'info':
            return 'bg-blue-100 text-blue-800 border border-blue-200';
        case 'primary':
            return 'bg-primary/10 text-primary border border-primary/20';
        case 'secondary':
            return 'bg-purple-100 text-purple-800 border border-purple-200';
        case 'default':
        default:
            return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2 py-0.5 text-xs';
        case 'sm':
            return 'px-2.5 py-1 text-xs';
        case 'md':
            return 'px-3 py-1.5 text-sm';
        case 'lg':
            return 'px-4 py-2 text-base';
        default:
            return 'px-3 py-1.5 text-sm';
    }
});

const dotColorClass = computed(() => {
    switch (props.variant) {
        case 'success':
            return 'bg-green-600';
        case 'warning':
            return 'bg-amber-600';
        case 'danger':
            return 'bg-red-600';
        case 'info':
            return 'bg-blue-600';
        case 'primary':
            return 'bg-primary';
        case 'secondary':
            return 'bg-purple-600';
        default:
            return 'bg-gray-600';
    }
});
</script>
