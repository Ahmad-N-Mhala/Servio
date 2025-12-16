<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
                @click.self="closeOnBackdrop && $emit('close')"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeOnBackdrop && $emit('close')"></div>

                <!-- Modal Content -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="show"
                        :class="[
                            'relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl z-10 max-h-[90vh] overflow-y-auto',
                            sizeClasses
                        ]"
                    >
                        <!-- Header -->
                        <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <slot name="header">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h3>
                            </slot>
                            <button
                                v-if="showClose"
                                @click="$emit('close')"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-4">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';

const props = withDefaults(defineProps<{
    show: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl' | '5xl' | '6xl' | '7xl' | 'full';
    maxWidth?: string; // For backward compatibility/flexibility
    showClose?: boolean;
    closeOnBackdrop?: boolean;
}>(), {
    show: false,
    size: '7xl',
    showClose: true,
    closeOnBackdrop: true
});

defineEmits<{
    close: [];
}>();

const sizeClasses = computed(() => {
    // Start with explicitly passed maxWidth if mapped, otherwise use size
    let currentSize = props.size;
    if (props.maxWidth) {
       // Map maxWidth common string to size if possible, or just ignore if we want strict typing? 
       // Start simple: strict size mapping.
       if (['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', 'full'].includes(props.maxWidth)) {
           currentSize = props.maxWidth as any;
       }
    }

    switch (currentSize) {
        case 'sm':
            return 'w-full max-w-sm mx-4';
        case 'md':
            return 'w-full max-w-md mx-4';
        case 'lg':
            return 'w-full max-w-lg mx-4';
        case 'xl':
            return 'w-full max-w-xl mx-4';
        case '2xl':
            return 'w-full max-w-2xl mx-4';
        case '3xl':
            return 'w-full max-w-3xl mx-4';
        case '4xl':
            return 'w-full max-w-4xl mx-4';
        case '5xl':
            return 'w-full max-w-5xl mx-4';
        case '6xl':
            return 'w-full max-w-6xl mx-4';
        case '7xl':
            return 'w-full max-w-7xl mx-4';
        case 'full':
            return 'w-full mx-4';
        default:
            return 'w-full max-w-md mx-4';
    }
});

// Prevent body scroll when modal is open
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>
