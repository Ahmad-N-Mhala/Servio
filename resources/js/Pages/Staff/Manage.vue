<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" :dir="isRtl ? 'rtl' : 'ltr'">
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Total Staff -->
                <div class="glass-card rounded-2xl p-6 card-hover bg-white dark:bg-gray-800 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Staff</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Staff -->
                <div class="glass-card rounded-2xl p-6 card-hover bg-white dark:bg-gray-800 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Role Cards -->
                <div 
                    v-for="(count, role) in stats.by_role" 
                    :key="role"
                    class="glass-card rounded-2xl p-6 card-hover bg-white dark:bg-gray-800 shadow-sm border border-gray-100"
                >
                    <div class="flex items-center gap-4">
                        <div :class="['p-3 rounded-xl', getRoleIconBg(role)]">
                            <!-- Helper to pick icon based on role (optional, using generic user icon for now or tailored) -->
                            <svg class="w-6 h-6" :class="getRoleIconColor(role)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ formatRole(String(role)) }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <Table
                :columns="columns"
                :data="staffList"
                :pagination="paginationMeta"
                v-model:search="params.search"
                title="Staff Management"
            >
                <!-- Header Actions -->
                <template #header-actions>
                    <Button v-if="hasPermission('create_staff')" variant="primary" size="md" @click="openCreateModal">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </template>
                        Add Staff Member
                    </Button>
                </template>

                <!-- Name Column -->
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-sm font-bold text-primary">{{ getInitials(row.name) }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ row.name }}</span>
                    </div>
                </template>

                <!-- Role Column -->
                <template #cell-role="{ row }">
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getRoleClass(row.role)]">
                        {{ formatRole(row.role) }}
                    </span>
                </template>

                <!-- Status Column -->
                <template #cell-is_active="{ row }">
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', row.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>

                <!-- Joined Column -->
                <template #cell-joined_at="{ row }">
                    <span class="text-sm text-gray-500">{{ row.joined_at || '-' }}</span>
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <button 
                        v-if="hasPermission('edit_staff')"
                        @click="openEditModal(row)"
                        class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-xs font-medium mr-2"
                    >
                        Edit
                    </button>
                    <button 
                        v-if="hasPermission('edit_staff')"
                        @click="toggleActive(row)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors mr-2', row.is_active ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200']"
                    >
                        {{ row.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button 
                        v-if="hasPermission('delete_staff')"
                        @click="deleteStaff(row.id)"
                        class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium"
                    >
                        Remove
                    </button>
                </template>
            </Table>
        </div>

        <!-- Add/Edit Staff Modal -->
        <Modal :show="showAddModal" :title="editingId ? 'Edit Staff Member' : 'Add Staff Member'" size="md" @close="closeAddModal">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <Input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="Enter full name"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <Input 
                        v-model="form.email" 
                        type="email" 
                        placeholder="email@example.com"
                        required
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select 
                        v-model="form.role"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                        required
                    >
                        <option value="" disabled>Select a role</option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ formatRole(role) }}
                        </option>
                    </select>
                    <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                </div>

                <div class="pt-2 text-sm text-gray-500" v-if="!editingId">
                    <p>* A temporary password will be generated and shown after creation.</p>
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" @click="closeAddModal">Cancel</Button>
                <Button variant="primary" @click="submitForm" :loading="form.processing">
                    {{ editingId ? 'Save Changes' : 'Add Staff Member' }}
                </Button>
            </template>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage, router, useForm } from '@inertiajs/vue3';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/Table.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { hasPermission } = usePermissions();

interface StaffMember {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    joined_at: string | null;
}

interface PaginatedStaff {
    data: StaffMember[];
    meta?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number;
    to?: number;
}

const props = withDefaults(defineProps<{
    staff?: PaginatedStaff;
    stats?: {
        total: number;
        active: number;
        by_role: Record<string, number>;
    };
    roles?: string[];
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    staff: () => ({ data: [] }),
    stats: () => ({ total: 0, active: 0, by_role: {} }),
    roles: () => ['owner', 'manager', 'head_chef', 'kitchen_staff', 'waiter', 'cashier', 'delivery_driver'],
    filters: () => ({})
});

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
const route = (window as any).route;

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'joined_at', label: 'Joined', sortable: true },
];

const showAddModal = ref(false);
const editingId = ref<number | null>(null);

// Search state managed here for server-side search integration
const params = ref({
    search: props.filters?.search || '',
});

// Watcher triggers server-side search
watch(
    () => params.value.search,
    debounce((value: string) => {
        router.get(route('staff.index'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300)
);

const form = useForm({
    name: '',
    email: '',
    role: ''
});

const staffList = computed(() => props.staff?.data || []);

// Adapter for Table component pagination
const paginationMeta = computed(() => {
    if (props.staff?.meta) {
        return props.staff; // Passing the whole object if it follows Resource structure usually works with Table logic expecting meta
    }
    // If props.staff is the Paginator object itself (top level)
    return props.staff; 
});

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showAddModal.value = true;
};

const openEditModal = (staff: StaffMember) => {
    editingId.value = staff.id;
    form.name = staff.name;
    form.email = staff.email;
    form.role = staff.role;
    form.clearErrors();
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    form.reset();
    editingId.value = null;
};

const submitForm = () => {
    form.clearErrors();
    let hasErrors = false;

    // Validate Name
    if (!form.name) {
        form.errors.name = 'Full name is required.';
        hasErrors = true;
    }

    // Validate Email
    if (!form.email) {
        form.errors.email = 'Email address is required.';
        hasErrors = true;
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(form.email)) {
            form.errors.email = 'Please enter a valid email address.';
            hasErrors = true;
        }
    }

    // Validate Role
    if (!form.role) {
        form.errors.role = 'Role is required.';
        hasErrors = true;
    }

    if (hasErrors) {
        return;
    }

    if (editingId.value) {
        form.put(route('staff.update', editingId.value), {
            onSuccess: () => closeAddModal()
        });
    } else {
        form.post(route('staff.store'), {
            onSuccess: () => closeAddModal()
        });
    }
};

const getInitials = (name: string): string => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    };

const formatRole = (role: string): string => {
    return role.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getRoleClass = (role: string): string => {
    const classes: Record<string, string> = {
        owner: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        manager: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        head_chef: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        kitchen_staff: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        waiter: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        cashier: 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
        delivery_driver: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    };
    return classes[role] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
};

const getRoleIconBg = (role: string): string => {
    const roleKey = String(role);
    const bgs: Record<string, string> = {
        owner: 'bg-purple-100 dark:bg-purple-900/30',
        manager: 'bg-blue-100 dark:bg-blue-900/30',
        head_chef: 'bg-orange-100 dark:bg-orange-900/30',
        kitchen_staff: 'bg-yellow-100 dark:bg-yellow-900/30',
        waiter: 'bg-green-100 dark:bg-green-900/30',
        cashier: 'bg-teal-100 dark:bg-teal-900/30',
        delivery_driver: 'bg-indigo-100 dark:bg-indigo-900/30',
    };
    return bgs[roleKey] || 'bg-gray-100 dark:bg-gray-800';
};

const getRoleIconColor = (role: string): string => {
    const roleKey = String(role);
    const colors: Record<string, string> = {
        owner: 'text-purple-600 dark:text-purple-400',
        manager: 'text-blue-600 dark:text-blue-400',
        head_chef: 'text-orange-600 dark:text-orange-400',
        kitchen_staff: 'text-yellow-600 dark:text-yellow-400',
        waiter: 'text-green-600 dark:text-green-400',
        cashier: 'text-teal-600 dark:text-teal-400',
        delivery_driver: 'text-indigo-600 dark:text-indigo-400',
    };
    return colors[roleKey] || 'text-gray-600 dark:text-gray-400';
};

const toggleActive = (member: StaffMember) => {
    router.put(route('staff.update', member.id), { is_active: !member.is_active });
    };

const deleteStaff = (id: number) => {
    if (confirm('Are you sure you want to remove this staff member?')) {
        router.delete(route('staff.destroy', id));
    }
};
</script>
