<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
                <button class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-hover shadow-lg shadow-primary/30">
                    Create New Plan
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-full uppercase tracking-wide">{{ plan.slug }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900">{{ plan.currency }}{{ plan.price_monthly }}</p>
                            <p class="text-xs text-gray-400">/month</p>
                        </div>
                    </div>
                    
                    <div class="flex-grow space-y-2 mb-6">
                        <p class="text-sm text-gray-600 font-medium">Features:</p>
                        <ul class="text-sm text-gray-500 space-y-1">
                            <li v-for="(feature, idx) in plan.features.slice(0, 4)" :key="idx" class="flex items-center gap-2">
                                 <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ feature }}
                            </li>
                             <li v-if="plan.features.length > 4" class="text-xs text-gray-400 pl-6">+ {{ plan.features.length - 4 }} more</li>
                        </ul>
                    </div>
                    
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-50 mt-auto">
                        <button class="flex-1 bg-gray-50 text-gray-700 hover:bg-gray-100 py-2 rounded-lg text-sm font-semibold transition-colors">Edit</button>
                        <button class="flex-1 bg-red-50 text-red-600 hover:bg-red-100 py-2 rounded-lg text-sm font-semibold transition-colors">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    plans: any[];
}>();
</script>
