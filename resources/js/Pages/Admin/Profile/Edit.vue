<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Profile Information -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Update your account's profile information and email address.
                            </p>
                        </header>

                        <form @submit.prevent="form.patch(route('admin.profile.update'))" class="mt-6 space-y-6">
                            <Input
                                v-model="form.name"
                                :label="$t('common.name')"
                                type="text"
                                required
                                autocomplete="name"
                                :error="form.errors.name"
                            />

                            <PhoneInput
                                v-model="form.phone"
                                :country="country"
                                id="phone"
                                label="Phone Number"
                                autocomplete="tel"
                                :error="form.errors.phone"
                            />

                            <Input
                                v-model="form.email"
                                label="Email"
                                type="email"
                                required
                                autocomplete="username"
                                :error="form.errors.email"
                            />

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="form.processing">{{ $t('common.save') }}</button>
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

                        <form @submit.prevent="passwordForm.put(route('admin.password.update'))" class="mt-6 space-y-6">
                            <Input
                                v-model="passwordForm.current_password"
                                type="password"
                                id="current_password"
                                label="Current Password"
                                autocomplete="current-password"
                                :error="passwordForm.errors.current_password"
                            />

                            <Input
                                v-model="passwordForm.password"
                                type="password"
                                id="password"
                                label="New Password"
                                autocomplete="new-password"
                                :error="passwordForm.errors.password"
                            />

                            <Input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                id="password_confirmation"
                                label="Confirm Password"
                                autocomplete="new-password"
                                :error="passwordForm.errors.password_confirmation"
                            />

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="passwordForm.processing">{{ $t('common.save') }}</button>
                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PhoneInput from '@/Components/PhoneInput.vue';
import Input from '@/Components/Input.vue';

const user: any = usePage().props.auth.user;
const country = 'United Arab Emirates'; // Default country for Super Admin profile

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '', // Handle null
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const route = (window as any).route;
</script>
