<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Delivery Provider
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link 
                        :href="route('admin.delivery-providers.index')" 
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Providers
                    </Link>
                    <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Add New Delivery Provider
                    </h3>
                    <p class="mt-2 text-gray-600">
                        Configure a new delivery platform for restaurants to integrate with
                    </p>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <form @submit.prevent="submit">
                        <div class="p-8 space-y-6">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    Basic Information
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Provider Name <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            v-model="form.name" 
                                            type="text" 
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="e.g., Talabat, Noon Food"
                                        />
                                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Slug
                                        </label>
                                        <input 
                                            v-model="form.slug" 
                                            type="text" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="Auto-generated from name"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate</p>
                                        <p v-if="errors.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea 
                                        v-model="form.description" 
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Brief description of the delivery provider..."
                                    ></textarea>
                                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Logo URL
                                        </label>
                                        <input 
                                            v-model="form.logo_url" 
                                            type="url" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="https://example.com/logo.png"
                                        />
                                        <p v-if="errors.logo_url" class="mt-1 text-sm text-red-600">{{ errors.logo_url }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            API Documentation URL
                                        </label>
                                        <input 
                                            v-model="form.api_documentation_url" 
                                            type="url" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="https://developers.example.com"
                                        />
                                        <p v-if="errors.api_documentation_url" class="mt-1 text-sm text-red-600">{{ errors.api_documentation_url }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuration Requirements -->
                            <div class="pt-6 border-t border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    Configuration Requirements
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-all" :class="{ 'border-blue-500 bg-blue-50': form.requires_api_key }">
                                        <input 
                                            v-model="form.requires_api_key" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                        />
                                        <span class="ml-3 text-sm font-medium text-gray-900">Requires API Key</span>
                                    </label>

                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition-all" :class="{ 'border-purple-500 bg-purple-50': form.requires_api_secret }">
                                        <input 
                                            v-model="form.requires_api_secret" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                                        />
                                        <span class="ml-3 text-sm font-medium text-gray-900">Requires API Secret</span>
                                    </label>

                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition-all" :class="{ 'border-green-500 bg-green-50': form.requires_store_id }">
                                        <input 
                                            v-model="form.requires_store_id" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-green-600 rounded focus:ring-green-500"
                                        />
                                        <span class="ml-3 text-sm font-medium text-gray-900">Requires Store ID</span>
                                    </label>

                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition-all" :class="{ 'border-orange-500 bg-orange-50': form.requires_webhook_secret }">
                                        <input 
                                            v-model="form.requires_webhook_secret" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-orange-600 rounded focus:ring-orange-500"
                                        />
                                        <span class="ml-3 text-sm font-medium text-gray-900">Requires Webhook Secret</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Status and Ordering -->
                            <div class="pt-6 border-t border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    Status & Display
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition-all" :class="{ 'border-green-500 bg-green-50': form.is_active }">
                                            <input 
                                                v-model="form.is_active" 
                                                type="checkbox" 
                                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500"
                                            />
                                            <div class="ml-3">
                                                <span class="text-sm font-medium text-gray-900">Active Provider</span>
                                                <p class="text-xs text-gray-500 mt-1">Available for restaurant integrations</p>
                                            </div>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Sort Order
                                        </label>
                                        <input 
                                            v-model.number="form.sort_order" 
                                            type="number" 
                                            min="0"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="0"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                        <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end gap-4">
                            <Link 
                                :href="route('admin.delivery-providers.index')" 
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition-colors"
                            >
                                Cancel
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="processing"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="processing">Creating...</span>
                                <span v-else>Create Provider</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const route = (window as any).route;

const form = ref({
    name: '',
    slug: '',
    description: '',
    logo_url: '',
    api_documentation_url: '',
    requires_api_key: true,
    requires_api_secret: true,
    requires_store_id: true,
    requires_webhook_secret: false,
    is_active: true,
    sort_order: 0,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    errors.value = {};

    router.post(route('admin.delivery-providers.store'), form.value, {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
