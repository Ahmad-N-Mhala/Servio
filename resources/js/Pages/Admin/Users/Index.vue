<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
                <Button @click="exportUsers" variant="outline" size="sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </Button>
            </div>

            <Table 
                :columns="columns"
                :data="users.data"
                :pagination="users"
                v-model:search="search"
                title="All Users"
            >
                <template #cell-id="{ value }">
                    <span class="font-mono text-xs text-gray-500" :title="value">{{ value }}</span>
                </template>

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

                <template #cell-roles_list="{ row }">
                     <div class="flex flex-wrap gap-1">
                         <span 
                             v-for="role in (row.roles_list ? row.roles_list.split(', ') : [])" 
                             :key="role"
                             class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize"
                         >
                             {{ role }}
                         </span>
                         <span v-if="!row.roles_list" class="text-xs text-gray-400">-</span>
                     </div>
                </template>

                <template #cell-restaurant_names="{ row }">
                     <span class="text-sm text-gray-600">{{ row.restaurant_names || 'None' }}</span>
                </template>

                <template #cell-last_login_at="{ value }">
                    <span class="text-sm text-gray-500">{{ value ? new Date(value).toLocaleDateString() + ' ' + new Date(value).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Never' }}</span>
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
import Button from '@/Components/Button.vue';
import { debounce } from 'lodash';

const columns = [
    { key: 'id', label: 'User ID', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'roles_list', label: 'Roles', sortable: false },
    { key: 'restaurant_names', label: 'Restaurants', sortable: false },
    { key: 'last_login_at', label: 'Last Login', sortable: true },
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

const exportUsers = () => {
    window.location.href = route('admin.users.index', { export: true, search: search.value });
};
</script>
