<template>
    <MainLayout>
        <div class="min-h-[60vh] flex items-center justify-center">
            <Modal :show="true" :close-on-backdrop="false" :show-close="false" size="md">
                <template #header>
                    <div class="flex items-center gap-3 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-lg font-bold">Access Denied</h3>
                    </div>
                </template>
                
                <div class="text-center py-4">
                    <p class="text-gray-600 dark:text-gray-300 mb-6 text-base">
                        {{ message || "You do not have permission to view this page." }}
                    </p>
                    
                    <div class="flex justify-center gap-3">
                        <button 
                            @click="goBack"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Go Back
                        </button>
                        
                        <Link 
                            :href="landing_url || route('dashboard')" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors shadow-sm"
                        >
                            Go Home
                        </Link>
                    </div>
                </div>
            </Modal>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';

// Ziggy is globally available
declare const route: any;

const props = defineProps<{
    message?: string;
    landing_url?: string;
}>();

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback if no history
        window.location.href = props.landing_url || route('dashboard');
    }
};
</script>
