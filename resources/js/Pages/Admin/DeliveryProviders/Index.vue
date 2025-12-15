<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Delivery Provider Applications
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                Delivery Applications
                            </h3>
                            <p class="mt-2 text-gray-600">
                                Manage the list of delivery platforms that restaurants can integrate with
                            </p>
                        </div>
                        <Link 
                            :href="route('admin.delivery-providers.create')" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-wider hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Provider
                        </Link>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total Providers</p>
                                <p class="text-3xl font-bold mt-2">{{ providers.total || 0 }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Active Providers</p>
                                <p class="text-3xl font-bold mt-2">{{ activeCount }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Total Integrations</p>
                                <p class="text-3xl font-bold mt-2">{{ totalIntegrations }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Providers Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="provider in providers.data" 
                        :key="provider.id"
                        class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group"
                    >
                        <!-- Provider Header -->
                        <div class="relative h-32 bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                            <div v-if="provider.logo_url" class="h-16 w-16 bg-white rounded-lg shadow-md flex items-center justify-center p-2">
                                <img :src="provider.logo_url" :alt="provider.name" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div v-else class="h-16 w-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg shadow-md flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">{{ provider.name.charAt(0) }}</span>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span :class="[
                                    'px-3 py-1 text-xs font-semibold rounded-full',
                                    provider.is_active 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ provider.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <!-- Provider Content -->
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ provider.name }}</h4>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ provider.description || 'No description available' }}
                            </p>

                            <!-- Provider Stats -->
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-blue-600">{{ provider.integrations_count || 0 }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Integrations</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-purple-600">{{ provider.sort_order }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Sort Order</p>
                                </div>
                            </div>

                            <!-- Requirements -->
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Required Fields:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="provider.requires_api_key" class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">API Key</span>
                                    <span v-if="provider.requires_api_secret" class="px-2 py-1 bg-purple-50 text-purple-700 text-xs rounded">Secret</span>
                                    <span v-if="provider.requires_store_id" class="px-2 py-1 bg-green-50 text-green-700 text-xs rounded">Store ID</span>
                                    <span v-if="provider.requires_webhook_secret" class="px-2 py-1 bg-orange-50 text-orange-700 text-xs rounded">Webhook</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <Link 
                                    :href="route('admin.delivery-providers.edit', provider.id)"
                                    class="flex-1 text-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium text-sm"
                                >
                                    Edit
                                </Link>
                                <button 
                                    @click="toggleStatus(provider.id)"
                                    class="flex-1 px-4 py-2 rounded-lg transition-colors font-medium text-sm"
                                    :class="provider.is_active 
                                        ? 'bg-orange-50 text-orange-700 hover:bg-orange-100' 
                                        : 'bg-green-50 text-green-700 hover:bg-green-100'"
                                >
                                    {{ provider.is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button 
                                    @click="deleteProvider(provider.id)"
                                    class="px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors font-medium text-sm"
                                    :disabled="provider.integrations_count > 0"
                                    :class="{ 'opacity-50 cursor-not-allowed': provider.integrations_count > 0 }"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="providers.data.length === 0" class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No delivery providers yet</h3>
                    <p class="text-gray-600 mb-6">Get started by adding your first delivery provider application</p>
                    <Link 
                        :href="route('admin.delivery-providers.create')" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Your First Provider
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="providers.data.length > 0 && providers.links" class="mt-8 flex justify-center">
                    <div class="flex gap-2">
                        <Link 
                            v-for="link in providers.links" 
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                link.active 
                                    ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' 
                                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    providers: {
        data: Array<any>;
        total: number;
        from: number;
        to: number;
        links: Array<any>;
    };
}>();

const route = (window as any).route;

const activeCount = computed(() => {
    return props.providers.data.filter(p => p.is_active).length;
});

const totalIntegrations = computed(() => {
    return props.providers.data.reduce((sum, p) => sum + (p.integrations_count || 0), 0);
});

const toggleStatus = (id: number) => {
    router.post(route('admin.delivery-providers.toggle-status', id), {}, {
        preserveScroll: true,
    });
};

const deleteProvider = (id: number) => {
    if (confirm('Are you sure you want to delete this delivery provider? This action cannot be undone.')) {
        router.delete(route('admin.delivery-providers.destroy', id));
    }
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
