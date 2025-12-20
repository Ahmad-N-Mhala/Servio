<template>
    <div class="glass-card rounded-2xl overflow-hidden card-hover">
        <!-- Header with gradient accent -->
        <div class="relative px-6 py-4 border-b border-gray-100/50 dark:border-gray-700/50">
            <!-- Gradient accent line -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-primary-hover to-primary-300"></div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary/10">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h3>
                </div>
                <slot name="actions"></slot>
            </div>
        </div>
        
        <!-- Chart Container -->
        <div class="p-4 sm:p-6" :style="{ height: height }">
            <slot></slot>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    height?: string;
}>();
</script>
