<template>
    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" :dir="isRtl ? 'rtl' : 'ltr'">

            <!-- Page Header -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $t('nav.earning_methods') }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $t('loyalty.earning_description') }}</p>
                    </div>
                </div>
            </div>

            <!-- ── EXISTING METHOD ── -->
            <template v-if="method">

                <!-- Status + Actions Bar -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full border"
                              :class="method.is_active
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : 'bg-gray-100 text-gray-500 border-gray-200'">
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="method.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                            {{ method.is_active ? $t('common.active') : $t('common.inactive') }}
                        </span>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400 px-3 py-1.5 bg-gray-100 rounded-full border border-gray-200">
                            {{ getTypeLabel(method.type) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="openModal(method)"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-primary bg-primary/10 hover:bg-primary/20 transition-all border border-primary/20 hover:border-primary/40"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ $t('common.edit') }}
                        </button>
                        <button
                            @click="deleteMethod(method)"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 transition-all border border-red-100 hover:border-red-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ $t('common.delete') }}
                        </button>
                    </div>
                </div>

                <!-- Main Config Card -->
                <div class="glass-card rounded-3xl overflow-hidden shadow-xl border border-white/60">

                    <!-- Hero Banner -->
                    <div class="relative bg-gradient-to-br from-primary via-primary/90 to-purple-600 px-8 pt-8 pb-20 overflow-hidden">
                        <!-- Background decoration -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
                        
                        <div class="relative z-10 flex items-start gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 shadow-lg">
                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(method.type)" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-white tracking-tight">{{ getLocaleName(method.name) }}</h2>
                                <p class="text-white/70 mt-1 text-sm leading-relaxed max-w-lg">
                                    {{ method.description || $t('common.no_description') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Strip (overlapping the banner) -->
                    <div class="relative -mt-12 mx-8 mb-0">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 grid grid-cols-3 divide-x divide-gray-100">
                            <!-- Points -->
                            <div class="px-6 py-5 text-center">
                                <p class="text-3xl font-black text-gray-900">{{ method.points }}</p>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $t('loyalty.points') }}</p>
                            </div>
                            <!-- Per Amount / Per Visit -->
                            <div class="px-6 py-5 text-center">
                                <p class="text-3xl font-black text-gray-900" v-if="method.type === 'order_total'">
                                    {{ method.currency_amount || 1 }} <span class="text-sm font-bold text-gray-400">{{ currency }}</span>
                                </p>
                                <p class="text-3xl font-black text-gray-900" v-else>1</p>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                                    {{ method.type === 'order_total' ? $t('loyalty.order_amount') : $t('loyalty.per_visit') }}
                                </p>
                            </div>
                            <!-- Min Spend -->
                            <div class="px-6 py-5 text-center">
                                <p class="text-3xl font-black" :class="method.min_spent ? 'text-gray-900' : 'text-gray-300'">
                                    {{ method.min_spent ? method.min_spent : '—' }}
                                    <span v-if="method.min_spent" class="text-sm font-bold text-gray-400">{{ currency }}</span>
                                </p>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Min. Spend</p>
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="px-8 pt-6 pb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Earning Rule -->
                            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ $t('loyalty.reward_type') }}</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(method.type)" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900">{{ getTypeLabel(method.type) }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <template v-if="method.type === 'order_total'">
                                                {{ method.points }} {{ $t('loyalty.points') }} {{ $t('loyalty.per') }} {{ method.currency_amount || 1 }} {{ currency }} {{ $t('loyalty.order_amount') }}
                                            </template>
                                            <template v-else>
                                                {{ method.points }} {{ $t('loyalty.points') }} {{ $t('loyalty.per_visit') }}
                                            </template>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Points Cap -->
                            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Max Points Cap</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                                         :class="method.max_points ? 'bg-amber-50' : 'bg-gray-100'">
                                        <svg class="w-5 h-5" :class="method.max_points ? 'text-amber-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900">
                                            {{ method.max_points ? method.max_points + ' pts' : 'No cap' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ method.max_points ? 'Maximum points per transaction' : 'Unlimited earning per transaction' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Edit CTA -->
                        <button
                            @click="openModal(method)"
                            class="mt-6 w-full py-3.5 rounded-2xl bg-gradient-to-r from-primary to-purple-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ $t('loyalty.edit_reward') }}
                        </button>
                    </div>
                </div>

                <!-- Info Notice -->
                <div class="mt-4 flex items-start gap-3 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">
                        Only one earning method is supported per restaurant. Edit the existing configuration to adjust how customers earn points.
                    </p>
                </div>

            </template>

            <!-- ── EMPTY STATE ── -->
            <template v-else>
                <div class="glass-card rounded-3xl p-16 flex flex-col items-center text-center border border-dashed border-gray-200 shadow-inner">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-primary/10 to-purple-100 flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Set Up Your Earning Method</h3>
                    <p class="text-gray-500 text-sm max-w-sm mb-8 leading-relaxed">
                        {{ $t('loyalty.no_methods_configured') }}
                    </p>
                    <button
                        @click="openModal(null)"
                        class="flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-primary to-purple-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ $t('common.add') }} {{ $t('common.method') }}
                    </button>
                </div>
            </template>

        </div>

        <!-- Add/Edit Modal -->
        <Modal
            :show="showModal"
            @close="closeModal"
            :title="editingMethod
                ? ($t('loyalty.edit_reward') || 'Edit Earning Method')
                : ($t('common.add') + ' ' + $t('common.method'))"
            size="7xl"
        >
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
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import CardDesigner from './CardDesigner.vue';

const props = withDefaults(defineProps<{
    methods: any;
    settings?: any;
    filters?: any;
}>(), {
    methods: () => ({ data: [] }),
    settings: () => ({}),
    filters: () => ({})
});

const { locale, t } = useI18n();
const route = (window as any).route;
const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const isRtl = computed(() => (page.props.isRtl as boolean));

// Single method — always the first (and only) entry
const method = computed(() => props.methods?.data?.[0] ?? null);

const showModal = ref(false);
const editingMethod = ref<any>(null);

const getLocaleName = (name: any) => {
    if (typeof name === 'string') {
        if (name === 'Points per Spend') return t('loyalty.points_per_spend') || name;
        if (name === 'Points per Visit') return t('loyalty.points_per_visit') || name;
        return name;
    }
    if (!name) return '';
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
    return icons[type] || icons['order_total'];
};

const openModal = (m: any = null) => {
    editingMethod.value = m;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingMethod.value = null;
    router.reload({ only: ['methods', 'settings'] });
};

const form = useForm({});

const deleteMethod = (m: any) => {
    if (confirm(t('common.confirm_delete'))) {
        form.delete(route('loyalty.earning-methods.destroy', m.id));
    }
};
</script>
