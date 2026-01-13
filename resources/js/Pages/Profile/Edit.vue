<template>
    <MainLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
                </div>

                <!-- Profile Information -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Update your account's profile information and email address.
                            </p>
                        </header>

                        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ $t('common.name') }}</label>
                                <input v-model="form.name" type="text" id="name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required autocomplete="name">
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <PhoneInput 
                                    v-model="form.phone" 
                                    :country="country"
                                    id="phone" 
                                    autocomplete="tel"
                                    :error="form.errors.phone"
                                />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="form.email" type="email" id="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required autocomplete="username">
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Update Password -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Update Password</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Ensure your account is using a long, random password to stay secure.
                            </p>
                        </header>

                        <form @submit.prevent="passwordForm.put(route('password.update'))" class="mt-6 space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input v-model="passwordForm.current_password" type="password" id="current_password" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" autocomplete="current-password">
                                <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input v-model="passwordForm.password" type="password" id="password" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" autocomplete="new-password">
                                <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input v-model="passwordForm.password_confirmation" type="password" id="password_confirmation" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" autocomplete="new-password">
                                <div v-if="passwordForm.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password_confirmation }}</div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" :disabled="passwordForm.processing">{{ $t('common.save') }}</button>
                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const page = usePage();
const user: any = page.props.auth.user;

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
