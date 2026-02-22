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
                                            <div v-if="(template.subject_en || template.subject) && type === 'email'" class="text-sm font-semibold text-gray-800 mb-1">
                                                {{ template.subject_en || template.subject }}
                                            </div>
                                            <div class="text-xs text-gray-500 truncate max-w-xs">
                                                {{ (template.content_en || template.content || '').substring(0, 50) }}...
                                            </div>
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

                    <!-- Content Fields -->
                    <div class="space-y-4">
                        <div v-if="type === 'email'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Input 
                                id="subject_en" 
                                label="Subject (English)" 
                                v-model="form.subject_en" 
                                :error="form.errors.subject_en" 
                                required 
                            />
                            <Input 
                                id="subject_ar" 
                                label="Subject (Arabic)" 
                                v-model="form.subject_ar" 
                                :error="form.errors.subject_ar" 
                                required 
                                dir="rtl"
                            />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="content_en" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">
                                    Content (English{{ type === 'email' ? ' HTML' : '' }})
                                </label>
                                <textarea 
                                    id="content_en" 
                                    v-model="form.content_en" 
                                    :rows="type === 'email' ? 12 : 5" 
                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all font-mono text-sm"
                                    :class="{'border-rose-300 focus:border-rose-500': form.errors.content_en}"
                                    required
                                ></textarea>
                                <p v-if="form.errors.content_en" class="mt-1 text-sm text-red-600">{{ form.errors.content_en }}</p>
                            </div>
                            
                            <div>
                                <label for="content_ar" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1 text-right">
                                    Content (Arabic{{ type === 'email' ? ' HTML' : '' }})
                                </label>
                                <textarea 
                                    id="content_ar" 
                                    v-model="form.content_ar" 
                                    :rows="type === 'email' ? 12 : 5" 
                                    dir="rtl"
                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all font-mono text-sm"
                                    :class="{'border-rose-300 focus:border-rose-500': form.errors.content_ar}"
                                    required
                                ></textarea>
                                <p v-if="form.errors.content_ar" class="mt-1 text-sm text-red-600">{{ form.errors.content_ar }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs text-gray-500 font-medium mb-2 uppercase tracking-wider">Available Variables</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                             <span v-for="v in availableVariables" :key="v" class="text-xs font-mono bg-white border px-1.5 py-0.5 rounded text-indigo-600" v-text="'{{ ' + v + ' }}'"></span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 italic">Variables vary depending on the trigger event.</p>
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
import { ref, watch, computed } from 'vue';
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
    { label: 'Registration OTP (OTP Only)', value: 'registration_otp' },
    { label: 'Loyalty OTP (Redemption)', value: 'loyalty_otp' },
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

const availableVariables = computed(() => {
    switch(form.trigger_event) {
        case 'registration_otp':
        case 'loyalty_otp':
            return ['otp'];
        case 'user_registered':
            return ['name', 'email'];
        case 'password_reset':
            return ['link'];
        case 'subscription_created':
            return ['plan_name', 'restaurant_name'];
        case 'subscription_warning':
            return ['restaurant_name', 'plan_name', 'expiry_date', 'days_remaining', 'link'];
        case 'subscription_expired':
            return ['restaurant_name', 'expiry_date', 'link'];
        case 'restaurant_created':
            return ['restaurant_name'];
        case 'inventory_expiry_warning':
            return ['restaurant_name', 'batch_number', 'ingredient_name_en', 'days_remaining', 'quantity_remaining'];
        case 'inventory_low_stock_warning':
            return ['restaurant_name', 'ingredient_name_en', 'current_stock', 'reorder_level'];
        default:
            return ['name', 'email', 'link', 'restaurant_name', 'otp'];
    }
});

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
    subject_en: '',
    subject_ar: '',
    content_en: '',
    content_ar: '',
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
    form.subject_en = template.subject_en;
    form.subject_ar = template.subject_ar;
    form.content_en = template.content_en;
    form.content_ar = template.content_ar;
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
    // Sync Custom Trigger
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
