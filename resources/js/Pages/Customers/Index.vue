<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Customers</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all your customers including their name, contact details, and loyalty status.</p>
                </div>
            </div>

            <!-- Table -->
            <Table
                :columns="columns"
                :data="customers.data"
                :pagination="customers"
                v-model:search="search"
                title="All Customers"
            >
                <!-- Customer Column -->
                <template #cell-name="{ row }">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                {{ row.name ? row.name.charAt(0).toUpperCase() : '?' }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ row.name || 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">Joined {{ new Date(row.created_at).toLocaleDateString() }}</div>
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
                        {{ value || 0 }} Visits
                    </span>
                </template>

                <!-- Points Column -->
                <template #cell-loyalty_points.balance="{ value }">
                    {{ value || 0 }} pts
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <Link :href="route('customers.show', row.id)" class="text-primary hover:text-primary-hover font-semibold text-sm">
                        View Logs
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
import debounce from 'lodash/debounce';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const columns = computed(() => [
    { key: 'name', label: 'Customer', sortable: true }, // Slot cell-name
    { key: 'contact', label: 'Contact', sortable: false }, // Slot cell-contact
    { key: 'orders_count', label: 'Total Visits', sortable: true, align: 'center' as const },
    { key: 'loyalty_points.balance', label: 'Points', sortable: true }, // Slot cell-loyalty_points.balance
    { key: 'total_spent', label: 'Total Spent', sortable: true, format: 'currency' as const, currency: currency.value },
]);

const props = defineProps<{
    customers: any;
    filters: any;
}>();

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value }, { preserveState: true, replace: true });
}, 300));


const route = (window as any).route;
</script>
