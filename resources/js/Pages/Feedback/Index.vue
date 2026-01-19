<template>
    <MainLayout>
        <Head title="Customer Feedback" />
        
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t('feedback.title') }}
                </h2>
                
                <div class="flex items-center gap-3" v-if="restaurant?.slug">
                    <a 
                        :href="route('public.feedback.create', restaurant.slug)" 
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        {{ $t('feedback.test_form') || 'Test Feedback Form' }}
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Tabs -->
                <div class="flex space-x-4 mb-6 border-b border-gray-200">
                    <button 
                        @click="currentTab = 'list'"
                        class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="currentTab === 'list' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        {{ $t('feedback.responses') }}
                    </button>
                    <button 
                         @click="currentTab = 'design'"
                        class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="currentTab === 'design' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        {{ $t('feedback.design') }}
                    </button>
                </div>

                <!-- Tab Content -->
                <div v-if="currentTab === 'list'">
                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <Input 
                                    v-model="filters.search" 
                                    :placeholder="$t('feedback.search_placeholder') || 'Search by customer name, comment, or order number...'" 
                                    type="search"
                                />
                            </div>
                            <div class="w-full sm:w-60">
                                <Select 
                                    v-model="filters.rating" 
                                    :options="ratingOptions"
                                />
                            </div>
                            <div class="w-full sm:w-auto">
                                <DateRangePicker 
                                    :initial-start-date="filters.date_from"
                                    :initial-end-date="filters.date_to"
                                    @update="onDateRangeUpdate"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <Table 
                            :columns="columns" 
                            :data="feedback.data"
                            :pagination="feedback"
                            :title="$t('feedback.responses')"
                            :empty-message="$t('feedback.no_feedback') || 'No feedback received yet.'"
                        >
                         <!-- Custom Cell Rendering -->
                         <template #cell-rating="{ row }">
                            <div class="flex text-yellow-400">
                                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= row.rating ? 'fill-current' : 'text-gray-300'" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363 1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                        </template>

                        <template #cell-comment="{ row }">
                            <p class="text-sm text-gray-600 max-w-xs truncate" :title="row.comment">{{ row.comment || '-' }}</p>
                        </template>

                        <template #cell-customer="{ row }">
                             <div v-if="row.customer">
                                <div class="font-medium text-gray-900">{{ row.customer.name }}</div>
                                <div class="text-xs text-gray-500">{{ row.customer.phone }}</div>
                             </div>
                             <span v-else class="text-gray-400 italic">{{ $t('common.guest') }}</span>
                        </template>

                         <template #cell-order="{ row }">
                             <span v-if="row.order" class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">#{{ row.order.order_number }}</span>
                             <span v-else class="text-gray-400">-</span>
                        </template>

                        <template #cell-created_at="{ row }">
                            {{ new Date(row.created_at).toLocaleDateString() }} 
                            <span class="text-gray-400 text-xs">{{ new Date(row.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                        </template>

                        <template #cell-redirected_to_google="{ row }">
                            <span v-if="row.redirected_to_google" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $t('common.yes') }}
                            </span>
                            <span v-else class="text-gray-400 text-xs">-</span>
                        </template>
                    </Table>
                </div>
            </div>

            <div v-else-if="currentTab === 'design'">
                <Design :settings="settings" :restaurant="restaurant" />
            </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import Table from '@/Components/Table.vue';
import Design from './Design.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, reactive, watch, computed } from 'vue';

const { t } = useI18n();
const props = defineProps<{
    feedback: any;
    settings?: Record<string, any>;
    restaurant?: { id: string; name: string; slug: string; logo?: string } | null;
    filters?: {
        search: string;
        rating: string;
        date_from: string;
        date_to: string;
    };
}>();

const currentTab = ref('list');

const filters = reactive({
    search: props.filters?.search || '',
    rating: props.filters?.rating || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

// Debounce timer
let searchTimeout: ReturnType<typeof setTimeout>;

// Watch filters and trigger search
watch(filters, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500); // 500ms debounce
});

const onDateRangeUpdate = ({ startDate, endDate }: { startDate: string; endDate: string }) => {
    filters.date_from = startDate;
    filters.date_to = endDate;
};

const applyFilters = () => {
    const params: Record<string, any> = {};
    
    if (filters.search) params.search = filters.search;
    if (filters.rating) params.rating = filters.rating;
    if (filters.date_from) params.date_from = filters.date_from;
    if (filters.date_to) params.date_to = filters.date_to;
    
    router.get(route('feedback.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const columns = computed(() => [
    { key: 'created_at', label: t('common.date') || 'Received' },
    { key: 'rating', label: t('feedback.rating') || 'Rating' },
    { key: 'comment', label: t('feedback.comment') || 'Comment' },
    { key: 'customer', label: t('customers.title') || 'Customer' },
    { key: 'order', label: t('nav.orders') || 'Order' },
    { key: 'redirected_to_google', label: t('feedback.google_redirect') || 'Google Redirect' },
]);

const ratingOptions = computed(() => [
    { label: t('feedback.all_ratings') || 'All Ratings', value: '' },
    { label: '⭐⭐⭐⭐⭐ (5 ' + (t('feedback.stars') || 'stars') + ')', value: '5' },
    { label: '⭐⭐⭐⭐ (4 ' + (t('feedback.stars') || 'stars') + ')', value: '4' },
    { label: '⭐⭐⭐ (3 ' + (t('feedback.stars') || 'stars') + ')', value: '3' },
    { label: '⭐⭐ (2 ' + (t('feedback.stars') || 'stars') + ')', value: '2' },
    { label: '⭐ (1 ' + (t('feedback.stars') || 'stars') + ')', value: '1' }
]);

const route = (window as any).route;
</script>
