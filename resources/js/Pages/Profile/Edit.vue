<template>
    <MainLayout>
        <Head :title="$t('auth.profile')" />
        
        <div class="min-h-screen bg-gray-50/50 py-8 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Main Header -->
                <div class="mb-8 sm:mb-12">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                        {{ $t('auth.settings') }}
                    </h1>
                    <p class="mt-2 text-lg text-gray-500">
                        {{ $t('auth.profile_subtitle') }}
                    </p>
                </div>

                <div class="space-y-6 sm:space-y-8">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-100 border border-gray-100 overflow-hidden transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                        <div class="p-6 sm:p-10">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $t('auth.profile_info') }}</h2>
                                    <p class="text-sm text-gray-500">{{ $t('auth.profile_info_desc') }}</p>
                                </div>
                            </div>

                            <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-6 max-w-2xl">
                                <Input
                                    v-model="form.name"
                                    :label="$t('common.name')"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    :error="form.errors.name"
                                    :placeholder="$t('auth.name_placeholder') || 'Your full name'"
                                />

                                <PhoneInput 
                                    v-model="form.phone" 
                                    :country="country"
                                    id="phone" 
                                    :label="$t('customers.phone_number')"
                                    autocomplete="tel"
                                    :error="form.errors.phone"
                                />

                                <Input
                                    v-model="form.email"
                                    :label="$t('auth.email')"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    :error="form.errors.email"
                                    :placeholder="$t('auth.email')"
                                />

                                <div class="flex items-center gap-4 pt-2">
                                    <Button type="submit" :loading="form.processing">
                                        {{ $t('common.save') }}
                                    </Button>
                                    
                                    <Transition
                                        enter-active-class="transition ease-out duration-300"
                                        enter-from-class="opacity-0 translate-x-2"
                                        enter-to-class="opacity-100 translate-x-0"
                                        leave-active-class="transition ease-in duration-200"
                                        leave-from-class="opacity-100 translate-x-0"
                                        leave-to-class="opacity-0 translate-x-2"
                                    >
                                        <p v-if="form.recentlySuccessful" class="text-sm font-medium text-green-600 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            {{ $t('auth.saved_successfully') }}
                                        </p>
                                    </Transition>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Password Card -->
                    <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-100 border border-gray-100 overflow-hidden transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                        <div class="p-6 sm:p-10">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $t('auth.security') }}</h2>
                                    <p class="text-sm text-gray-500">{{ $t('auth.security_desc') }}</p>
                                </div>
                            </div>

                            <div v-if="status" class="mb-6 p-4 bg-green-50/80 backdrop-blur-sm border border-green-200 rounded-xl text-green-700 font-medium text-sm flex items-start gap-2 animate-fade-in">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ status }}</span>
                            </div>

                            <div v-if="resetEmailForm.errors.email" class="mb-6 p-4 bg-rose-50/80 backdrop-blur-sm border border-rose-200 rounded-xl text-rose-700 font-medium text-sm flex items-start gap-2 animate-fade-in">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span>{{ resetEmailForm.errors.email }}</span>
                            </div>

                            <form @submit.prevent="passwordForm.put(route('password.update'))" class="space-y-6 max-w-2xl">
                                <Input
                                    v-model="passwordForm.current_password"
                                    :label="$t('auth.current_password')"
                                    type="password"
                                    autocomplete="current-password"
                                    :error="passwordForm.errors.current_password"
                                    placeholder="••••••••"
                                />

                                <Input
                                    v-model="passwordForm.password"
                                    :label="$t('auth.new_password')"
                                    type="password"
                                    autocomplete="new-password"
                                    :error="passwordForm.errors.password"
                                    placeholder="••••••••"
                                />

                                <Input
                                    v-model="passwordForm.password_confirmation"
                                    :label="$t('auth.confirm_password')"
                                    type="password"
                                    autocomplete="new-password"
                                    :error="passwordForm.errors.password_confirmation"
                                    placeholder="••••••••"
                                />

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-100 mt-6">
                                    <div class="flex items-center gap-4">
                                        <Button type="submit" :loading="passwordForm.processing">
                                            {{ $t('auth.update_password') }}
                                        </Button>

                                        <Transition
                                            enter-active-class="transition ease-out duration-300"
                                            enter-from-class="opacity-0 translate-x-2"
                                            enter-to-class="opacity-100 translate-x-0"
                                            leave-active-class="transition ease-in duration-200"
                                            leave-from-class="opacity-100 translate-x-0"
                                            leave-to-class="opacity-0 translate-x-2"
                                        >
                                            <p v-if="passwordForm.recentlySuccessful" class="text-sm font-medium text-green-600 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                {{ $t('auth.saved_successfully') }}
                                            </p>
                                        </Transition>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-400 font-bold uppercase">{{ $t('common.or') || 'OR' }}</span>
                                        <Button 
                                            type="button" 
                                            variant="secondary" 
                                            @click="sendResetEmail" 
                                            :loading="resetEmailForm.processing"
                                        >
                                            {{ $t('auth.reset_via_email') }}
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage();
const user: any = (page.props as any).auth.user;

const country = computed(() => (page.props.current_restaurant as any)?.country || 'United Arab Emirates');

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '', 
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const resetEmailForm = useForm({
    email: user.email,
});

const sendResetEmail = () => {
    resetEmailForm.post(route('password.email'), {
        preserveScroll: true,
    });
};

const route = (window as any).route;
</script>
