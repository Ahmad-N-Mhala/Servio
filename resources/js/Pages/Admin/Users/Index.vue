<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
            </div>

            <Table 
                :columns="columns"
                :data="users.data"
                :pagination="users"
                v-model:search="search"
                title="All Users"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                            {{ row.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ row.name }}</div>
                            <div class="text-xs text-gray-500" v-if="row.is_super_admin">Super Admin</div>
                        </div>
                    </div>
                </template>

                <template #cell-email="{ row }">
                     <div class="text-sm text-gray-600">{{ row.email }}</div>
                </template>

                 <template #cell-phone="{ row }">
                     <span class="text-sm text-gray-600">{{ row.phone || '-' }}</span>
                </template>

                <template #cell-restaurant_names="{ row }">
                     <span class="text-sm text-gray-600">{{ row.restaurant_names || 'None' }}</span>
                </template>

                <template #cell-created_at="{ value }">
                    <span class="text-sm text-gray-500">{{ new Date(value).toLocaleDateString() }}</span>
                </template>

            </Table>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import Table from '@/Components/Table.vue';
import { debounce } from 'lodash';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'restaurant_names', label: 'Restaurants', sortable: false },
    { key: 'created_at', label: 'Joined', sortable: true },
];

const props = defineProps<{
    users: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');

const handleSearch = debounce(() => {
    router.get(route('admin.users.index'), { 
        search: search.value,
    }, { preserveState: true, replace: true });
}, 300);

watch(search, handleSearch);

const route = (window as any).route;
</script>
