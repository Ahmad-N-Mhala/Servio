<template>
    <MainLayout>
        <Head title="Profile" />
        
        <div class="min-h-screen bg-gray-50/50 py-8 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Main Header -->
                <div class="mb-8 sm:mb-12">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                        Account Settings
                    </h1>
                    <p class="mt-2 text-lg text-gray-500">
                        Manage your personal details and security preferences.
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
                                    <h2 class="text-xl font-bold text-gray-900">Profile Information</h2>
                                    <p class="text-sm text-gray-500">Update your account's profile information and email address.</p>
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
                                    placeholder="Your full name"
                                />

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <PhoneInput 
                                        v-model="form.phone" 
                                        :country="country"
                                        id="phone" 
                                        autocomplete="tel"
                                        :error="form.errors.phone"
                                    />
                                </div>

                                <Input
                                    v-model="form.email"
                                    label="Email Address"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    :error="form.errors.email"
                                    placeholder="name@example.com"
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
                                            Saved Successfully
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
                                    <h2 class="text-xl font-bold text-gray-900">Security</h2>
                                    <p class="text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
                                </div>
                            </div>

                            <form @submit.prevent="passwordForm.put(route('password.update'))" class="space-y-6 max-w-2xl">
                                <Input
                                    v-model="passwordForm.current_password"
                                    label="Current Password"
                                    type="password"
                                    autocomplete="current-password"
                                    :error="passwordForm.errors.current_password"
                                    placeholder="••••••••"
                                />

                                <Input
                                    v-model="passwordForm.password"
                                    label="New Password"
                                    type="password"
                                    autocomplete="new-password"
                                    :error="passwordForm.errors.password"
                                    placeholder="••••••••"
                                />

                                <Input
                                    v-model="passwordForm.password_confirmation"
                                    label="Confirm Password"
                                    type="password"
                                    autocomplete="new-password"
                                    :error="passwordForm.errors.password_confirmation"
                                    placeholder="••••••••"
                                />

                                <div class="flex items-center gap-4 pt-2">
                                    <Button type="submit" :loading="passwordForm.processing" variant="secondary">
                                        Update Password
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
                                            Updated Successfully
                                        </p>
                                    </Transition>
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

const route = (window as any).route;
</script>
