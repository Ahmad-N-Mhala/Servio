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
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('common.total') }} {{ $t('nav.staff') }}</p>
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
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('common.active') }}</p>
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
                :title="$t('staff.title')"
                :allow-overflow="true"
            >
                <!-- Header Actions -->
                <template #header-actions>
                    <Button v-if="hasPermission('create_staff')" variant="primary" size="md" @click="openCreateModal">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </template>
                        {{ $t('staff.add_staff') }}
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
                <!-- Logs Column -->
                <template #cell-logs="{ row }">
                    <button @click="showStaffLogs(row)" class="text-gray-400 hover:text-primary transition-colors" :title="$t('common.history')">
                        <ClockIcon class="w-5 h-5" />
                    </button>
                </template>

                <!-- Actions Column -->
                <template #actions="{ row }">
                    <Menu as="div" class="relative inline-block text-left">
                        <div>
                            <MenuButton class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                {{ $t('common.actions') }}
                                <ChevronDownIcon class="-mr-1 ml-1 h-5 w-5 text-gray-400" aria-hidden="true" />
                            </MenuButton>
                        </div>

                        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                            <MenuItems class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="py-1">
                                    <MenuItem v-if="hasPermission('edit_staff')" v-slot="{ active }">
                                        <button @click="openEditModal(row)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'group flex w-full items-center px-4 py-2 text-sm']">
                                            <PencilIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                                            {{ $t('common.edit') }}
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-if="hasPermission('edit_staff')" v-slot="{ active }">
                                        <button @click="toggleActive(row)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'group flex w-full items-center px-4 py-2 text-sm']">
                                            <component :is="row.is_active ? NoSymbolIcon : CheckCircleIcon" class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                                            {{ row.is_active ? $t('common.inactive') : $t('common.active') }}
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-if="hasPermission('delete_staff')" v-slot="{ active }">
                                        <button @click="deleteStaff(row.id)" :class="[active ? 'bg-red-50 text-red-900' : 'text-red-700', 'group flex w-full items-center px-4 py-2 text-sm']">
                                            <TrashIcon class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-500" aria-hidden="true" />
                                            {{ $t('common.remove') }}
                                        </button>
                                    </MenuItem>
                                </div>
                            </MenuItems>
                        </transition>
                    </Menu>
                </template>
            </Table>
        </div>

        <!-- Add/Edit Staff Modal -->
        <Modal :show="showAddModal" :title="editingId ? $t('staff.edit_staff') : $t('staff.add_staff')" size="md" @close="closeAddModal">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('staff.name') }} *</label>
                    <Input 
                        v-model="form.name" 
                        type="text" 
                        :placeholder="$t('staff.name')"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('staff.email') }} *</label>
                    <Input 
                        v-model="form.email" 
                        type="email" 
                        placeholder="email@example.com"
                        required
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('staff.phone') }} *</label>
                    <Input 
                        v-model="form.phone" 
                        type="tel" 
                        placeholder="+971 50 123 4567"
                        required
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <Select
                        v-model="form.role"
                        :label="$t('staff.role') + ' *'"
                        :options="roles"
                        :placeholder="$t('common.select') + ' ' + $t('staff.role')"
                        :error="form.errors.role"
                    />
                </div>

                <div class="pt-2 text-sm text-gray-500" v-if="!editingId">
                    <p class="text-sm text-gray-500 mt-2">{{ $t('staff.password_setup_instruction') }}</p>
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" @click="closeAddModal">{{ $t('common.cancel') }}</Button>
                <Button variant="primary" @click="submitForm" :loading="form.processing">
                    {{ editingId ? $t('common.save') : $t('staff.add_staff') }}
                </Button>
            </template>
        </Modal>

        <!-- Logs Modal -->
        <Modal :show="showLogsModal" :title="$t('common.history')" size="2xl" @close="showLogsModal = false">
             <div v-if="logsLoading" class="flex justify-center p-8">
                <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
             </div>
             <div v-else-if="logs.length === 0" class="text-center p-8 text-gray-500">
                Data not available
             </div>
             <div v-else class="space-y-4 max-h-[85vh] overflow-y-auto pr-2">
                <div v-for="log in logs" :key="log.id" class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900 text-sm">{{ log.action }}</span>
                            <span class="text-xs text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">{{ log.ip }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ log.date }}</span>
                    </div>
                     <p class="text-xs text-gray-500 mb-2">by <span class="font-medium text-gray-700">{{ log.causer_name }}</span></p>
                    
                    <div v-if="log.changes && Object.keys(log.changes).length > 0" class="bg-gray-50 rounded-lg p-3 text-xs space-y-1">
                        <div v-for="(change, field) in log.changes" :key="field" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <span class="font-medium text-gray-600">{{ formatLogKey(field) }}:</span>
                            <div class="text-gray-800 break-all">
                                <span v-if="change.old !== undefined && change.old !== null" class="text-red-500 line-through mr-1">{{ formatLogValue(field, change.old) }}</span>
                                <span class="text-green-600">{{ formatLogValue(field, change.new) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
             <template #footer>
                <Button variant="ghost" @click="showLogsModal = false">{{ $t('common.close') }}</Button>
            </template>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
// @ts-ignore
import debounce from 'lodash/debounce';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { PencilIcon, TrashIcon, NoSymbolIcon, CheckCircleIcon, ChevronDownIcon, ClockIcon } from '@heroicons/vue/20/solid';

const { t } = useI18n();
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/Table.vue';
import Select from '@/Components/Select.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { hasPermission } = usePermissions();

interface StaffMember {
    id: number;
    name: string;
    email: string;
    phone: string; // Added phone
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
    roles?: Array<{ value: string; label: string }>;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    staff: () => ({ data: [] }),
    stats: () => ({ total: 0, active: 0, by_role: {} }),
    roles: () => [],
    filters: () => ({})
});

const page = usePage();
const isRtl = computed(() => page.props.isRtl as boolean);
const route = (window as any).route;

const columns = [
    { key: 'name', label: t('staff.name'), sortable: true },
    { key: 'email', label: t('staff.email'), sortable: true },
    { key: 'phone', label: t('staff.phone') },
    { key: 'role', label: t('staff.role'), sortable: true },
    { key: 'is_active', label: t('common.status'), sortable: true },
    { key: 'logs', label: '', sortable: false },
];

const showAddModal = ref(false);
const showLogsModal = ref(false);
const logs = ref<any[]>([]);
const logsLoading = ref(false);
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
    phone: '',
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
    form.phone = staff.phone || '';
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

    // Validate Phone
    if (!form.phone) {
        form.errors.phone = 'Phone number is required.';
        hasErrors = true;
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
    if (props.roles && props.roles.length > 0) {
        const found = props.roles.find(r => r.value === role);
        if (found) return found.label;
    }
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
    if (confirm(t('staff.delete_confirm'))) {
        router.delete(route('staff.destroy', id));
    }
};

const showStaffLogs = async (staff: StaffMember) => {
    logsLoading.value = true;
    showLogsModal.value = true;
    logs.value = [];
    
    try {
        const url = route('staff.logs', staff.id);
        const response = await axios.get(url);
        // Response format is likely { data: [...], ... } due to pagination
        logs.value = response.data.data ? response.data.data : response.data; 
    } catch (e) {
        console.error("Failed to fetch logs", e);
    } finally {
        logsLoading.value = false;
    }
};

const formatLogKey = (key: string | number) => {
    const k = String(key);
    switch (k) {
        case 'name': return t('staff.name');
        case 'email': return t('staff.email');
        case 'phone': return t('staff.phone');
        case 'role': return t('staff.role');
        case 'is_active': return t('common.status');
        default: return k;
    }
};

const formatLogValue = (key: string | number, value: any) => {
    const k = String(key);
    if (k === 'is_active') {
        const boolVal = value === 1 || value === true || value === 'true';
        return boolVal ? t('common.active') : t('common.inactive');
    }
    if (k === 'role') {
        // Try to find the label in the props.roles array if available
         const found = props.roles?.find((r: any) => r.value === value);
         return found ? found.label : value;
    }
    return value;
};
</script>
