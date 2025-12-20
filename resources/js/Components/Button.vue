<template>
    <button
        :type="type"
        :class="[
            'relative inline-flex items-center justify-center rounded-xl font-bold transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden',
            variantClasses,
            sizeClasses,
            widthClass,
            { 'btn-shimmer btn-glow': variant === 'primary' && !disabled && !loading }
        ]"
        :disabled="disabled || loading"
    >
        <!-- Loading Spinner -->
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-5 w-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        
        <!-- Icon Slot -->
        <span v-if="$slots.icon" class="mr-2">
            <slot name="icon"></slot>
        </span>
        
        <!-- Button Content -->
        <slot></slot>
        
        <!-- Gradient Shine Effect -->
        <span 
            v-if="variant === 'primary' && !disabled && !loading" 
            class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full transition-transform duration-700 ease-in-out"
        ></span>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'danger' | 'outline' | 'ghost' | 'success';
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
    disabled?: boolean;
    loading?: boolean;
    block?: boolean;
}>(), {
    type: 'button',
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
    block: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-gradient-to-r from-primary to-primary-hover text-white shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 active:scale-95 focus:ring-primary';
        case 'secondary':
            return 'bg-gradient-to-r from-slate-600 to-slate-700 text-white shadow-lg shadow-slate-200 hover:shadow-xl hover:from-slate-700 hover:to-slate-800 active:scale-95 focus:ring-slate-500';
        case 'success':
            return 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/30 active:scale-95 focus:ring-emerald-500';
        case 'danger':
            return 'bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg shadow-rose-500/20 hover:shadow-xl hover:shadow-rose-500/30 active:scale-95 focus:ring-rose-500';
        case 'outline':
            return 'border-2 border-primary text-primary bg-transparent hover:bg-primary hover:text-white hover:shadow-lg hover:shadow-primary/20 active:scale-95 focus:ring-primary';
        case 'ghost':
            return 'text-slate-600 dark:text-slate-300 bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white active:scale-95 focus:ring-slate-500';
        default:
            return 'bg-gradient-to-r from-primary to-primary-hover text-white shadow-lg shadow-primary/20 hover:shadow-xl active:scale-95';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2.5 py-1.5 text-xs';
        case 'sm':
            return 'px-3 py-2 text-sm';
        case 'md':
            return 'px-5 py-2.5 text-sm';
        case 'lg':
            return 'px-6 py-3 text-base';
        case 'xl':
            return 'px-8 py-4 text-lg';
        default:
            return 'px-5 py-2.5 text-sm';
    }
});

const widthClass = computed(() => props.block ? 'w-full' : '');
</script>
