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

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button 
                            @click="activeTab = 'permissions'" 
                            :class="[activeTab === 'permissions' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                        >
                            Permissions Assignment
                        </button>
                        <button 
                            @click="activeTab = 'roles'" 
                            :class="[activeTab === 'roles' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                        >
                            Roles Management
                        </button>
                    </nav>
                </div>

                <!-- Roles Management Tab -->
                <div v-show="activeTab === 'roles'" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">All Roles</h3>
                                <p class="text-sm text-gray-500">Create, edit, or remove roles.</p>
                            </div>
                            <button 
                                @click="showCreateRoleModal = true" 
                                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create Role
                                </span>
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Name</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="role in rolesList" :key="role.name" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ role.label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ role.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openEditModal(role)" class="text-blue-600 hover:text-blue-900 mr-4 font-semibold">Edit</button>
                                            <button 
                                                v-if="role.name !== 'owner'" 
                                                @click="deleteRole(role.name)" 
                                                class="text-red-600 hover:text-red-900 font-semibold"
                                            >
                                                Delete
                                            </button>
                                            <span v-else class="text-gray-400 cursor-not-allowed">Delete</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Permissions Tab -->
                <div v-show="activeTab === 'permissions'">
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
                                </div>
                            </div>
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
        <!-- Edit Role Modal -->
        <div v-if="showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showEditRoleModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="updateRole">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Role</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role Name (English)</label>
                                    <input 
                                        v-model="editRoleForm.name_en"
                                        type="text" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                        required
                                    >
                                    <p v-if="editRoleForm.errors.name_en" class="mt-1 text-sm text-red-600">{{ editRoleForm.errors.name_en }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role Name (Arabic)</label>
                                    <input 
                                        v-model="editRoleForm.name_ar"
                                        type="text" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-right"
                                        dir="rtl"
                                        required
                                    >
                                    <p v-if="editRoleForm.errors.name_ar" class="mt-1 text-sm text-red-600">{{ editRoleForm.errors.name_ar }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="submit" 
                                :disabled="editRoleForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Save Changes
                            </button>
                            <button 
                                type="button" 
                                @click="showEditRoleModal = false"
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
    rolesList: Array<{ name: string; display_name: any; label: string }>;
    permissions: Record<string, any>;
    rolePermissions: Record<string, string[]>;
}>();

const route = (window as any).route;

const activeTab = ref('permissions');
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

const editRoleForm = useForm({
    name_en: '',
    name_ar: ''
});
const editingRoleSlug = ref('');
const showEditRoleModal = ref(false);

const openEditModal = (role: any) => {
    editingRoleSlug.value = role.name;
    editRoleForm.clearErrors();
    editRoleForm.name_en = role.display_name?.en || '';
    editRoleForm.name_ar = role.display_name?.ar || '';
    showEditRoleModal.value = true;
};

const updateRole = () => {
    editRoleForm.put(route('admin.roles.update', editingRoleSlug.value), {
        preserveScroll: true,
        onSuccess: () => {
            showEditRoleModal.value = false;
            editRoleForm.reset();
        }
    });
};

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
        onError: (errors) => {
            // Check if it's a CSRF token error (419)
            if (errors && typeof errors === 'object' && Object.keys(errors).length === 0) {
                // Likely a 419 error - reload the page to get a fresh token
                alert('Your session has expired. The page will reload to refresh your session.');
                window.location.reload();
            }
        },
        onFinish: () => {
            // Ensure form is not stuck in processing state
        }
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

const deleteRole = (roleName?: string) => {
    const target = roleName || selectedRole.value;
    if (!target) return;
    
    // Get label
    const label = props.roles[target] || target;
    
    if (confirm(`Are you sure you want to delete the role "${label}"? This action cannot be undone.`)) {
        router.delete(route('admin.roles.destroy', target), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedRole.value === target) {
                    selectedRole.value = '';
                }
            }
        });
    }
};
</script>
