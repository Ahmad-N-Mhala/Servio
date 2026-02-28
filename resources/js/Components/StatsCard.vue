<template>
    <div 
        class="relative overflow-hidden rounded-2xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl group border border-white/20 dark:border-gray-700/50"
        :class="[gradientClass, colorGlowClass]"
    >
        <div class="p-5 sm:p-6 relative z-10">
            <div class="flex items-center justify-between mb-5">
                <div :class="`p-3 rounded-2xl shadow-inner transition-transform group-hover:scale-110 duration-300 ${iconBgClass}`">
                    <component :is="iconComponent" class="w-6 h-6" :class="iconColorClass" />
                </div>
                <div v-if="trend" class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold backdrop-blur-md bg-white/30 dark:bg-black/20 shadow-sm" :class="trendColorClass">
                    <svg v-if="trend > 0" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    <span>{{ Math.abs(trend) }}%</span>
                </div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ title }}</p>
                <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white flex items-baseline gap-1">
                    <span ref="valueElement">{{ displayValue }}</span>
                </p>
                <div v-if="subtitle" class="flex items-center gap-1.5 mt-3">
                    <div class="w-1.5 h-1.5 rounded-full" :class="dotColorClass"></div>
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 truncate opacity-80 uppercase tracking-tighter">{{ subtitle }}</p>
                </div>
            </div>
        </div>
        
        <!-- Decorative elements -->
        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full opacity-10 blur-3xl" :class="glowBgClass"></div>
        <div class="absolute inset-0 bg-white/40 dark:bg-transparent backdrop-blur-[2px] pointer-events-none"></div>
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref, watch } from 'vue';

const props = defineProps<{
    title: string;
    value: number | string;
    icon: 'orders' | 'revenue' | 'staff' | 'customers' | 'waste' | 'time' | 'feedback' | 'gift';
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
        feedback: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' })
        ]),
        gift: h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0l-8 4-8-4M4 7v10l8 4 8-4V7' }) // Use a box icon for now or actual gift
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

const colorGlowClass = computed(() => {
    const glows = {
        blue: 'hover:shadow-blue-500/20',
        green: 'hover:shadow-emerald-500/20',
        yellow: 'hover:shadow-amber-500/20',
        purple: 'hover:shadow-purple-500/20',
        red: 'hover:shadow-rose-500/20',
    };
    return glows[props.color];
});

const dotColorClass = computed(() => {
    const dots = {
        blue: 'bg-blue-500',
        green: 'bg-emerald-500',
        yellow: 'bg-amber-500',
        purple: 'bg-purple-500',
        red: 'bg-rose-500',
    };
    return dots[props.color];
});

const glowBgClass = computed(() => {
    const glows = {
        blue: 'bg-blue-500',
        green: 'bg-emerald-500',
        yellow: 'bg-amber-500',
        purple: 'bg-purple-500',
        red: 'bg-rose-500',
    };
    return glows[props.color];
});

const trendColorClass = computed(() => {
    return props.trend && props.trend > 0 
        ? 'text-emerald-600 dark:text-emerald-400' 
        : 'text-rose-600 dark:text-rose-400';
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
