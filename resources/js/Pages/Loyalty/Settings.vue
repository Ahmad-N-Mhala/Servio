<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('loyalty.settings') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('loyalty.manage_settings_description') }}</p>
                </div>
            </div>

            <!-- Settings Form (CardDesigner) -->
            <CardDesigner 
                mode="program" 
                :settings="settings" 
                :earningMethod="earningMethod" 
                @success="handleSuccess"
            />
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import CardDesigner from './CardDesigner.vue';

defineProps<{
    settings?: Record<string, any>;
    earningMethod?: Record<string, any>;
}>();

const handleSuccess = () => {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: {
            message: 'Settings saved successfully',
            type: 'success'
        }
    }));
};
</script>
