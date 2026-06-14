<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('nav.customers') }}</h1>
                    <p class="mt-2 text-sm text-gray-700">{{ $t('customers.subtitle') || 'A list of all your customers including their name, contact details, and loyalty status.' }}</p>
                </div>
            </div>

            <!-- Table -->
            <Table
                :columns="columns"
                :data="customers.data"
                :pagination="customers"
                v-model:search="search"
                :title="$t('customers.all_customers')"
            >
                <template #header-actions>
                    <Button @click="exportExcel" variant="secondary" class="font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ $t('common.export') || 'Export' }}
                    </Button>
                </template>
                <!-- Customer Column -->
                <template #cell-name="{ row }">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                {{ typeof row.name === 'string' ? row.name.charAt(0).toUpperCase() : (row.name?.[locale] ? row.name[locale].charAt(0).toUpperCase() : '?') }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ getLocaleName(row.name) }}</div>
                            <div class="text-sm text-gray-500">{{ $t('loyalty.member_since') }} {{ new Date(row.created_at).toLocaleDateString() }}</div>
                        </div>
                    </div>
                </template>

                <!-- Contact Column -->
                <template #cell-contact="{ row }">
                    <div class="text-sm text-gray-900">{{ row.phone }}</div>
                    <div class="text-sm text-gray-500">{{ row.email }}</div>
                </template>

                <!-- Orders Count / Visits Column -->
                <template #cell-orders_count="{ value }">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ value || 0 }} {{ $t('loyalty.total_orders') }}
                    </span>
                </template>

                <!-- Points Column -->
                <template #cell-loyalty_points.balance="{ value }">
                    {{ value || 0 }} pts
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <Link :href="route('customers.show', row.id)" class="text-primary hover:text-primary-hover font-semibold text-sm">
                        {{ $t('loyalty.view_details') }}
                    </Link>
                </template>
            </Table>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Table from '@/Components/Table.vue';
import Button from '@/Components/Button.vue';
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = computed(() => [
    { key: 'name', label: t('customers.customer_name') || 'Customer', sortable: true }, // Slot cell-name
    { key: 'contact', label: t('reports.method') || 'Contact', sortable: false }, // Slot cell-contact
    { key: 'orders_count', label: t('customers.total_orders') || 'Total Visits', sortable: true, align: 'center' as const },
    { key: 'loyalty_points.balance', label: t('loyalty.points') || 'Points', sortable: true }, // Slot cell-loyalty_points.balance
    { key: 'total_spent', label: t('customers.total_spent') || 'Total Spent', sortable: true, format: 'currency' as const, currency: currency.value },
]);

const props = defineProps<{
    customers: any;
    filters: any;
}>();

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const exportExcel = () => {
    window.location.href = route('customers.index', {
        search: search.value,
        export: 'excel'
    });
};


const { t, locale } = useI18n();
const route = (window as any).route;

const getLocaleName = (name: any) => {
    if (!name) return t('common.unknown');
    if (typeof name === 'string') return name;
    return name[locale.value] || Object.values(name)[0] || t('common.unknown');
};
</script>
