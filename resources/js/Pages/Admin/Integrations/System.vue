<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Settings /</span>
                <span>System Integrations</span>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8 pb-32">
            
            <div v-if="$page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2 mb-6">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2 mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <!-- Email Section -->
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-100/50 overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-2xl hover:shadow-gray-200/50">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-4">
                        <div class="p-3 bg-indigo-100 rounded-2xl text-indigo-600 shadow-sm">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            Email Configuration
                            <p class="mt-1 text-gray-500 text-sm font-normal">Configure SMTP settings and notification recipients.</p>
                        </div>
                    </h3>
                </div>
                <!-- Main Email Form -->
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                     <!-- SMTP Settings -->
                     <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Input 
                            v-model="form.mail_host" 
                            label="SMTP Host" 
                            placeholder="smtp.example.com" 
                            :error="form.errors.mail_host"
                        />
                        <Input 
                            v-model="form.mail_port" 
                            label="SMTP Port" 
                            placeholder="587" 
                            :error="form.errors.mail_port"
                        />
                        <Select 
                            v-model="form.mail_encryption" 
                            label="Encryption" 
                            :options="[{label: 'TLS', value: 'tls'}, {label: 'SSL', value: 'ssl'}, {label: 'None', value: null}]"
                            :error="form.errors.mail_encryption"
                        />
                     </div>
                     <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Input 
                            v-model="form.mail_username" 
                            label="SMTP Username" 
                            :error="form.errors.mail_username"
                        />
                        <Input 
                            v-model="form.mail_password" 
                            label="SMTP Password" 
                            type="password"
                            :error="form.errors.mail_password"
                        />
                     </div>

                     <div class="md:col-span-2 h-px bg-gray-100"></div>

                     <Input 
                        v-model="form.mail_from_address" 
                        label="Mail From Address" 
                        placeholder="no-reply@servio.com" 
                        :error="form.errors.mail_from_address"
                    />
                     <Input 
                        v-model="form.mail_from_name" 
                        label="Mail From Name" 
                        placeholder="Servio System" 
                        :error="form.errors.mail_from_name"
                    />
                     <div class="md:col-span-2">
                        <Input 
                            v-model="form.registration_email" 
                            label="Registration Notification Email" 
                            placeholder="leads@servio.com" 
                            :error="form.errors.registration_email"
                        />
                        <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            This email receives notifications when potential customers register interest.
                        </p>
                     </div>
                </div>

                <!-- Test Email Section -->
                <div class="bg-indigo-50/50 p-8 border-t border-indigo-100/50">
                    <div class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="flex-grow w-full">
                            <Input 
                                v-model="testEmailAddress" 
                                label="Validate Configuration" 
                                placeholder="Enter valid email (e.g., your@email.com)" 
                                icon="at-symbol"
                            />
                        </div>
                        <Button 
                            @click="sendTestEmail" 
                            :loading="sendingTestEmail" 
                            variant="secondary"
                            class="mb-0.5 whitespace-nowrap bg-white text-indigo-600 border-indigo-200 hover:bg-indigo-50"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Test Email
                        </Button>
                    </div>
                </div>
            </div>

            <!-- SMS Section -->
             <div class="bg-white rounded-3xl shadow-xl shadow-gray-100/50 overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-2xl hover:shadow-gray-200/50">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-4">
                        <div class="p-3 bg-emerald-100 rounded-2xl text-emerald-600 shadow-sm">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div>
                            SMS Configuration
                             <p class="mt-1 text-gray-500 text-sm font-normal">Setup your SMS gateway for OTPs and notifications.</p>
                        </div>
                    </h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                     <Select 
                        v-model="form.sms_provider" 
                        label="Provider" 
                        :options="[{label: 'Twilio', value: 'twilio'}, {label: 'Vonage (Nexmo)', value: 'nexmo'}]"
                        :error="form.errors.sms_provider"
                        class="w-full"
                     />
                     <Input 
                        v-model="form.sms_from" 
                        label="From Number / Sender ID" 
                        placeholder="+1234567890" 
                        :error="form.errors.sms_from"
                     />
                     <Input 
                        v-model="form.sms_sid" 
                        label="API Key / SID" 
                        :error="form.errors.sms_sid"
                     />
                     <Input 
                        v-model="form.sms_token" 
                        label="API Secret / Token" 
                        type="password" 
                        :error="form.errors.sms_token"
                     />
                </div>

                <!-- Test SMS Section -->
                <div class="bg-emerald-50/50 p-8 border-t border-emerald-100/50">
                    <div class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="flex-grow w-full">
                            <Input 
                                v-model="testPhone" 
                                label="Validate SMS Configuration" 
                                placeholder="Enter phone number (e.g., +1234567890)" 
                                icon="phone"
                            />
                        </div>
                        <Button 
                            @click="sendTestSms" 
                            :loading="sendingTestSms" 
                            variant="secondary"
                            class="mb-0.5 whitespace-nowrap bg-white text-emerald-600 border-emerald-200 hover:bg-emerald-50"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            Send Test SMS
                        </Button>
                    </div>
                </div>
            </div>
            
            <!-- Spacer for floating button -->
            <div class="h-20"></div>

            <!-- Floating Action Bar -->
            <div class="fixed bottom-6 right-6 z-30 animate-bounce-in">
                <Button 
                    @click="submit" 
                    :loading="form.processing" 
                    size="lg" 
                    class="shadow-2xl shadow-primary/40 rounded-full px-8 py-4 text-lg font-bold transform hover:scale-105 transition-all"
                >
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Save Changes
                </Button>
            </div>

        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';

const props = defineProps({
    configurations: Object,
});

const form = useForm({
    mail_host: props.configurations.mail_host || '',
    mail_port: props.configurations.mail_port || '',
    mail_username: props.configurations.mail_username || '',
    mail_password: props.configurations.mail_password || '',
    mail_encryption: props.configurations.mail_encryption || 'tls',
    mail_from_address: props.configurations.mail_from_address || '',
    mail_from_name: props.configurations.mail_from_name || '',
    registration_email: props.configurations.registration_email || '',
    sms_provider: props.configurations.sms_provider || '',
    sms_sid: props.configurations.sms_sid || '',
    sms_token: props.configurations.sms_token || '',
    sms_from: props.configurations.sms_from || '',
});

const submit = () => {
    form.post(route('admin.settings.system.update'), {
        preserveScroll: true,
    });
};

const testEmailAddress = ref('');
const sendingTestEmail = ref(false);

const sendTestEmail = () => {
    if (!testEmailAddress.value) return;
    sendingTestEmail.value = true;
    router.post(route('admin.settings.system.test-email'), {
        email: testEmailAddress.value
    }, {
        preserveScroll: true,
        onFinish: () => sendingTestEmail.value = false
    });
};

const testPhone = ref('');
const sendingTestSms = ref(false);

const sendTestSms = () => {
    if (!testPhone.value) return;
    sendingTestSms.value = true;
    router.post(route('admin.settings.system.test-sms'), {
        phone: testPhone.value
    }, {
        preserveScroll: true,
        onFinish: () => sendingTestSms.value = false
    });
};
</script>

<style scoped>
.animate-bounce-in {
    animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
