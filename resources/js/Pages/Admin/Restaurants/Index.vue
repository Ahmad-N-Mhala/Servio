<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Restaurants Management</h1>
            </div>

            <Table 
                :columns="columns"
                :data="restaurants.data"
                :pagination="restaurants"
                v-model:search="search"
                title="All Restaurants"
            >
                <!-- Header Actions -->
                <template #header-actions>
                    <Link :href="route('admin.restaurants.create')" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-hover shadow-lg shadow-primary/30 text-center whitespace-nowrap flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add New Restaurant
                    </Link>
                </template>

                <!-- Name & Owner Column -->
                <template #cell-name="{ row }">
                    <div>
                        <div class="font-medium text-gray-900 text-base">{{ row.name }}</div>
                        <button 
                            v-if="getOwner(row)" 
                            @click="openOwnerModal(getOwner(row))"
                            class="text-xs text-primary hover:text-primary-hover hover:underline mt-1 flex items-center gap-1 transition-colors"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ getOwner(row).name }}
                        </button>
                        <div v-else class="text-xs text-gray-400 mt-1 italic">No Owner Assigned</div>
                    </div>
                </template>

                <!-- Status Column -->
                <template #cell-status="{ row }">
                    <span 
                        class="px-3 py-1 rounded-full text-xs font-bold capitalize inline-flex items-center gap-1"
                        :class="{
                            'bg-green-100 text-green-700': row.status === 'active',
                            'bg-yellow-100 text-yellow-700': row.status === 'suspended',
                            'bg-red-100 text-red-700': row.status === 'deleted',
                            'bg-gray-100 text-gray-700': !row.status
                        }"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="{
                            'bg-green-500': row.status === 'active',
                            'bg-yellow-500': row.status === 'suspended',
                            'bg-red-500': row.status === 'deleted',
                            'bg-gray-500': !row.status
                        }"></span>
                        {{ row.status || 'Active' }}
                    </span>
                </template>
                
                <!-- Plan Column (Custom rendering if needed, or use default) -->
                <template #cell-subscription.plan.name="{ value }">
                     <span class="text-gray-700 font-medium bg-gray-100 px-2 py-1 rounded-md text-xs">{{ value || 'No Plan' }}</span>
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <Link :href="route('admin.restaurants.edit', row.id)" class="text-indigo-600 hover:text-indigo-900 p-2 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </Link>
                    <button @click="deleteRestaurant(row)" class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </template>
            </Table>
        </div>

        <!-- Owner Details Modal -->
        <Modal :show="showOwnerModal" @close="closeOwnerModal">
            <!-- Modal Content (unchanged) -->
            <div class="p-6">
                <!-- ... header ... -->
                <div class="flex justify-between items-start mb-6">
                     <h2 class="text-xl font-bold text-gray-900">Owner Details</h2>
                     <button @click="closeOwnerModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div v-if="selectedOwner" class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-2xl font-bold text-gray-500">
                            {{ selectedOwner.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ selectedOwner.name }}</h3>
                            <p class="text-sm text-gray-500">Restaurant Owner</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                             </div>
                             <div>
                                 <p class="text-xs text-gray-500 uppercase font-semibold">Email Address</p>
                                 <p class="text-sm font-medium text-gray-900">{{ selectedOwner.email }}</p>
                             </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                             </div>
                             <div>
                                 <p class="text-xs text-gray-500 uppercase font-semibold">Phone Number</p>
                                 <p class="text-sm font-medium text-gray-900">{{ selectedOwner.phone || 'Not Provided' }}</p>
                             </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button 
                        @click="closeOwnerModal"
                        class="bg-gray-900 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-800 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/Table.vue';
import { debounce } from 'lodash';

const columns = [
    { key: 'name', label: 'Name & Owner', sortable: true },
    { key: 'status', label: 'Status', sortable: true, align: 'center' as const },
    { key: 'subscription.plan.name', label: 'Plan', sortable: false },
];

const props = defineProps<{
    restaurants: {
        data: any[];
        links: any[]; // Pagination links
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');

watch(search, debounce((value: string) => {
    router.get(route('admin.restaurants.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const showOwnerModal = ref(false);
const selectedOwner = ref<any>(null);

const getOwner = (restaurant: any) => {
    if (Array.isArray(restaurant.owner)) {
        return restaurant.owner[0] || null;
    }
    return restaurant.owner || null;
};

const openOwnerModal = (owner: any) => {
    selectedOwner.value = owner;
    showOwnerModal.value = true;
};

const closeOwnerModal = () => {
    showOwnerModal.value = false;
    selectedOwner.value = null;
};

const deleteRestaurant = (restaurant: any) => {
    if (confirm('Are you sure you want to delete this restaurant? This action cannot be undone.')) {
        router.delete(route('admin.restaurants.destroy', restaurant.id));
    }
};

const route = (window as any).route;
</script>
