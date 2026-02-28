<template>
    <div class="glass-card rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl group border border-white/20 dark:border-gray-700/50">
        <!-- Header with more premium accent -->
        <div class="relative px-6 py-5 border-b border-gray-100/30 dark:border-gray-700/30">
            <!-- Dynamic Gradient accent line -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-emerald-400 to-teal-300 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-2.5 rounded-xl bg-primary/10 transition-transform group-hover:scale-110 duration-300">
                        <slot name="icon">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </slot>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">{{ title }}</h3>
                        <p v-if="subtitle" class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ subtitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>
        
        <!-- Chart Container -->
        <div class="p-5 sm:p-6" :style="{ height: height }">
            <slot></slot>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string | number;
    height?: string;
}>();
</script>
