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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Provider Name *</label>
                            <input v-model="form.provider" type="text" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <div v-if="form.errors.provider" class="text-red-600 text-sm mt-1">{{ form.errors.provider }}</div>
                        </div>

                        <!-- API Key -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                            <input v-model="form.api_key" type="text" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <div v-if="form.errors.api_key" class="text-red-600 text-sm mt-1">{{ form.errors.api_key }}</div>
                        </div>

                        <!-- API Secret -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Secret</label>
                            <input v-model="form.api_secret" type="password" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" placeholder="Leave blank to keep current">
                            <div v-if="form.errors.api_secret" class="text-red-600 text-sm mt-1">{{ form.errors.api_secret }}</div>
                        </div>

                        <!-- Webhook URL -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Webhook URL</label>
                            <input v-model="form.webhook_url" type="url" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <div v-if="form.errors.webhook_url" class="text-red-600 text-sm mt-1">{{ form.errors.webhook_url }}</div>
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

const props = defineProps<{
    integration: any;
}>();

const route = (window as any).route;

const form = useForm({
    provider: props.integration.provider,
    api_key: props.integration.api_key,
    api_secret: '',
    webhook_url: props.integration.webhook_url,
    is_enabled: props.integration.is_enabled,
});

const submit = () => {
    form.put(route('admin.integrations.update', props.integration.id));
};
</script>
