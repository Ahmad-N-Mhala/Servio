<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <div class="relative">
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Search plans..." 
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary w-full sm:w-64"
                        >
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                    <Link :href="route('admin.plans.create')" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-hover shadow-lg shadow-primary/30 text-center whitespace-nowrap">
                        Create New Plan
                    </Link>
                </div>
            </div>

            <div v-if="plansData.length === 0" class="text-center py-12">
                <p class="text-gray-500">No plans found.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cards content remains same -->
                <div v-for="plan in plansData" :key="plan.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-full uppercase tracking-wide">{{ plan.slug }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900">{{ currency }} {{ convertPrice(plan.price_monthly) }}</p>
                            <p class="text-xs text-gray-400">/month</p>
                        </div>
                    </div>
                    
                    <div class="flex-grow space-y-2 mb-6">
                        <p class="text-sm text-gray-600 font-medium">Features:</p>
                        <ul v-if="planFeatures(plan).length > 0" class="text-sm text-gray-500 space-y-1">
                            <li v-for="(feature, idx) in planFeatures(plan).slice(0, 4)" :key="idx" class="flex items-center gap-2">
                                 <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ feature }}
                            </li>
                             <li v-if="planFeatures(plan).length > 4">
                                <button 
                                    @click="openPlanDetails(plan)"
                                    class="text-xs text-primary hover:text-primary-hover hover:underline pl-6 font-medium focus:outline-none"
                                >
                                    + {{ planFeatures(plan).length - 4 }} more coverage
                                </button>
                             </li>
                        </ul>
                        <p v-else class="text-sm text-gray-400 italic">No features listed</p>
                    </div>
                    
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-50 mt-auto">
                        <Link :href="route('admin.plans.edit', plan.id)" class="flex-1 bg-gray-50 text-gray-700 hover:bg-gray-100 py-2 rounded-lg text-sm font-semibold transition-colors text-center">
                            Edit
                        </Link>
                        <button @click="deletePlan(plan)" class="flex-1 bg-red-50 text-red-600 hover:bg-red-100 py-2 rounded-lg text-sm font-semibold transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Details Modal -->
        <Modal :show="showPlanModal" @close="closePlanModal">
            <div class="p-6" v-if="selectedPlan">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ selectedPlan.name }} Details</h2>
                        <span class="text-sm font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full uppercase tracking-wide mt-2 inline-block">{{ selectedPlan.slug }}</span>
                    </div>
                    <button @click="closePlanModal" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="bg-primary-50 rounded-xl p-6 text-center">
                        <p class="text-4xl font-extrabold text-primary">{{ currency }} {{ convertPrice(selectedPlan.price_monthly) }}</p>
                        <p class="text-sm text-primary-600 font-medium">per month</p>
                        <div class="mt-2 text-sm text-gray-500">
                            or {{ currency }} {{ convertPrice(selectedPlan.price_yearly) }} / year
                        </div>
                    </div>

                    <div v-if="selectedPlan.description">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-600">{{ selectedPlan.description }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Included Features</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="(feature, idx) in planFeatures(selectedPlan)" :key="idx" class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/50">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-sm text-gray-700 font-medium">{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button @click="closePlanModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors">Close</button>
                    <Link :href="route('admin.plans.edit', selectedPlan.id)" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-hover shadow-lg shadow-primary/30 transition-colors">
                        Edit Plan
                    </Link>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Modal from '@/Components/Modal.vue';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const currencyRate = computed(() => (page.props.current_restaurant as any)?.currency_rate || 1);

const convertPrice = (amount: any) => {
    return (Number(amount || 0) * currencyRate.value).toFixed(2);
};

const props = defineProps<{
    plans: any;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const showPlanModal = ref(false);
const selectedPlan = ref<any>(null);

const openPlanDetails = (plan: any) => {
    selectedPlan.value = plan;
    showPlanModal.value = true;
};

const closePlanModal = () => {
    showPlanModal.value = false;
    selectedPlan.value = null;
};

watch(search, debounce((value: string) => {
    router.get(route('admin.plans.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const route = (window as any).route;

const deletePlan = (plan: any) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        router.delete(route('admin.plans.destroy', plan.id));
    }
};

// Handle both paginated and non-paginated data
const plansData = computed(() => {
    if (!props.plans) return [];
    // Check if it's Laravel pagination object
    if (props.plans.data && Array.isArray(props.plans.data)) {
        return props.plans.data;
    }
    // Otherwise assume it's a plain array
    return Array.isArray(props.plans) ? props.plans : [];
});

// Safely get features array
const planFeatures = (plan: any) => {
    if (!plan.features) return [];
    if (Array.isArray(plan.features)) return plan.features;
    // If features is a JSON string, parse it
    if (typeof plan.features === 'string') {
        try {
            const parsed = JSON.parse(plan.features);
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }
    return [];
};
</script>
