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
                    <label class="block text-sm font-medium text-gray-700 mb-3">Select Role</label>
                    <select 
                        v-model="selectedRole" 
                        class="w-full max-w-md rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    >
                        <option value="">Choose a role...</option>
                        <option v-for="(label, role) in roles" :key="role" :value="role">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- Permissions Grid -->
                <div v-if="selectedRole" class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gray-50">
                        <h4 class="text-lg font-semibold text-gray-900">
                            Permissions for: {{ roles[selectedRole] }}
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">Select the permissions this role should have</p>
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
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    roles: Record<string, string>;
    permissions: Record<string, any>;
    rolePermissions: Record<string, string[]>;
}>();

const route = (window as any).route;

const selectedRole = ref('');
const selectedPermissions = ref<string[]>([]);

const form = useForm({
    role: '',
    permissions: [] as string[],
});

// Watch for role changes
watch(selectedRole, (newRole) => {
    if (newRole) {
        selectedPermissions.value = props.rolePermissions[newRole] || [];
    } else {
        selectedPermissions.value = [];
    }
});

// Format permission name for display
const formatPermission = (permission: string) => {
    return permission
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

// Reset permissions to saved state
const resetPermissions = () => {
    if (selectedRole.value) {
        selectedPermissions.value = props.rolePermissions[selectedRole.value] || [];
    }
};

// Save permissions
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
</script>
