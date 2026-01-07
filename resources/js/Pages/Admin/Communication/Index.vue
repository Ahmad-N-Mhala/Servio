<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                System {{ type === 'email' ? 'Email' : 'SMS' }} Templates
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <p class="text-sm text-gray-600">
                                Manage standard {{ type }} templates sent by the system (e.g., Welcome emails, Reset Password).
                            </p>
                            <Button @click="openCreateModal" variant="primary" size="sm">
                                Add New Template
                            </Button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name / Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject / Preview</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.status') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="template in templates" :key="template.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ template.name }}</div>
                                            <div class="text-xs text-gray-500 font-mono bg-gray-100 inline-block px-1 rounded mt-1">{{ template.trigger_event }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="template.subject" class="text-sm font-semibold text-gray-800 mb-1">{{ template.subject }}</div>
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ template.content.substring(0, 50) }}...</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="template.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ template.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <Button size="xs" variant="ghost" @click="editTemplate(template)">{{ $t('common.edit') }}</Button>
                                                <Button size="xs" variant="danger" @click="deleteTemplate(template)">{{ $t('common.delete') }}</Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="templates.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No templates found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Edit Template' : 'New Template' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Name -->
                    <Input 
                        id="name" 
                        label="Template Name" 
                        v-model="form.name" 
                        :error="form.errors.name" 
                        required 
                    />

                    <!-- Trigger Event -->
                    <Select
                        id="trigger_event"
                        label="Trigger Event (System Key)"
                        v-model="form.trigger_event"
                        :error="form.errors.trigger_event"
                        :options="triggerOptions"
                    />
                    
                    <Input 
                        v-if="form.trigger_event === 'custom'" 
                        v-model="customTrigger" 
                        placeholder="Enter custom event key..." 
                        class="mt-2" 
                    />

                    <!-- Subject (Email Only) -->
                    <div v-if="type === 'email'">
                         <Input 
                            id="subject" 
                            label="Email Subject" 
                            v-model="form.subject" 
                            :error="form.errors.subject" 
                            required 
                        />
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
                            Content
                        </label>
                        <textarea 
                            id="content" 
                            v-model="form.content" 
                            rows="5" 
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all"
                            :class="{'border-rose-300 focus:border-rose-500': form.errors.content}"
                            required
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Common Variables (vary by event): <code v-text="'{{ name }}'"></code>, <code v-text="'{{ email }}'"></code>, <code v-text="'{{ link }}'"></code>, <code v-text="'{{ restaurant_name }}'"></code><br>
                            For "Restaurant Created": <code v-text="'{{ owner_email }}'"></code>, <code v-text="'{{ owner_password }}'"></code>
                        </p>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <!-- Timing -->
                    <!-- Timing -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div :class="form.timing_type !== 'immediately' ? '' : 'md:col-span-3'">
                            <Select 
                                id="timing_type" 
                                label="When to send?" 
                                v-model="form.timing_type"
                                :options="timingOptions"
                            />
                        </div>
                        <div v-if="form.timing_type !== 'immediately'">
                             <Input 
                                id="timing_days" 
                                :label="form.timing_type === 'before' ? 'Days Before' : 'Days Delay'" 
                                type="number" 
                                v-model="form.timing_days" 
                             />
                        </div>
                        <div v-if="form.timing_type !== 'immediately'">
                             <Input 
                                id="timing_time" 
                                label="Time of Day (UAE Time)" 
                                type="time"
                                v-model="form.timing_time"
                                hint="Server uses UAE Timezone"
                             />
                        </div>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-600">Active Enabled</label>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Button variant="ghost" @click="closeModal" class="mr-3">{{ $t('common.cancel') }}</Button>
                        <Button variant="primary" :disabled="form.processing" type="submit">
                            {{ isEditing ? 'Update Template' : 'Create Template' }}
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';

const route = (window as any).route;

const props = defineProps<{
    type: 'email' | 'sms';
    templates: any[];
}>();

const showModal = ref(false);
const isEditing = ref(false);
const customTrigger = ref('');

const triggerOptions = [
    { label: 'User Registered (Standard Welcome)', value: 'user_registered' },
    { label: 'Password Reset Request', value: 'password_reset' },
    { label: 'Subscription Created', value: 'subscription_created' },
    { label: 'Subscription Expiry Warning (Days Before)', value: 'subscription_warning' },
    { label: 'Subscription Expired (Immediately)', value: 'subscription_expired' },
    { label: 'Restaurant Created', value: 'restaurant_created' },
    { label: 'Inventory Expiry Warning', value: 'inventory_expiry_warning' },
    { label: 'Inventory Low Stock Warning', value: 'inventory_low_stock_warning' },
    { label: 'Custom (Type manually below)', value: 'custom' },
];

const timingOptions = [
    { label: 'Immediately', value: 'immediately' },
    { label: 'Delay After Event', value: 'after' },
    { label: 'Before Event (Scheduled)', value: 'before' },
];

const form = useForm({
    id: null,
    name: '',
    type: props.type,
    trigger_event: 'user_registered',
    subject: '',
    content: '',
    conditions: [],
    is_active: true,
    timing_type: 'immediately',
    timing_days: 0,
    timing_time: '09:00',
});

// Sync custom trigger
watch(customTrigger, () => {
    if (form.trigger_event === 'custom') {
         // This logic handled on submit really, but we need to pass strict validation
    }
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.type = props.type;
    showModal.value = true;
};

const editTemplate = (template: any) => {
    isEditing.value = true;
    form.id = template.id;
    form.name = template.name;
    form.type = props.type;
    form.trigger_event = template.trigger_event;
    form.subject = template.subject;
    form.content = template.content;
    form.is_active = !!template.is_active;
    form.timing_type = template.timing_type || 'immediately';
    
    // Check custom trigger
    const standardTriggers = ['user_registered', 'password_reset', 'subscription_created', 'subscription_expired', 'restaurant_created'];
    if (!standardTriggers.includes(template.trigger_event)) {
        form.trigger_event = 'custom';
        customTrigger.value = template.trigger_event;
    }
    
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (form.trigger_event === 'custom') {
        form.trigger_event = customTrigger.value;
    }

    if (isEditing.value) {
        form.put(route('admin.communication.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.communication.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTemplate = (template: any) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('admin.communication.destroy', template.id));
    }
};
</script>
