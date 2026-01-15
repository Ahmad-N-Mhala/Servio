<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manage Super Admins
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <p class="text-sm text-gray-600">
                                Create and manage accounts with full system access.
                            </p>
                            <Button @click="openCreateModal" variant="primary">
                                Add New Admin
                            </Button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="admin in superAdmins" :key="admin.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ admin.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ new Date(admin.created_at).toLocaleDateString() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Button 
                                                v-if="admin.id !== currentUser.id"
                                                size="xs" 
                                                variant="danger" 
                                                @click="deleteAdmin(admin)"
                                            >
                                                Delete
                                            </Button>
                                            <span v-else class="text-gray-400 text-xs italic">Current User</span>
                                        </td>
                                    </tr>
                                    <tr v-if="superAdmins.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No super admins found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Add New Super Admin
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <Input 
                        id="name" 
                        label="Name" 
                        v-model="form.name" 
                        :error="form.errors.name" 
                        required 
                        autofocus
                    />

                    <Input 
                        id="email" 
                        label="Email Address" 
                        type="email"
                        v-model="form.email" 
                        :error="form.errors.email" 
                        required 
                    />

                    <Input 
                        id="password" 
                        label="Password" 
                        type="password"
                        v-model="form.password" 
                        :error="form.errors.password" 
                        required 
                    />

                    <Input 
                        id="password_confirmation" 
                        label="Confirm Password" 
                        type="password"
                        v-model="form.password_confirmation" 
                        :error="form.errors.password_confirmation" 
                        required 
                    />

                    <div class="flex items-center justify-end mt-4">
                        <Button variant="ghost" @click="closeModal" type="button" class="mr-3">Cancel</Button>
                        <Button variant="primary" :disabled="form.processing" type="submit">
                            Create Account
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const route = (window as any).route;
const page = usePage();
const currentUser = computed(() => (page.props.auth as any).user);

const props = defineProps<{
    superAdmins: any[];
}>();

const showModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const openCreateModal = () => {
    form.reset();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    form.post(route('admin.super-admins.store'), {
        onSuccess: () => closeModal(),
    });
};

const deleteAdmin = (admin: any) => {
    if (confirm(`Are you sure you want to delete ${admin.name}? This action cannot be undone.`)) {
        router.delete(route('admin.super-admins.destroy', admin.id));
    }
};
</script>
