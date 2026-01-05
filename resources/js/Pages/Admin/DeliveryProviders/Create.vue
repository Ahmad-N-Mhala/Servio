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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('common.description') }}</label>
                                    <textarea 
                                        v-model="form.description" 
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Brief description of the delivery provider..."
                                    ></textarea>
                                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                                </div>

                                    <div class="mt-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Logo Image
                                        </label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors bg-gray-50 hover:bg-white">
                                            <div class="space-y-1 text-center">
                                                <svg v-if="!logoPreview" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <img v-else :src="logoPreview" class="mx-auto h-24 w-24 object-contain rounded-lg shadow-sm" />
                                                
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                        <span>Upload a file</span>
                                                        <input type="file" class="sr-only" accept="image/*" @change="handleLogoChange">
                                                    </label>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500">
                                                    PNG, JPG, GIF up to 2MB
                                                </p>
                                            </div>
                                        </div>
                                        <p v-if="errors.logo" class="mt-1 text-sm text-red-600">{{ errors.logo }}</p>
                                    </div>

                                    <div class="mt-6">
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
                            
                            <!-- Integration Settings (Orders & Webhooks) -->
                            <div class="pt-6 border-t border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    Order Reception & Webhooks
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Webhook URL Template
                                        </label>
                                        <input 
                                            v-model="form.webhook_url_template" 
                                            type="text" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-gray-50"
                                            placeholder="https://api.restofy.com/webhooks/delivery/{provider}/{store_id}"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">
                                            This is the callback URL we provide to the delivery platform to receive orders.
                                        </p>
                                        <p v-if="errors.webhook_url_template" class="mt-1 text-sm text-red-600">{{ errors.webhook_url_template }}</p>
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
                                    <!-- Store ID -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition-all" :class="{ 'border-green-500 bg-green-50': form.requires_store_id }">
                                            <input v-model="form.requires_store_id" type="checkbox" class="w-5 h-5 text-green-600 rounded focus:ring-green-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Store ID</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The specific ID for this restaurant branch on the delivery platform.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- API Key -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-all" :class="{ 'border-blue-500 bg-blue-50': form.requires_api_key }">
                                            <input v-model="form.requires_api_key" type="checkbox" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires API Key</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The unique access token or key provided by the delivery platform.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- API Secret -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition-all" :class="{ 'border-indigo-500 bg-indigo-50': form.requires_api_secret }">
                                            <input v-model="form.requires_api_secret" type="checkbox" class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires API Secret</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The secret key used to sign requests or authenticate (often paired with API Key).
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- Client ID (OAuth) -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition-all" :class="{ 'border-purple-500 bg-purple-50': form.requires_client_id }">
                                            <input v-model="form.requires_client_id" type="checkbox" class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Client ID</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The OAuth Client ID used to generate access tokens (common for UberEats).
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- Client Secret (OAuth) -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-500 transition-all" :class="{ 'border-pink-500 bg-pink-50': form.requires_client_secret }">
                                            <input v-model="form.requires_client_secret" type="checkbox" class="w-5 h-5 text-pink-600 rounded focus:ring-pink-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Client Secret</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The OAuth Client Secret used to authenticate for tokens.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- Username -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-cyan-500 transition-all" :class="{ 'border-cyan-500 bg-cyan-50': form.requires_username }">
                                            <input v-model="form.requires_username" type="checkbox" class="w-5 h-5 text-cyan-600 rounded focus:ring-cyan-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Username</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            Login username for the platform's API (if required).
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition-all" :class="{ 'border-red-500 bg-red-50': form.requires_password }">
                                            <input v-model="form.requires_password" type="checkbox" class="w-5 h-5 text-red-600 rounded focus:ring-red-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Password</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            Login password for authentication.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>

                                    <!-- Webhook Secret -->
                                    <div class="relative group">
                                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition-all" :class="{ 'border-orange-500 bg-orange-50': form.requires_webhook_secret }">
                                            <input v-model="form.requires_webhook_secret" type="checkbox" class="w-5 h-5 text-orange-600 rounded focus:ring-orange-500" />
                                            <span class="ml-3 text-sm font-medium text-gray-900">Requires Webhook Secret</span>
                                            <div class="ml-auto text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </label>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 text-center">
                                            The secret used to verify that incoming orders are genuinely from the provider.
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end gap-4">
                            <Link 
                                :href="route('admin.delivery-providers.index')" 
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition-colors"
                            >{{ $t('common.cancel') }}</Link>
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
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const route = (window as any).route;

const form = useForm({
    name: '',
    slug: '',
    description: '',
    logo: null as File | null,
    api_documentation_url: '',
    requires_api_key: false,
    requires_api_secret: false,
    requires_client_id: false,
    requires_client_secret: false,
    requires_username: false,
    requires_password: false,
    requires_store_id: true,
    requires_webhook_secret: false,
    webhook_url_template: '',
    is_active: true,
    sort_order: 0,
});
// ... (rest of script is same)

const logoPreview = ref<string | null>(null);

const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    
    // Using useForm submit, which handles basic uploading nicely, or manual post
    // Inertia's useForm automatically handles FormData if it detects files.
    
    form.post(route('admin.delivery-providers.store'), {
        forceFormData: true,
        onError: (err) => {
             // Cast to generic object to fix TS
            errors.value = err as any;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>
