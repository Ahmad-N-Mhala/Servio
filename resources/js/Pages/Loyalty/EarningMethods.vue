<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header & Search -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Earning Methods</h1>
                    <p class="mt-1 text-sm text-gray-500">Configure how customers earn loyalty points</p>
                </div>
                <div class="flex gap-4 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <input 
                            v-model="params.search"
                            type="text" 
                            placeholder="Search methods..." 
                            class="w-full sm:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
                        >
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <Button v-if="methodsList.length === 0" @click="openModal()" variant="primary" class="whitespace-nowrap">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Method
                    </Button>
                </div>
            </div>

            <!-- Table Layout -->
            <!-- Cards Grid Layout -->
            <div v-if="methodsList.length === 0" class="glass-card rounded-2xl overflow-hidden text-center py-16 px-6">
                 <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                     </svg>
                 </div>
                 <h3 class="text-lg font-medium text-gray-900">No earning methods configred</h3>
                 <p class="text-gray-500 mt-1">Add a method to start rewarding your customers.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="method in methodsList" 
                    :key="method.id"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                             <div class="p-2 rounded-lg" :class="getTypeColor(method.type)">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(method.type)" />
                                 </svg>
                             </div>
                             <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ getTypeLabel(method.type) }}</span>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="method.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                             {{ method.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ getLocaleName(method.name) }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-4 h-10">{{ method.description || 'No description provided' }}</p>

                    <div class="mt-auto bg-gray-50 rounded-xl p-4 mb-6">
                        <div class="text-sm text-gray-500 mb-1">Earning Rule</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-primary">{{ method.points }}</span>
                            <span class="font-semibold text-gray-900">Points</span>
                            <span v-if="method.type === 'order_total'" class="text-sm text-gray-500">per {{ method.currency_amount || 1 }} Currency</span>
                            <span v-if="method.type === 'visit'" class="text-sm text-gray-500">per Visit</span>
                            <span v-if="method.type === 'referral'" class="text-sm text-gray-500">per Referral</span>
                        </div>
                         <div class="flex flex-wrap gap-2 mt-2">
                             <span v-if="method.min_spent" class="text-xs bg-white px-2 py-1 rounded border border-gray-200 text-gray-600">Min Order: {{ method.min_spent }}</span>
                             <span v-if="method.max_points" class="text-xs bg-white px-2 py-1 rounded border border-gray-200 text-gray-600">Max Cap: {{ method.max_points }}</span>
                         </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <button @click="openModal(method)" class="flex-1 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors">
                            Edit Configuration
                        </button>
                        <button @click="deleteMethod(method)" class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Delete Method">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" :title="editingMethod ? 'Edit Earning Method' : 'Add Earning Method'" size="lg">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name (Multilingual) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Input 
                        v-model="form.name.en"
                        label="Name (English)"
                        placeholder="e.g. Loyalty Points"
                        required
                        :error="(form.errors as any)['name.en']"
                    />
                    <Input 
                        v-model="form.name.ar"
                        label="Name (Arabic)"
                        placeholder="e.g. نقاط الولاء"
                        class="text-right"
                        dir="rtl"
                        :error="(form.errors as any)['name.ar']"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea 
                        v-model="form.description"
                        rows="2"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                        placeholder="Brief description of how this method works..."
                    ></textarea>
                </div>

                <!-- Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Earning Type</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button 
                            type="button"
                            v-for="type in ['order_total', 'visit']"   
                            :key="type"
                            @click="form.type = type"
                            class="relative flex items-center p-3 rounded-xl border transition-all duration-200 text-left"
                            :class="form.type === type ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                        >
                            <div class="p-2 rounded-lg mr-3" :class="form.type === type ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(type)" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold" :class="form.type === type ? 'text-primary' : 'text-gray-900'">
                                    {{ getTypeLabel(type) }}
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Points Configuration -->
                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 space-y-4">
                    <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Points Configuration
                    </h4>

                    <!-- Ratio for Order Total -->
                    <div v-if="form.type === 'order_total'" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1 w-full">
                            <Input 
                                v-model="form.points"
                                label="Earn Points"
                                type="number"
                                min="1"
                                required
                                :error="form.errors.points"
                            />
                        </div>
                        <div class="hidden sm:flex flex-col items-center justify-center pt-6 text-gray-400">
                            <span class="text-xs font-bold uppercase tracking-wider">For Every</span>
                            <svg class="w-4 h-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                        <div class="sm:hidden w-full text-center text-xs font-bold text-gray-400 uppercase tracking-wider py-2">
                            For Every
                        </div>
                        <div class="flex-1 w-full">
                            <Input 
                                v-model="form.currency_amount as any"
                                label="Amount (Currency)"
                                type="number"
                                min="0.01"
                                step="0.01"
                                required
                                :error="form.errors.currency_amount"
                            />
                        </div>
                    </div>

                    <!-- Simple Points for others -->
                    <div v-else>
                        <Input 
                            v-model="form.points"
                            :label="getPointsLabel(form.type)"
                            type="number"
                            min="1"
                            required
                            :error="form.errors.points"
                        />
                    </div>

                    <!-- Conditions -->
                    <div v-if="['order_total', 'visit'].includes(form.type)" class="pt-4 mt-4 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Input 
                            v-model="form.min_spent as any"
                            label="Min Spent Amount"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Optional"
                            :error="form.errors.min_spent"
                        />
                        <Input 
                            v-if="form.type === 'order_total'"
                            v-model="form.max_points as any"
                            label="Max Points Cap"
                            type="number"
                            min="1"
                            placeholder="Optional"
                            :error="form.errors.max_points"
                        />
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center h-5">
                        <input 
                            type="checkbox" 
                            v-model="form.is_active"
                            id="is_active"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4 cursor-pointer"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700 cursor-pointer">Active Status</label>
                        <p class="text-gray-500">Enable or disable this earning method.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                    <Button type="submit" :loading="form.processing">{{ editingMethod ? 'Update Method' : 'Create Method' }}</Button>
                </div>
            </form>
        </Modal>
    </MainLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
const props = withDefaults(defineProps<{
    methods: any;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    methods: () => ({ data: [] }),
    filters: () => ({})
});

const { locale } = useI18n();
const route = (window as any).route;

const showModal = ref(false);
const editingMethod = ref<any>(null);

const params = ref({
    search: props.filters?.search || '',
    sort_field: props.filters?.sort_field || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc'
});

watch(
    () => params.value.search,
    debounce((value: string) => {
        // @ts-ignore
        router.get(route('loyalty.earning-methods.index'), { ...params.value, search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300)
);

const methodsList = computed(() => props.methods.data || []);
// const paginationMeta = computed(() => props.methods); // Removed unused pagination

const form = useForm({
    name: { en: '', ar: '' },
    description: '',
    type: 'order_total',
    points: 1,
    currency_amount: 1,
    min_spent: null,
    max_points: null,
    is_active: true
});

const getLocaleName = (name: any) => {
    if (typeof name === 'string') return name;
    return name[locale.value] || name['en'] || 'Unknown';
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        order_total: 'Per Order Amount',
        visit: 'Per Visit'
    };
    return labels[type] || type;
};

const getPointsLabel = (type: string) => {
    if (type === 'order_total') return 'Points per Currency Unit';
    if (type === 'visit') return 'Points per Visit';
    return 'Points Awarded';
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        order_total: 'bg-blue-100 text-blue-600',
        visit: 'bg-indigo-100 text-indigo-600',
        referral: 'bg-purple-100 text-purple-600',
        review: 'bg-yellow-100 text-yellow-600'
    };
    return colors[type] || 'bg-gray-100 text-gray-600';
};

const getTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        order_total: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        visit: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
        referral: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        review: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'
    };
    return icons[type] || '';
};

const openModal = (method: any = null) => {
    if (method) {
        editingMethod.value = method;
        form.name.en = method.name.en || method.name;
        form.name.ar = method.name.ar || '';
        form.description = method.description;
        form.type = method.type;
        form.points = method.points;
        form.currency_amount = method.currency_amount || 1;
        form.min_spent = method.min_spent;
        form.max_points = method.max_points;
        form.is_active = !!method.is_active;
    } else {
        editingMethod.value = null;
        form.reset();
        form.clearErrors();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    editingMethod.value = null;
};

const submit = () => {
    if (editingMethod.value) {
        form.put(route('loyalty.earning-methods.update', editingMethod.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('loyalty.earning-methods.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteMethod = (method: any) => {
    if (confirm('Are you sure you want to delete this earning method?')) {
        form.delete(route('loyalty.earning-methods.destroy', method.id));
    }
};
</script>
