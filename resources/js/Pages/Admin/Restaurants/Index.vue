<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Restaurants Management</h1>
                <button class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-hover shadow-lg shadow-primary/30">
                    Add New Restaurant
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Owner</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Plan</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="restaurant in restaurants.data" :key="restaurant.id" class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ restaurant.name }}</td>
                             <td class="px-6 py-4">{{ restaurant.owner?.name || 'No Owner' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Active</span>
                            </td>
                             <td class="px-6 py-4">Pro Plan</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                <button class="text-red-600 hover:text-red-900">Suspend</button>
                            </td>
                        </tr>
                        <tr v-if="restaurants.data.length === 0">
                             <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                No restaurants found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination (Basic Placeholder) -->
            <div v-if="restaurants.links && restaurants.links.length > 3" class="flex justify-center mt-4">
                <!-- Pagination links implementation would go here -->
                 <span class="text-xs text-gray-400">Pagination to be implemented</span>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    restaurants: {
        data: any[];
        links: any[]; // Pagination links
    };
}>();
</script>
