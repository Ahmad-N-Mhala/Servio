<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Sales Reports</h1>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">View your sales analytics</p>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
</script>

