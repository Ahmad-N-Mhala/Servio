<template>
    <div :class="['flex items-center gap-2', className]">
        <!-- New Servio Logo Icon -->
        <svg
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            :class="['text-primary', iconClass]"
        >
            <!-- Outer Circle/Plate (Optional, maybe just the S is better for modern look) -->
            <!-- Stylized S (Steam/Service) -->
            <path
                d="M70 25C70 25 30 25 30 50C30 75 70 75 70 100"
                stroke="currentColor"
                stroke-width="12"
                stroke-linecap="round"
                class="opacity-50"
            />
            <path
                d="M70 0C70 0 30 0 30 25C30 50 70 50 70 75"
                stroke="currentColor"
                stroke-width="12"
                stroke-linecap="round"
            />
            <!-- Accent Dot -->
            <circle cx="70" cy="85" r="6" fill="currentColor" />
        </svg>
        
        <div v-if="showText" class="flex items-center">
            <span class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Servio</span>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    className: {
        type: String,
        default: '',
    },
    iconClass: {
        type: String,
        default: 'w-10 h-10',
    },
    showText: {
        type: Boolean,
        default: true,
    },
});
</script>
