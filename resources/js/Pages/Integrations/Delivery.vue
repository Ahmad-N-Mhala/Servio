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
                        <div class="w-16 h-16 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-2">
                            <!-- Placeholder logs since we don't have real images yet -->
                            <span class="text-xs font-bold text-gray-400">{{ provider.name }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ provider.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ provider.description }}</p>
                        </div>
                    </div>

                    <div v-if="activeProvider === provider.id" class="space-y-4 animate-fade-in">
                         <!-- Settings Form -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store ID</label>
                                <Input v-model="forms[provider.id].store_id" type="text" placeholder="e.g. 15482" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key / Token</label>
                                <Input v-model="forms[provider.id].api_key" type="password" placeholder="****************" />
                            </div>

                             <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret Key (Optional)</label>
                                <Input v-model="forms[provider.id].api_secret" type="password" placeholder="****************" />
                            </div>

                             <div class="flex items-center gap-2 mt-4">
                                <input 
                                    type="checkbox" 
                                    :id="`enable-${provider.id}`"
                                    v-model="forms[provider.id].is_enabled"
                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                >
                                <label :for="`enable-${provider.id}`" class="text-sm text-gray-700 dark:text-gray-300">Enable Integration</label>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <Button variant="primary" size="sm" @click="saveIntegration(provider.id)" :loading="forms[provider.id].processing">
                                Save Settings
                            </Button>
                            <Button variant="ghost" size="sm" @click="activeProvider = null">
                                Cancel
                            </Button>
                        </div>
                    </div>

                    <div v-else>
                         <Button variant="outline" class="w-full" @click="editIntegration(provider.id)">
                            {{ isIntegrated(provider.id) ? 'Manage Settings' : 'Connect' }}
                        </Button>
                    </div>
                </div>
            </div>
            
            <!-- Integration Guide / Help -->
            <div class="mt-12 bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-6 border border-blue-100 dark:border-blue-900/20">
                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">How to Integrate?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">1. Get Credentials</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Login to your delivery partner dashboard (e.g. Noon Food Partner Portal) and navigate to API settings to generate your API Key and Store ID.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">2. Enter Details</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Copy the credentials and paste them into the respective fields above. Toggle "Enable Integration" and save.</p>
                    </div>
                     <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">3. Webhooks (Advanced)</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Some providers require a Webhook URL for real-time status updates. <br> Your Webhook URL: <span class="font-mono bg-white/50 px-2 py-0.5 rounded select-all">{{ webhookUrl }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
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
    forms.value[providerId].post(route('integrations.delivery.update'), {
        preserveScroll: true,
        onSuccess: () => {
            activeProvider.value = null;
        }
    });
};

const webhookUrl = computed(() => `${window.location.origin}/api/webhook/delivery`);

</script>
