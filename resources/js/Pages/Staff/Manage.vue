<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Staff Management</h1>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Manage your restaurant team and permissions</p>
                </div>
                <div class="flex gap-4 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <input 
                            v-model="params.search"
                            type="text" 
                            placeholder="Search staff..." 
                            class="w-full sm:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
                        >
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <Button variant="primary" size="md" @click="openAddModal">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </template>
                        Add Staff Member
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="glass-card rounded-2xl p-6 card-hover">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Staff</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalStaff }}</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 card-hover">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-green-100 dark:bg-green-900/30">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ activeCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 card-hover">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/30">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Roles</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ roles?.length || 4 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table Card -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <!-- Empty State -->
                <div v-if="staffCount === 0" class="p-6 py-16 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No staff members yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Start building your team by adding staff members</p>
                    <Button variant="primary" @click="openAddModal">Add Your First Staff Member</Button>
                </div>
                
                <!-- Table -->
                <div v-else>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('name')"
                                >
                                    <div class="flex items-center gap-1">
                                        Name
                                        <span v-if="params.sort_field === 'name'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('email')"
                                >
                                    <div class="flex items-center gap-1">
                                        Email
                                        <span v-if="params.sort_field === 'email'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('role')"
                                >
                                    <div class="flex items-center gap-1">
                                        Role
                                        <span v-if="params.sort_field === 'role'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('is_active')"
                                >
                                    <div class="flex items-center gap-1">
                                        Status
                                        <span v-if="params.sort_field === 'is_active'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th 
                                    scope="col" 
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sort('joined_at')"
                                >
                                    <div class="flex items-center gap-1">
                                        Joined
                                        <span v-if="params.sort_field === 'joined_at'">
                                            {{ params.sort_direction === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="member in staffList" 
                                :key="member.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-sm font-bold text-primary">{{ getInitials(member.name) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ member.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ member.email }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getRoleClass(member.role)]">
                                        {{ formatRole(member.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', member.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                                        {{ member.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ member.joined_at || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            @click="toggleActive(member)"
                                            :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors', member.is_active ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200']"
                                        >
                                            {{ member.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button 
                                            @click="deleteStaff(member.id)"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="border-t border-gray-200 bg-gray-50">
                        <Pagination :meta="paginationMeta" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Staff Modal -->
        <Modal :show="showAddModal" title="Add Staff Member" size="md" @close="closeAddModal">
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

                <div class="pt-2 text-sm text-gray-500">
                    <p>* A temporary password will be generated and shown after creation.</p>
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" @click="closeAddModal">Cancel</Button>
                <Button variant="primary" @click="submitForm" :loading="form.processing">
                    Add Staff Member
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
import Pagination from '@/Components/Pagination.vue';

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
    roles?: string[];
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    staff: () => ({ data: [] }),
    roles: () => ['owner', 'manager', 'head_chef', 'kitchen_staff', 'waiter', 'cashier', 'delivery_driver'],
    filters: () => ({})
});

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
const route = (window as any).route;

const showAddModal = ref(false);

const params = ref({
    search: props.filters?.search || '',
    sort_field: props.filters?.sort_field || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc'
});

watch(
    () => params.value.search,
    debounce((value: string) => {
        router.get(route('staff.index'), { ...params.value, search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300)
);

const sort = (field: string) => {
    params.value.sort_field = field;
    params.value.sort_direction = params.value.sort_direction === 'asc' ? 'desc' : 'asc';
    
    router.get(route('staff.index'), params.value, {
        preserveState: true,
        replace: true
    });
};

const form = useForm({
    name: '',
    email: '',
    role: ''
});

const staffList = computed(() => props.staff?.data || []);
const staffCount = computed(() => staffList.value.length);
const totalStaff = computed(() => props.staff?.total || props.staff?.meta?.total || staffCount.value);
const activeCount = computed(() => staffList.value.filter(s => s.is_active).length);

const paginationMeta = computed(() => {
    if (props.staff?.meta) {
        return props.staff.meta;
    }
    return {
        current_page: props.staff?.current_page || 1,
        last_page: props.staff?.last_page || 1,
        per_page: props.staff?.per_page || 10,
        total: props.staff?.total || 0,
        from: props.staff?.from || 0,
        to: props.staff?.to || 0
    };
});

const openAddModal = () => {
    form.reset();
    form.clearErrors();
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    form.reset();
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

    form.post(route('staff.store'), {
        onSuccess: () => {
            closeAddModal();
        }
    });
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

const toggleActive = (member: StaffMember) => {
    router.put(route('staff.update', member.id), { is_active: !member.is_active });
};

const deleteStaff = (id: number) => {
    if (confirm('Are you sure you want to remove this staff member?')) {
        router.delete(route('staff.destroy', id));
    }
};
</script>
