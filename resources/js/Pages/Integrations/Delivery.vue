<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Delivery Integrations</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Connect your restaurant with popular delivery platforms in UAE.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="provider in providers" :key="provider.id" class="glass-card p-6 rounded-2xl relative overflow-hidden transition-all duration-300 hover:shadow-lg border border-gray-100 dark:border-gray-700">
                    <!-- Status Indicator -->
                    <div class="absolute top-4 right-4">
                        <span 
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="isIntegrated(provider.id) && getIntegration(provider.id).is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        >
                            {{ isIntegrated(provider.id) && getIntegration(provider.id).is_enabled ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>

                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-2 overflow-hidden">
                            <img 
                                v-if="provider.logo_url" 
                                :src="provider.logo_url" 
                                :alt="provider.name" 
                                class="w-full h-full object-contain"
                            />
                            <span v-else class="text-xs font-bold text-gray-400">{{ provider.name }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ provider.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ provider.description }}</p>
                            <a v-if="provider.api_documentation_url" :href="provider.api_documentation_url" target="_blank" class="text-xs text-blue-600 hover:underline mt-2 inline-block">Developer Docs &rarr;</a>
                        </div>
                    </div>

                    <div v-if="activeProvider === provider.id" class="space-y-4 animate-fade-in border-t border-gray-100 dark:border-gray-700 pt-4">
                         <!-- Settings Form -->
                        <div class="space-y-4">
                            <!-- Store ID -->
                            <div v-if="provider.requires_store_id">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store ID <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].store_id" :error="forms[provider.id].errors.store_id" type="text" placeholder="e.g. 15482" />
                            </div>
                            
                            <!-- API Key -->
                            <div v-if="provider.requires_api_key">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key / Token <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].api_key" :error="forms[provider.id].errors.api_key" type="password" placeholder="****************" />
                            </div>

                             <!-- API Secret -->
                             <div v-if="provider.requires_api_secret">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].api_secret" :error="forms[provider.id].errors.api_secret" type="password" placeholder="****************" />
                            </div>

                            <!-- Client ID -->
                            <div v-if="provider.requires_client_id">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client ID <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].client_id" :error="forms[provider.id].errors.client_id" type="text" placeholder="OAuth Client ID" />
                            </div>

                            <!-- Client Secret -->
                            <div v-if="provider.requires_client_secret">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Secret <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].client_secret" :error="forms[provider.id].errors.client_secret" type="password" placeholder="OAuth Client Secret" />
                            </div>

                            <!-- Username -->
                            <div v-if="provider.requires_username">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].username" :error="forms[provider.id].errors.username" type="text" placeholder="Username" />
                            </div>

                            <!-- Password -->
                            <div v-if="provider.requires_password">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
                                <Input v-model="forms[provider.id].password" :error="forms[provider.id].errors.password" type="password" placeholder="Password" />
                            </div>

                             <!-- Webhook Secret -->
                             <div v-if="provider.requires_webhook_secret">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret / Signature Key</label>
                                <Input v-model="forms[provider.id].webhook_secret" :error="forms[provider.id].errors.webhook_secret" type="password" placeholder="Signature key for validation" />
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

                        <!-- Provider Specific Webhook URL -->
                        <div v-if="provider.webhook_url_template" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-xs">
                             <p class="font-semibold text-blue-800 dark:text-blue-200 mb-1">Your Webhook URL:</p>
                             <div class="font-mono bg-white dark:bg-gray-800 p-2 rounded border border-blue-100 dark:border-blue-800 break-all select-all text-gray-600 dark:text-gray-300">
                                 {{ generateWebhookUrl(provider) }}
                             </div>
                             <p class="mt-1 text-blue-700 dark:text-blue-300 opacity-80">Copy this URL to the provider's dashboard.</p>
                        </div>

                            <Button variant="primary" size="sm" @click="saveIntegration(provider.id)" :loading="forms[provider.id].processing">
                                Save Settings
                            </Button>
                            
                            <Button 
                                v-if="isIntegrated(provider.id)"
                                variant="danger" 
                                size="sm" 
                                @click="disconnectIntegration(provider.id)"
                                :loading="disconnecting === provider.id"
                            >
                                Disconnect
                            </Button>

                            <Button variant="ghost" size="sm" @click="activeProvider = null">
                                Cancel
                            </Button>
                        </div>

                    <div v-else class="mt-4">
                         <div class="flex justify-between items-center text-sm text-gray-500 mb-4 px-1">
                             <span v-if="provider.requires_store_id">• Requires Store ID</span>
                             <span v-if="provider.requires_api_key">• Requires API Key</span>
                         </div>
                         <Button variant="outline" class="w-full" @click="editIntegration(provider.id)">
                            {{ isIntegrated(provider.id) ? 'Manage Settings' : 'Connect' }}
                        </Button>
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
    // We replace {provider} with slug and {store_id} locally for display, though backend handles routing.
    // If template is https://api.restofy.com/webhooks/delivery/{provider}/{store_id}
    
    // We should use the actual URL origin
    const origin = window.location.origin;
    
    let template = provider.webhook_url_template as string;
    if (!template) return `${origin}/api/webhook/delivery/${provider.slug}`; // Fallback

    // Simple replacement if template has variables, otherwise just return it
    template = template.replace('{provider}', provider.slug);
    
    // If template requires store_id, we check if user entered it.
    const enteredStoreId = forms.value[provider.id]?.store_id || '{store_id}';
    template = template.replace('{store_id}', enteredStoreId);
    
    return template;
};

const disconnecting = ref<string | null>(null);

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

</script>
