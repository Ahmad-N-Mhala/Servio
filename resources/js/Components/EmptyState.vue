<template>
    <div class="flex flex-col items-center justify-center py-12 px-4">
        <!-- Icon/Illustration -->
        <div class="mb-6">
            <slot name="icon">
                <svg 
                    :class="iconSizeClass" 
                    class="text-gray-300" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path 
                        v-if="icon === 'box'"
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" 
                    />
                    <path 
                        v-else-if="icon === 'search'"
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" 
                    />
                    <path 
                        v-else-if="icon === 'folder'"
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" 
                    />
                    <path 
                        v-else-if="icon === 'clipboard'"
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" 
                    />
                    <path 
                        v-else
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" 
                    />
                </svg>
            </slot>
        </div>

        <!-- Message -->
        <div class="text-center max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ title }}
            </h3>
            <p class="text-gray-500 mb-6">
                {{ description }}
            </p>
        </div>

        <!-- Action Button -->
        <div v-if="actionText || $slots.action">
            <slot name="action">
                <Button 
                    :variant="actionVariant" 
                    @click="$emit('action')"
                >
                    <template v-if="actionIcon" #icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </template>
                    {{ actionText }}
                </Button>
            </slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Button from './Button.vue';

const props = withDefaults(defineProps<{
    title?: string;
    description?: string;
    icon?: 'box' | 'search' | 'folder' | 'clipboard' | 'custom';
    iconSize?: 'sm' | 'md' | 'lg' | 'xl';
    actionText?: string;
    actionVariant?: 'primary' | 'secondary' | 'outline';
    actionIcon?: boolean;
}>(), {
    title: 'No data found',
    description: 'Get started by adding your first item',
    icon: 'box',
    iconSize: 'lg',
    actionVariant: 'primary',
    actionIcon: true
});

defineEmits<{
    (e: 'action'): void;
}>();

const iconSizeClass = computed(() => {
    switch (props.iconSize) {
        case 'sm':
            return 'w-12 h-12';
        case 'md':
            return 'w-16 h-16';
        case 'lg':
            return 'w-20 h-20';
        case 'xl':
            return 'w-24 h-24';
        default:
            return 'w-20 h-20';
    }
});
</script>
