<template>
    <MainLayout title="Financial">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Financial Overview</h1>
                <p class="mt-2 text-gray-600">Manage your expenses and view sales reports</p>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <!-- Tab Headers -->
                <div class="border-b border-gray-200 bg-gray-50">
                    <nav class="flex -mb-px">
                        <button
                            @click="switchTab('expenses')"
                            :class="[
                                'flex-1 py-4 px-6 text-center font-semibold transition-all duration-200',
                                activeTab === 'expenses'
                                    ? 'border-b-2 border-primary text-primary bg-white'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                            ]"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Monthly Expenses</span>
                            </div>
                        </button>
                        <button
                            @click="switchTab('reports')"
                            :class="[
                                'flex-1 py-4 px-6 text-center font-semibold transition-all duration-200',
                                activeTab === 'reports'
                                    ? 'border-b-2 border-primary text-primary bg-white'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                            ]"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Sales Reports</span>
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Monthly Expenses Tab -->
                    <div v-show="activeTab === 'expenses'">
                        <iframe 
                            :src="(window as any).route('monthly-expenses.index', { month: selectedMonth })"
                            class="w-full border-0"
                            style="height: 800px;"
                        ></iframe>
                    </div>

                    <!-- Sales Reports Tab -->
                    <div v-show="activeTab === 'reports'">
                        <iframe 
                            :src="(window as any).route('reports.sales', filters)"
                            class="w-full border-0"
                            style="height: 800px;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps<{
    activeTab?: string;
    selectedMonth?: string;
    filters?: {
        start_date: string;
        end_date: string;
    };
}>();

const activeTab = ref(props.activeTab || 'expenses');
const selectedMonth = ref(props.selectedMonth || new Date().toISOString().slice(0, 7));
const filters = ref(props.filters || {
    start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
    end_date: new Date().toISOString().slice(0, 10),
});

const switchTab = (tab: string) => {
    activeTab.value = tab;
    router.get((window as any).route('financial.index'), { tab }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
