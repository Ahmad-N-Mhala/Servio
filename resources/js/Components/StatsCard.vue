<template>
    <div 
        class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 dark:border-gray-700"
        :class="gradientClass"
    >
        <div class="p-4 sm:p-6 relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div :class="`p-3 rounded-xl ${iconBgClass}`">
                    <component :is="iconComponent" class="w-6 h-6" :class="iconColorClass" />
                </div>
                <div v-if="trend" class="flex items-center gap-1 text-sm font-medium" :class="trendColorClass">
                    <svg v-if="trend > 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    <span>{{ Math.abs(trend) }}%</span>
                </div>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ title }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                    <span ref="valueElement">{{ displayValue }}</span>
                </p>
                <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ subtitle }}</p>
            </div>
        </div>
        
        <!-- Decorative gradient overlay -->
        <div class="absolute inset-0 opacity-10" :class="overlayClass"></div>
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref, watch } from 'vue';

const props = defineProps<{
    title: string;
    value: number | string;
    icon: 'orders' | 'revenue' | 'staff' | 'customers' | 'waste' | 'time';
    color: 'blue' | 'green' | 'yellow' | 'purple' | 'red';
    trend?: number;
    subtitle?: string;
}>();

const valueElement = ref<HTMLElement | null>(null);
const displayValue = ref(props.value);

const iconComponent = computed(() => {
    const icons = {
        orders: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' })
        ]),
        revenue: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
        ]),
        staff: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' })
        ]),
        customers: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
        ]),
        waste: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' })
        ]),
        time: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
        ]),
    };
    return icons[props.icon];
});

const gradientClass = computed(() => {
    const gradients = {
        blue: 'bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800',
        green: 'bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800',
        yellow: 'bg-gradient-to-br from-yellow-50 to-white dark:from-yellow-900/20 dark:to-gray-800',
        purple: 'bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800',
        red: 'bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-gray-800',
    };
    return gradients[props.color];
});

const iconBgClass = computed(() => {
    const classes = {
        blue: 'bg-blue-100 dark:bg-blue-900/30',
        green: 'bg-green-100 dark:bg-green-900/30',
        yellow: 'bg-yellow-100 dark:bg-yellow-900/30',
        purple: 'bg-purple-100 dark:bg-purple-900/30',
        red: 'bg-red-100 dark:bg-red-900/30',
    };
    return classes[props.color];
});

const iconColorClass = computed(() => {
    const classes = {
        blue: 'text-blue-600 dark:text-blue-400',
        green: 'text-green-600 dark:text-green-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
        purple: 'text-purple-600 dark:text-purple-400',
        red: 'text-red-600 dark:text-red-400',
    };
    return classes[props.color];
});

const overlayClass = computed(() => {
    const classes = {
        blue: 'bg-gradient-to-br from-blue-500 to-blue-600',
        green: 'bg-gradient-to-br from-green-500 to-green-600',
        yellow: 'bg-gradient-to-br from-yellow-500 to-yellow-600',
        purple: 'bg-gradient-to-br from-purple-500 to-purple-600',
        red: 'bg-gradient-to-br from-red-500 to-red-600',
    };
    return classes[props.color];
});

const trendColorClass = computed(() => {
    return props.trend && props.trend > 0 
        ? 'text-green-600 dark:text-green-400' 
        : 'text-red-600 dark:text-red-400';
});

// Animate number counting
watch(() => props.value, (newValue) => {
    if (typeof newValue === 'number' && typeof displayValue.value === 'number') {
        const start = displayValue.value;
        const end = newValue;
        const duration = 1000;
        const startTime = Date.now();
        
        const animate = () => {
            const now = Date.now();
            const progress = Math.min((now - startTime) / duration, 1);
            const current = start + (end - start) * progress;
            displayValue.value = Math.round(current);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        animate();
    } else {
        displayValue.value = newValue;
    }
});
</script>
