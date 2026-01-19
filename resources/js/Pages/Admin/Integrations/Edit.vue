<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Delivery Integration
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <form @submit.prevent="submit">
                        <!-- Provider -->
                        <div class="mb-4">
                            <Select
                                v-model="form.provider"
                                label="Provider Name *"
                                :options="providerOptions"
                                placeholder="Select Provider"
                                disabled
                                :error="form.errors.provider"
                            />
                        </div>

                         <!-- Store ID (Common) -->
                         <!-- Store ID (Common) -->
                        <div class="mb-4">
                            <Input
                                v-model="form.store_id"
                                label="Store ID (External ID) *"
                                :error="form.errors.store_id"
                                placeholder="e.g. 12345 or X-999"
                            />
                        </div>

                        <!-- Webhook URL Display (Read Only) -->
                         <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200" v-if="form.provider">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Your Webhook URL</label>
                            <div class="flex items-center gap-2">
                                <code class="text-sm text-primary flex-1 break-all">{{ route('api.webhook.delivery', form.provider) }}</code>
                                <button type="button" @click="copyWebhook" class="text-gray-400 hover:text-gray-600 text-xs">Copy</button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Paste this URL into your {{ form.provider }} developer portal settings.</p>
                        </div>


                        <!-- Uber Eats Specifics -->
                        <template v-if="form.provider === 'uber-eats'">
                            <div class="mb-4">
                                <Input
                                    v-model="form.client_id"
                                    label="Client ID"
                                />
                            </div>
                            <div class="mb-4">
                                <Input
                                    v-model="form.client_secret"
                                    label="Client Secret"
                                    type="password"
                                    placeholder="Leave blank to keep current"
                                />
                            </div>
                        </template>

                        <!-- Standard API Key/Secret (Deliveroo, Talabat, etc) -->
                         <template v-if="form.provider !== 'uber-eats'">
                            <div class="mb-4">
                                <Input
                                    v-model="form.api_key"
                                    label="API Key / Token"
                                    :error="form.errors.api_key"
                                />
                            </div>

                            <div class="mb-4" v-if="form.provider === 'deliveroo' || form.provider === 'noon'">
                                <Input
                                    v-model="form.api_secret"
                                    label="API Secret"
                                    type="password"
                                    placeholder="Leave blank to keep current"
                                />
                            </div>
                        </template>

                        <!-- Webhook Secrets -->
                         <div class="mb-4" v-if="form.provider === 'deliveroo'">
                            <Input
                                v-model="form.webhook_secret"
                                label="Webhook Secret"
                                type="password"
                                placeholder="Leave blank to keep current"
                            />
                             <p class="text-xs text-gray-500 mt-1">Used to verify incoming webhook signatures.</p>
                        </div>


                        <!-- Webhook URL -->
                        <div class="mb-4">
                            <Input
                                v-model="form.webhook_url"
                                label="Webhook URL"
                                type="url"
                                :error="form.errors.webhook_url"
                            />
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input v-model="form.is_enabled" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">{{ $t('common.active') }}</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <Link :href="route('admin.integrations.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">{{ $t('common.cancel') }}</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                Update Integration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';

const providerOptions = [
    { value: 'uber-eats', label: 'Uber Eats' },
    { value: 'deliveroo', label: 'Deliveroo' },
    { value: 'talabat', label: 'Talabat' },
    { value: 'careem', label: 'Careem' },
    { value: 'noon', label: 'Noon Food' },
];

const props = defineProps<{
    integration: any;
}>();

const route = (window as any).route;

const form = useForm({
    provider: props.integration.provider,
    store_id: props.integration.store_id || '',
    api_key: props.integration.api_key || '',
    api_secret: '',
    client_id: props.integration.client_id || '',
    client_secret: '',
    webhook_secret: '',
    webhook_url: props.integration.webhook_url,
    is_enabled: props.integration.is_enabled,
});

const submit = () => {
    form.put(route('admin.integrations.update', props.integration.id));
};

const copyWebhook = () => {
    if (!form.provider) return;
    const url = route('api.webhook.delivery', form.provider);
    navigator.clipboard.writeText(url);
    alert('Webhook URL copied to clipboard!');
};
</script>
