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

            <!-- Empty State -->
            <div v-if="methodsList.length === 0" class="glass-card rounded-2xl overflow-hidden text-center py-16 px-6">
                 <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                     </svg>
                 </div>
                 <h3 class="text-lg font-medium text-gray-900">No earning methods configured</h3>
                 <p class="text-gray-500 mt-1">Add a method to start rewarding your customers.</p>
            </div>

            <!-- Methods Grid -->
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

        <!-- Add/Edit Modal (Fullscreen/Large for Designer) -->
        <Modal :show="showModal" @close="closeModal" :title="editingMethod ? 'Edit Earning Method & Design' : 'Add Earning Method & Design'" size="7xl">
             <!-- Use key to force re-render when editingMethod changes -->
             <div class="p-1">
                 <CardDesigner 
                    :key="editingMethod?.id || 'new'"
                    :settings="settings"
                    :earning-method="editingMethod || undefined"
                    @success="closeModal"
                 />
             </div>
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
import CardDesigner from './CardDesigner.vue';

const props = withDefaults(defineProps<{
    methods: any;
    settings?: any;
    filters?: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>(), {
    methods: () => ({ data: [] }),
    settings: () => ({}),
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
        visit: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'
    };
    return icons[type] || '';
};

const openModal = (method: any = null) => {
    editingMethod.value = method;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingMethod.value = null;
    // Optional: Refresh data to reflect changes
    router.reload({ only: ['methods', 'settings'] });
};

const form = useForm({}); // Placeholder for delete

const deleteMethod = (method: any) => {
    if (confirm('Are you sure you want to delete this earning method?')) {
        form.delete(route('loyalty.earning-methods.destroy', method.id));
    }
};
</script>
