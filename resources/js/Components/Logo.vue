<template>
    <div :class="['flex items-center', className]">
        <svg
            viewBox="0 0 200 200"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            :class="['text-primary', iconClass]"
        >
            <defs>
                <mask id="fork-mask">
                    <rect x="0" y="0" width="200" height="200" fill="white" />
                    <path
                        d="M88 65 V100 C88 110 92 115 96 115 V165 H104 V115 C108 115 112 110 112 100 V65"
                        stroke="black"
                        stroke-width="12"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        fill="black"
                    />
                </mask>
            </defs>

            <!-- Handle -->
            <path
                d="M85 45 C85 35 90 25 100 25 C110 25 115 35 115 45"
                stroke="currentColor"
                stroke-width="12"
                stroke-linecap="round"
            />
            
            <g mask="url(#fork-mask)">
                <!-- Cloche Dome -->
                <path
                    d="M30 130 C30 70 60 45 100 45 C140 45 170 70 170 130 Z"
                    fill="currentColor"
                />
                
                <!-- Base -->
                <path
                    d="M20 135 H180 C185 135 190 140 190 145 C190 155 180 160 170 160 H30 C20 160 10 155 10 145 C10 140 15 135 20 135 Z"
                    fill="currentColor"
                />
            </g>
        </svg>
        
        <div v-if="showText" class="ml-3 flex items-center">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">Restaur</span>
            <span class="text-2xl font-bold text-primary">Fy</span>
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
