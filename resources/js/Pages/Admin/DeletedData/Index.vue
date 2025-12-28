<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Deleted Data Management</h1>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'users'"
                        :class="[
                            activeTab === 'users' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Deleted Users
                    </button>
                    <button 
                        @click="activeTab = 'restaurants'"
                        :class="[
                            activeTab === 'restaurants' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Deleted Restaurants
                    </button>
                </nav>
            </div>

            <!-- Users Table -->
            <div v-if="activeTab === 'users'">
                <Table 
                    :columns="userColumns"
                    :data="deletedUsers.data"
                    :pagination="deletedUsers"
                    title="Deleted Users"
                    :search-enabled="false"
                >
                    <template #cell-name="{ row }">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900">{{ row.name }}</span>
                            <span class="text-xs text-gray-500">{{ row.email }}</span>
                        </div>
                    </template>

                    <template #cell-deleted_at="{ row }">
                        <span class="text-sm text-gray-600">
                            {{ new Date(row.deleted_at).toLocaleString() }}
                        </span>
                    </template>

                    <template #cell-deleted_by="{ row }">
                        <div class="text-sm">
                            <div v-if="row.deleted_by_name" class="font-medium text-gray-900">
                                {{ row.deleted_by_name }}
                            </div>
                            <div v-if="row.deleted_by" class="text-xs text-gray-500">
                                {{ row.deleted_by }}
                            </div>
                            <span v-else class="text-xs text-gray-400 italic">Unknown</span>
                        </div>
                    </template>

                    <template #cell-actions="{ row }">
                        <button 
                            @click="restoreUser(row)" 
                            class="text-indigo-600 hover:text-indigo-900 font-medium text-sm"
                        >
                            Restore
                        </button>
                    </template>
                </Table>
            </div>

            <!-- Restaurants Table -->
            <div v-if="activeTab === 'restaurants'">
                 <Table 
                    :columns="restaurantColumns"
                    :data="deletedRestaurants.data"
                    :pagination="deletedRestaurants"
                    title="Deleted Restaurants"
                    :search-enabled="false"
                >
                    <template #cell-name="{ row }">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900">{{ row.name }}</span>
                            <span class="text-xs text-gray-500">{{ row.email }}</span>
                        </div>
                    </template>

                    <template #cell-deleted_at="{ row }">
                        <span class="text-sm text-gray-600">
                            {{ new Date(row.deleted_at).toLocaleString() }}
                        </span>
                    </template>

                    <template #cell-deleted_by="{ row }">
                        <div class="text-sm">
                            <div v-if="row.deleted_by_name" class="font-medium text-gray-900">
                                {{ row.deleted_by_name }}
                            </div>
                             <div v-if="row.deleted_by" class="text-xs text-gray-500">
                                {{ row.deleted_by }}
                            </div>
                             <span v-else class="text-xs text-gray-400 italic">Unknown</span>
                        </div>
                    </template>

                    <template #cell-actions="{ row }">
                         <button 
                            @click="restoreRestaurant(row)" 
                            class="text-indigo-600 hover:text-indigo-900 font-medium text-sm"
                        >
                            Restore
                        </button>
                    </template>
                </Table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Table from '@/Components/Table.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    deletedUsers: {
        data: any[];
        links: any[];
    };
    deletedRestaurants: {
        data: any[];
        links: any[];
    };
}>();

const activeTab = ref('users');

const userColumns = [
    { key: 'name', label: 'User Details', sortable: false },
    { key: 'deleted_at', label: 'Deleted At', sortable: true },
    { key: 'deleted_by', label: 'Deleted By', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, align: 'right' as const },
];

const restaurantColumns = [
    { key: 'name', label: 'Restaurant Details', sortable: false },
    { key: 'deleted_at', label: 'Deleted At', sortable: true },
    { key: 'deleted_by', label: 'Deleted By', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, align: 'right' as const },
];

const restoreUser = (user: any) => {
    if (confirm(`Are you sure you want to restore user "${user.name}"?`)) {
        router.post(route('admin.users.restore', user.id));
    }
};

const restoreRestaurant = (restaurant: any) => {
    if (confirm(`Are you sure you want to restore restaurant "${restaurant.name}"?`)) {
        router.post(route('admin.restaurants.restore', restaurant.id));
    }
};

const route = (window as any).route;
</script>
