<template>
    <div :class="['flex items-center gap-2', className]">
        <img 
            src="/logo.png" 
            alt="Servio Logo" 
            :class="['text-primary object-contain', iconClass]"
        />
        
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
