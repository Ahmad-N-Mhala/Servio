<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Delivery Integrations
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <Table
                    :columns="columns"
                    :data="integrations.data"
                    :pagination="integrations"
                    title="Delivery Integrations"
                    v-model:search="search"
                    @sort="handleSort"
                    empty-message="No delivery providers found. Add providers like Noon, Kareem, UberEats!"
                >
                    <template #header-actions>
                        <Link 
                            :href="route('admin.integrations.create')" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Integration
                        </Link>
                    </template>

                    <!-- Restaurant Column -->
                    <template #cell-restaurant="{ row }">
                        <div class="text-sm font-medium text-gray-900">{{ row.restaurant?.name || 'N/A' }}</div>
                    </template>

                    <!-- API Key Column -->
                    <template #cell-api_key="{ row }">
                         <div class="text-sm text-gray-500">{{ row.api_key ? '••••••••' : 'Not set' }}</div>
                    </template>

                    <!-- Status Column -->
                    <template #cell-is_enabled="{ row }">
                        <span :class="[
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                            row.is_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                            {{ row.is_enabled ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <!-- Actions -->
                    <template #actions="{ row }">
                        <Link 
                            :href="route('admin.integrations.edit', row.id)" 
                            class="text-primary hover:text-primary/80"
                        >
                            Edit
                        </Link>
                        <button 
                            @click="deleteIntegration(row.id)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Delete
                        </button>
                    </template>
                </Table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Table from '@/Components/Table.vue';
// @ts-ignore
import debounce from 'lodash/debounce';

const props = defineProps<{
    integrations: {
        data: Array<any>;
        from: number;
        to: number;
        total: number;
        links: Array<any>;
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search || '');

const columns = [
    { key: 'restaurant', label: 'Restaurant', sortable: false }, // Sorting via relationship needs backend logic, kept false for now or true if implemented
    { key: 'provider', label: 'Provider', sortable: true },
    { key: 'api_key', label: 'API Key', sortable: false },
    { key: 'is_enabled', label: 'Status', sortable: true },
];

const handleSort = (key: string, direction: string) => {
    router.get(
        route('admin.integrations.index'),
        { 
            search: search.value,
            sort_field: key,
            sort_direction: direction
        },
        { preserveState: true, replace: true }
    );
};

watch(search, debounce((value: string) => {
    router.get(route('admin.integrations.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const route = (window as any).route;

const deleteIntegration = (id: number) => {
    if (confirm('Are you sure you want to delete this integration?')) {
        router.delete(route('admin.integrations.destroy', id));
    }
};
</script>
