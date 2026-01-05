<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Delivery Integrations</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Connect your restaurant with popular delivery platforms in UAE.</p>
            </div>


            <!-- Connected Integrations -->
            <div v-if="connectedProviders.length > 0" class="mb-12">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Connected Integrations</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="provider in connectedProviders" :key="provider.id" class="glass-card p-6 rounded-2xl relative overflow-hidden transition-all duration-300 hover:shadow-lg border border-green-200 dark:border-green-900 bg-green-50/30 dark:bg-green-900/10">
                         <!-- Unified Provider Card content to avoid duplication, we will use a template or component concept if this was larger, but copy-paste logic for now since we are splitting the loop -->
                         
                        <!-- Status Indicator -->
                        <div class="absolute top-4 right-4">
                            <span 
                                v-if="!provider.integration_status || provider.integration_status === 'success'"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                            >
                                Connected
                            </span>
                             <div  
                                v-else 
                                class="group relative inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 cursor-help"
                            >
                                Integration Error
                                <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg z-20">
                                    {{ provider.integration_error_message || 'Unknown configuration error' }}
                                    <div class="absolute top-full right-8 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center p-2 overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm">
                                <img 
                                    v-if="provider.logo_url" 
                                    :src="provider.logo_url" 
                                    :alt="provider.name" 
                                    class="w-full h-full object-contain"
                                    @error="(e) => (e.target as HTMLImageElement).src = '/images/placeholder-logo.svg'" 
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-primary/10 text-primary font-bold text-xs rounded">
                                    {{ provider.name.substring(0, 2).toUpperCase() }}
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ provider.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ provider.description }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                     <button @click="editIntegration(provider.id)" class="text-xs font-medium text-primary hover:text-primary-dark hover:underline">
                                        Configure Settings
                                    </button>
                                     <a v-if="provider.api_documentation_url" :href="provider.api_documentation_url" target="_blank" class="text-xs text-gray-400 hover:text-gray-600 hover:underline">Docs &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Settings Form (Only shown if active) -->
                        <div v-if="activeProvider === provider.id" class="space-y-4 animate-fade-in border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
                            <!-- Helper Text -->
                             <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 p-3 rounded-lg text-xs" v-if="provider.webhook_url_template">
                                <p class="font-bold mb-1">Webhook Configuration:</p>
                                <p>Copy this URL to your {{ provider.name }} dashboard settings.</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <code class="bg-white dark:bg-gray-900 px-2 py-1 rounded border border-blue-100 dark:border-blue-800 break-all select-all">{{ generateWebhookUrl(provider) }}</code>
                                </div>
                            </div>

                             <!-- Dynamic Fields based on Provider Requirements -->
                            <div class="space-y-4">
                                <!-- Store ID -->
                                <div v-if="provider.requires_store_id">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store ID</label>
                                    <Input v-model="forms[provider.id].store_id" :error="forms[provider.id].errors.store_id" type="text" placeholder="e.g. 15482" />
                                </div>
                                
                                <!-- API Key -->
                                <div v-if="provider.requires_api_key">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key / Token</label>
                                    <Input v-model="forms[provider.id].api_key" :error="forms[provider.id].errors.api_key" type="password" placeholder="****************" />
                                </div>

                                 <!-- API Secret -->
                                 <div v-if="provider.requires_api_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret</label>
                                    <Input v-model="forms[provider.id].api_secret" :error="forms[provider.id].errors.api_secret" type="password" placeholder="****************" />
                                </div>
                                
                                <!-- Other fields... (simplified for brevity, assume similar structure as original loop) -->
                                <!-- For simplicity in this edit, I'm including the key fields. The user has full form in original, preserving checks. -->
                                <div v-if="provider.requires_client_id">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client ID</label>
                                    <Input v-model="forms[provider.id].client_id" :error="forms[provider.id].errors.client_id" type="text" />
                                </div>
                                 <div v-if="provider.requires_client_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Secret</label>
                                    <Input v-model="forms[provider.id].client_secret" :error="forms[provider.id].errors.client_secret" type="password" />
                                </div>
                                 <div v-if="provider.requires_username">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                    <Input v-model="forms[provider.id].username" :error="forms[provider.id].errors.username" type="text" />
                                </div>
                                 <div v-if="provider.requires_password">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                    <Input v-model="forms[provider.id].password" :error="forms[provider.id].errors.password" type="password" />
                                </div>
                                <div v-if="provider.requires_webhook_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                                    <Input v-model="forms[provider.id].webhook_secret" :error="forms[provider.id].errors.webhook_secret" type="password" />
                                </div>


                                 <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <input 
                                        type="checkbox" 
                                        :id="`enable-${provider.id}`"
                                        v-model="forms[provider.id].is_enabled"
                                        class="rounded border-gray-300 text-primary focus:ring-primary w-5 h-5"
                                    >
                                    <label :for="`enable-${provider.id}`" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer select-none">Enable Integration</label>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 pt-4">
                                <Button 
                                    @click="saveIntegration(provider.id)" 
                                    :loading="forms[provider.id].processing"
                                    variant="primary"
                                    class="w-full justify-center"
                                >
                                    Update Configuration
                                </Button>
                                 <Button 
                                    @click="disconnectIntegration(provider.id)" 
                                    :loading="disconnecting === provider.id"
                                    variant="outline"
                                    class="w-full justify-center border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300"
                                >
                                    Disconnect
                                </Button>
                            </div>
                            
                             <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">{{ $t('common.actions') }}</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <Button 
                                        @click="pushMenu(provider.id)"
                                        :loading="pushingMenu === provider.id"
                                        variant="outline"
                                        size="sm"
                                        class="w-full justify-center text-xs"
                                    >
                                        <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        Force Push Menu
                                    </Button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 text-center" v-if="getIntegration(provider.id).last_menu_push_at">
                                    Last synced: {{ new Date(getIntegration(provider.id).last_menu_push_at).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Integrations -->
            <div>
                 <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Available Integrations</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="provider in availableProviders" :key="provider.id" class="glass-card p-6 rounded-2xl relative overflow-hidden transition-all duration-300 hover:shadow-lg border border-gray-100 dark:border-gray-700 opacity-90 hover:opacity-100">
                         <!-- Similar Card Structure for Available -->
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-16 h-16 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-2 overflow-hidden border border-gray-100 dark:border-gray-700">
                                <img 
                                    v-if="provider.logo_url" 
                                    :src="provider.logo_url" 
                                    :alt="provider.name" 
                                    class="w-full h-full object-contain grayscale hover:grayscale-0 transition-all duration-300"
                                    @error="(e) => (e.target as HTMLImageElement).src = '/images/placeholder-logo.svg'"
                                />
                                 <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold text-xs rounded uppercase">
                                    {{ provider.name.substring(0, 2) }}
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ provider.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ provider.description }}</p>
                                <button @click="editIntegration(provider.id)" class="mt-3 text-sm font-medium text-primary hover:text-primary-dark flex items-center gap-1 group">
                                    Connect Now 
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </button>
                            </div>
                        </div>

                         <!-- Form Container for Connecting (Hidden by default) -->
                         <div v-if="activeProvider === provider.id" class="space-y-4 animate-fade-in border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
                            <!-- Helper Text -->
                             <div class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 p-3 rounded-lg text-xs mb-4">
                                <p class="font-bold">Setup Instructions:</p>
                                <p>Enter your API credentials provided by {{ provider.name }}. You may need to request these from their partner portal.</p>
                            </div>

                             <!-- Webhook URL Display (Even before connecting, shows what it WILL be) -->
                             <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg text-xs mb-4" v-if="provider.webhook_url_template">
                                <p class="font-bold mb-1 text-gray-700 dark:text-gray-300">Your Webhook URL:</p>
                                <code class="text-gray-500 break-all select-all">{{ generateWebhookUrl(provider) }}</code>
                            </div>

                             <!-- Input Fields (Identical to above but for 'NEW' connection context) -->
                            <div class="space-y-4">
                                 <!-- Store ID -->
                                <div v-if="provider.requires_store_id">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store ID <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].store_id" :error="forms[provider.id].errors.store_id" type="text" placeholder="e.g. 15482" />
                                </div>
                                <div v-if="provider.requires_api_key">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key / Token <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].api_key" :error="forms[provider.id].errors.api_key" type="password" />
                                </div>
                                <!-- Add other fields as necessary... using same v-if logic -->
                                 <div v-if="provider.requires_api_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].api_secret" :error="forms[provider.id].errors.api_secret" type="password" />
                                </div>
                                <div v-if="provider.requires_client_id">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client ID <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].client_id" :error="forms[provider.id].errors.client_id" type="text" />
                                </div>
                                <div v-if="provider.requires_client_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Secret <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].client_secret" :error="forms[provider.id].errors.client_secret" type="password" />
                                </div>
                                <div v-if="provider.requires_username">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].username" :error="forms[provider.id].errors.username" type="text" />
                                </div>
                                <div v-if="provider.requires_password">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
                                    <Input v-model="forms[provider.id].password" :error="forms[provider.id].errors.password" type="password" />
                                </div>
                                <div v-if="provider.requires_webhook_secret">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                                    <Input v-model="forms[provider.id].webhook_secret" :error="forms[provider.id].errors.webhook_secret" type="password" />
                                </div>

                                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <input 
                                        type="checkbox" 
                                        :id="`enable-new-${provider.id}`"
                                        v-model="forms[provider.id].is_enabled"
                                        class="rounded border-gray-300 text-primary focus:ring-primary w-5 h-5"
                                    >
                                    <label :for="`enable-new-${provider.id}`" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer select-none">Enable immediately</label>
                                </div>
                            </div>

                            <div class="pt-4">
                                <Button 
                                    @click="saveIntegration(provider.id)" 
                                    :loading="forms[provider.id].processing"
                                    variant="primary"
                                    class="w-full justify-center"
                                >
                                    Save & Connect
                                </Button>
                            </div>
                         </div>
                    </div>
                </div>
            </div>



            <!-- Integration Guide / Help -->
            <div class="mt-12 bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-6 border border-blue-100 dark:border-blue-900/20">
                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">How to Integrate?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">1. Get Credentials</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Login to your delivery partner dashboard (e.g. Noon Food Partner Portal) and navigate to API settings to generate your credentials.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">2. Enter Details</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Copy the credentials (API Key, Store ID, etc.) and paste them into the respective fields above. Toggle "Enable Integration" and save.</p>
                    </div>
                     <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">3. Webhooks Config</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Copy the specialized Webhook URL shown in the provider card after you click "Connect" and add it to their system to receive orders.</p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const props = defineProps<{
    providers: any[];
    integrations: Record<string, any>;
}>();

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
const route = (window as any).route;

const activeProvider = ref<string | null>(null);

// Initialize forms for each provider
const forms = ref<Record<string, any>>({});

props.providers.forEach(p => {
    const existing = props.integrations[p.id] || {};
    forms.value[p.id] = useForm({
        provider: p.id,
        store_id: existing.store_id || '',
        api_key: existing.api_key || '',
        api_secret: existing.api_secret || '',
        client_id: existing.client_id || '',
        client_secret: existing.client_secret || '',
        username: existing.username || '',
        password: existing.password || '',
        webhook_secret: existing.webhook_secret || '',
        is_enabled: existing.is_enabled || false,
    });
});

const isIntegrated = (providerId: string) => {
    return !!props.integrations[providerId];
};

const getIntegration = (providerId: string) => {
    return props.integrations[providerId] || {};
};

const editIntegration = (providerId: string) => {
    activeProvider.value = providerId;
};

const saveIntegration = (providerId: string) => {
    // Post to update route
    forms.value[providerId].post(route('integrations.delivery.update'), {
        preserveScroll: true,
        onSuccess: () => {
            activeProvider.value = null;
        }
    });
};

const generateWebhookUrl = (provider: any) => {
    // Generate a webhook URL based on the template. 
    // We replace {provider} with slug and {store_id} locally for display.
    
    let template = provider.webhook_url_template as string;
    const origin = window.location.origin;

    // Default fallback if no template provided
    if (!template) {
         const storeIdPart = forms.value[provider.id]?.store_id ? `?store_id=${forms.value[provider.id].store_id}` : '';
         return `${origin}/api/webhook/delivery/${provider.slug}${storeIdPart}`;
    }

    // Check if template is absolute (starts with http)
    const isAbsolute = template.startsWith('http');
    
    // If not absolute, prepend origin
    let url = isAbsolute ? template : `${origin}${template}`;

    // Replace {provider} placeholder
    url = url.replace('{provider}', provider.slug);
    
    // Replace {store_id} placeholder with actual value from form or keep placeholder if empty
    const enteredStoreId = forms.value[provider.id]?.store_id;
    if (url.includes('{store_id}')) {
        url = url.replace('{store_id}', enteredStoreId || 'YOUR_STORE_ID');
    } else if (provider.requires_store_id && enteredStoreId) {
        // If template doesn't have placeholder but store_id is required/entered, append it as query param
        const separator = url.includes('?') ? '&' : '?';
        url = `${url}${separator}store_id=${enteredStoreId}`;
    }
    
    return url;
};

const pushingMenu = ref<string | null>(null);

const disconnecting = ref<string | null>(null);

const connectedProviders = computed(() => {
    return props.providers.filter(p => isIntegrated(p.id) && getIntegration(p.id).is_enabled);
});

const availableProviders = computed(() => {
    return props.providers.filter(p => !isIntegrated(p.id) || !getIntegration(p.id).is_enabled);
});

const pushMenu = (providerId: string) => {
    pushingMenu.value = providerId;
    
    // Use Inertia post
    router.post(route('integrations.delivery.push-menu', props.integrations[providerId].provider), {}, { // Use provider slug if stored, or just ID if they match
        preserveScroll: true,
        onSuccess: () => {
            pushingMenu.value = null;
        },
        onError: () => {
            pushingMenu.value = null;
        }
    });
};

const disconnectIntegration = (providerId: string) => {
    if (!confirm('Are you sure you want to disconnect this integration? Orders will no longer be synced.')) {
        return;
    }

    disconnecting.value = providerId;
    
    // We use router.delete since we are sending a DELETE request
    // Note: Inertia router visits are not easily cancellable or loading-state tracked per-se like forms,
    // so we manage local state.
    router.delete(route('integrations.delivery.destroy', providerId), {
        preserveScroll: true,
        onSuccess: () => {
            disconnecting.value = null;
            activeProvider.value = null;
            // Clear form
            forms.value[providerId].reset();
        },
        onError: () => {
             disconnecting.value = null;
        }
    });
};

const getLastPush = (providerId: string) => {
    const integration = getIntegration(providerId);
    if (integration.settings && integration.settings.last_menu_push) {
         return new Date(integration.settings.last_menu_push).toLocaleString();
    }
    return null;
};

</script>
