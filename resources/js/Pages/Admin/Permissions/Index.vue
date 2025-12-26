<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Roles & Permissions
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Manage Role Permissions</h3>
                    <p class="mt-1 text-sm text-gray-600">Configure what each role can access in the system</p>
                </div>

                <!-- Role Selector -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-end gap-4">
                        <div class="w-full max-w-md">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Role to Manage</label>
                            <div class="flex gap-2">
                                <select 
                                    v-model="selectedRole" 
                                    class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                >
                                    <option value="">Choose a role...</option>
                                    <option v-for="(label, role) in roles" :key="role" :value="role">
                                        {{ label }}
                                    </option>
                                </select>
                                <button 
                                    v-if="selectedRole && selectedRole !== 'owner'"
                                    @click="deleteRole"
                                    class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100"
                                    title="Delete Role"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button 
                            @click="showCreateRoleModal = true"
                            class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Role
                        </button>
                    </div>
                </div>

                <!-- Permissions Grid -->
                <div v-if="selectedRole" class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                Permissions for: {{ roles[selectedRole] }}
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">Select the permissions this role should have</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="savePermissions">
                            <div class="space-y-6">
                                <div 
                                    v-for="(module, moduleKey) in permissions" 
                                    :key="moduleKey"
                                    class="border border-gray-200 rounded-lg p-4 hover:border-primary/30 transition-colors"
                                >
                                    <h5 class="font-semibold text-gray-900 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ module.label }}
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 ml-7">
                                        <label 
                                            v-for="permission in module.permissions" 
                                            :key="permission"
                                            class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer"
                                        >
                                            <input 
                                                type="checkbox" 
                                                :value="permission"
                                                v-model="selectedPermissions"
                                                class="rounded border-gray-300 text-primary focus:ring-primary"
                                            >
                                            <span class="text-sm text-gray-700">{{ formatPermission(permission) }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-gray-200">
                                <button 
                                    type="button"
                                    @click="resetPermissions"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Reset
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
                                >
                                    <span v-if="!form.processing">Save Permissions</span>
                                    <span v-else>Saving...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Select a Role</h3>
                    <p class="mt-2 text-sm text-gray-500">Choose a role from the dropdown above to manage its permissions</p>
                </div>
            </div>
        </div>

        <!-- Create Role Modal -->
        <div v-if="showCreateRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showCreateRoleModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="createRole">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Role</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role Name (English)</label>
                                    <input 
                                        v-model="createRoleForm.name_en"
                                        type="text" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                        placeholder="e.g. Supervisor"
                                        required
                                    >
                                    <p v-if="createRoleForm.errors.name_en" class="mt-1 text-sm text-red-600">{{ createRoleForm.errors.name_en }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role Name (Arabic)</label>
                                    <input 
                                        v-model="createRoleForm.name_ar"
                                        type="text" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-right"
                                        dir="rtl"
                                        placeholder="مثال: مشرف"
                                        required
                                    >
                                    <p v-if="createRoleForm.errors.name_ar" class="mt-1 text-sm text-red-600">{{ createRoleForm.errors.name_ar }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="submit" 
                                :disabled="createRoleForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Create
                            </button>
                            <button 
                                type="button" 
                                @click="showCreateRoleModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    roles: Record<string, string>;
    permissions: Record<string, any>;
    rolePermissions: Record<string, string[]>;
}>();

const route = (window as any).route;

const selectedRole = ref('owner');
const selectedPermissions = ref<string[]>(props.rolePermissions[selectedRole.value] || []);
const showCreateRoleModal = ref(false);

const form = useForm({
    role: '',
    permissions: [] as string[],
});

const createRoleForm = useForm({
    name_en: '',
    name_ar: ''
});

// Watch for role changes
watch(selectedRole, (newRole) => {
    if (newRole) {
        selectedPermissions.value = props.rolePermissions[newRole] || [];
    } else {
        selectedPermissions.value = [];
    }
});

// Watch for server-side updates
watch(() => props.rolePermissions, (newRolePerms) => {
    if (selectedRole.value && newRolePerms[selectedRole.value]) {
        selectedPermissions.value = [...newRolePerms[selectedRole.value]];
    }
}, { deep: true });

// Also watch roles to ensure selected role still exists
watch(() => props.roles, (newRoles) => {
    if (selectedRole.value && !newRoles[selectedRole.value]) {
        selectedRole.value = ''; // Reset if deleted
    }
});

const formatPermission = (permission: string) => {
    return permission
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const resetPermissions = () => {
    if (selectedRole.value) {
        selectedPermissions.value = props.rolePermissions[selectedRole.value] || [];
    }
};

const savePermissions = () => {
    form.role = selectedRole.value;
    form.permissions = selectedPermissions.value;
    form.post(route('admin.permissions.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Permissions saved successfully
        },
    });
};

const createRole = () => {
    createRoleForm.post(route('admin.roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateRoleModal.value = false;
            createRoleForm.reset();
            // Optionally select the new role? The page reloads, so we might need logic, but user can select it.
        }
    });
};

const deleteRole = () => {
    if (!selectedRole.value) return;
    if (confirm(`Are you sure you want to delete the role "${props.roles[selectedRole.value]}"? This action cannot be undone.`)) {
        router.delete(route('admin.roles.destroy', selectedRole.value), {
            preserveScroll: true,
            onSuccess: () => {
                selectedRole.value = '';
            }
        });
    }
};
</script>
