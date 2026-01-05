<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header & Search -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('nav.earning_methods') || 'Earning Methods' }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('loyalty.earning_description') || 'Configure how customers earn loyalty points' }}</p>
                </div>
                <div class="flex gap-4 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <input 
                            v-model="params.search"
                            type="text" 
                            :placeholder="$t('common.search')" 
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
                        {{ $t('common.add') }} {{ $t('common.method') || 'Method' }}
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
                 <h3 class="text-lg font-medium text-gray-900">{{ $t('charts.no_data') }}</h3>
                 <p class="text-gray-500 mt-1">{{ $t('loyalty.no_methods_configured') || 'Add a method to start rewarding your customers.' }}</p>
            </div>

            <!-- Methods Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="method in methodsList" 
                    :key="method.id"
                    class="glass-card p-6 rounded-2xl border border-gray-100 hover:border-primary/30 transition-all group relative overflow-hidden"
                >
                    <!-- Hover Actions -->
                    <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-10">
                        <button @click="openModal(method)" class="text-purple-500 hover:text-purple-700 bg-white/90 p-1.5 rounded-full shadow-sm backdrop-blur-sm transition-colors" title="Design Preview">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </button>
                        <button @click="openModal(method)" class="text-blue-500 hover:text-blue-700 bg-white/90 p-1.5 rounded-full shadow-sm backdrop-blur-sm transition-colors" title="Edit Configuration">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button @click="deleteMethod(method)" class="text-red-500 hover:text-red-700 bg-white/90 p-1.5 rounded-full shadow-sm backdrop-blur-sm transition-colors" title="Delete Method">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-primary/10 to-purple-100 transition-transform group-hover:scale-110 duration-300">
                             <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(method.type)" />
                             </svg>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full border" :class="method.is_active ? 'bg-green-50 text-green-700 border-green-100' : 'bg-gray-50 text-gray-600 border-gray-100'">
                             {{ method.is_active ? $t('common.active') : $t('common.inactive') }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-1 group-hover:text-primary transition-colors">{{ getLocaleName(method.name) }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-6 h-10">{{ method.description || $t('common.no_description') }}</p>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 bg-gray-50/50 -mx-6 -mb-6 px-6 py-4">
                        <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
                            {{ getTypeLabel(method.type) }}
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="text-lg font-bold text-gray-900 leading-none">
                                {{ method.points }} {{ $t('loyalty.points') }}
                            </span>
                            <span v-if="method.type === 'order_total'" class="text-[10px] text-gray-500 uppercase font-medium tracking-wide">
                                {{ $t('loyalty.per') || 'per' }} {{ method.currency_amount || 1 }} {{ currency }}
                            </span>
                             <span v-else class="text-[10px] text-gray-500 uppercase font-medium tracking-wide">
                                {{ $t('loyalty.per_visit') || 'per Visit' }}
                            </span>
                        </div>
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
import { useForm, router, usePage } from '@inertiajs/vue3';
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

const { locale, t } = useI18n();
const route = (window as any).route;
const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

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
    if (!name) return t('common.unknown') || 'Unknown';
    return name[locale.value] || Object.values(name)[0] || '';
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        order_total: t('loyalty.order_amount') || 'Per Order Amount',
        visit: t('loyalty.per_visit') || 'Per Visit'
    };
    return labels[type] || type;
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
    if (confirm(t('common.confirm'))) {
        form.delete(route('loyalty.earning-methods.destroy', method.id));
    }
};
</script>
