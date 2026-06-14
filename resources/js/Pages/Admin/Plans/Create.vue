<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-2xl text-slate-800 dark:text-slate-100 leading-tight">Create Subscription Plan</h2>
                <Link :href="route('admin.plans.index')" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Plans
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- General Details -->
                    <Card title="General Details" subtitle="Enter the core information and bilingual names for the plan.">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Input
                                v-model="form.name_en"
                                label="Name (English)"
                                :error="form.errors.name_en"
                                required
                                placeholder="e.g. Professional Plan"
                            />

                            <Input
                                v-model="form.name_ar"
                                label="Name (Arabic)"
                                :error="form.errors.name_ar"
                                required
                                dir="rtl"
                                placeholder="الخطة الاحترافية"
                            />

                            <div class="md:col-span-2">
                                <Input
                                    v-model="form.slug"
                                    label="Slug (Unique Key)"
                                    :error="form.errors.slug"
                                    required
                                    placeholder="e.g. professional-plan"
                                    help="Used for system configuration and translation keys (e.g. plans.slug_name)"
                                    @input="isSlugModified = true"
                                />
                            </div>

                            <Input
                                v-model="form.description_en"
                                type="textarea"
                                label="Description (English)"
                                :error="form.errors.description_en"
                                placeholder="Provide brief summary of this plan..."
                                rows="3"
                            />

                            <Input
                                v-model="form.description_ar"
                                type="textarea"
                                label="Description (Arabic)"
                                :error="form.errors.description_ar"
                                dir="rtl"
                                placeholder="اكتب وصفاً موجزاً لهذه الخطة..."
                                rows="3"
                            />
                        </div>
                    </Card>

                    <!-- Pricing & Display Order -->
                    <Card title="Pricing & Display" subtitle="Configure plan pricing options and priority.">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <Input
                                v-model="form.price_monthly"
                                type="number"
                                step="0.01"
                                :label="`Monthly Price (${currency})`"
                                :error="form.errors.price_monthly"
                                required
                                placeholder="0.00"
                            />

                            <Input
                                v-model="form.price_yearly"
                                type="number"
                                step="0.01"
                                :label="`Yearly Price (${currency})`"
                                :error="form.errors.price_yearly"
                                required
                                placeholder="0.00"
                            />

                            <Input
                                v-model="form.order"
                                type="number"
                                label="Display Order"
                                :error="form.errors.order"
                                placeholder="0"
                                help="Lower numbers appear first on the landing page"
                            />
                        </div>
                    </Card>

                    <!-- Limits -->
                    <Card title="System Limits" subtitle="Define strict usage boundaries for restaurants on this plan.">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <Input
                                v-model="form.max_restaurants"
                                type="number"
                                label="Max Restaurants"
                                :error="form.errors.max_restaurants"
                                placeholder="Unlimited if empty"
                            />

                            <Input
                                v-model="form.max_users"
                                type="number"
                                label="Max Users"
                                :error="form.errors.max_users"
                                placeholder="Unlimited if empty"
                            />

                            <Input
                                v-model="form.max_orders_per_month"
                                type="number"
                                label="Max Orders/Month"
                                :error="form.errors.max_orders_per_month"
                                placeholder="Unlimited if empty"
                            />
                        </div>
                    </Card>

                    <!-- Technical Capabilities -->
                    <Card title="Technical Features" subtitle="Select the modules and technical capabilities enabled for this subscription plan.">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="(label, key) in availableFeatures" 
                                :key="key" 
                                @click="toggleFeature(key)"
                                :class="[
                                    'cursor-pointer rounded-2xl border p-5 transition-all duration-300 relative overflow-hidden group flex flex-col justify-between h-36 select-none',
                                    form.enabled_features.includes(key)
                                        ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-md shadow-primary/5'
                                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
                                ]"
                            >
                                <!-- Selection indicator -->
                                <div class="absolute top-4 right-4 flex items-center justify-center">
                                    <div 
                                        :class="[
                                            'w-5 h-5 rounded-full border flex items-center justify-center transition-all duration-300',
                                            form.enabled_features.includes(key)
                                                ? 'bg-primary border-primary text-white scale-110'
                                                : 'border-slate-300 dark:border-slate-600 bg-transparent'
                                        ]"
                                    >
                                        <svg v-if="form.enabled_features.includes(key)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Icon -->
                                <div class="flex items-start gap-4">
                                    <div 
                                        :class="[
                                            'p-2.5 rounded-xl transition-all duration-300',
                                            form.enabled_features.includes(key)
                                                ? 'bg-primary/15 text-primary'
                                                : 'bg-slate-100 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200 dark:group-hover:bg-slate-700'
                                        ]"
                                    >
                                        <svg v-if="key === 'pos'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else-if="key === 'inventory'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <svg v-else-if="key === 'loyalty'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                        </svg>
                                        <svg v-else-if="key === 'marketing'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                        </svg>
                                        <svg v-else-if="key === 'feedback'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <svg v-else-if="key === 'delivery'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zm-2-4h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-9-4H4V5a2 2 0 00-2-2h10a2 2 0 002 2v8m-1 4h-4" />
                                        </svg>
                                        <svg v-else-if="key === 'analytics'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <svg v-else-if="key === 'kds'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else-if="key === 'qr_ordering'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mt-2 text-left">
                                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ getFeatureTitle(key) }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-normal">{{ getFeatureDescription(key) }}</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Public Features list -->
                    <Card title="Public Features List" subtitle="List of feature bullet points shown on the public landing page (one feature per line).">
                        <div class="space-y-4">
                            <textarea 
                                :value="form.features.join('\n')" 
                                @input="form.features = ($event.target as HTMLTextAreaElement).value.split('\n').filter(s => s.trim())"
                                rows="5" 
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 px-4 transition-all hover:border-slate-300 dark:hover:border-slate-600 dark:text-slate-200"
                                placeholder="Examples:&#10;1 Restaurant&#10;Unlimited Staff&#10;Email Support"
                            ></textarea>
                            <div v-if="form.errors.features" class="text-rose-500 text-xs mt-1">{{ form.errors.features }}</div>
                        </div>
                    </Card>

                    <!-- Active state & Actions -->
                    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
                        <label class="flex items-center cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" v-model="form.is_active" class="sr-only">
                                <div :class="['w-11 h-6 rounded-full transition-colors duration-300', form.is_active ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700']"></div>
                                <div :class="['absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-300', form.is_active ? 'translate-x-5' : '']"></div>
                            </div>
                            <span class="ml-3 font-semibold text-sm text-slate-700 dark:text-slate-300">Active Status</span>
                        </label>

                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.plans.index')" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 font-bold transition-all text-sm">
                                {{ $t('common.cancel') }}
                            </Link>
                            <Button type="submit" :loading="form.processing">
                                Create Plan
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

const props = defineProps<{
    availableFeatures: Record<string, string>;
}>();

const form = useForm({
    name_en: '',
    name_ar: '',
    slug: '',
    description_en: '',
    description_ar: '',
    price_monthly: 0,
    price_yearly: 0,
    max_restaurants: null as number | null,
    max_users: null as number | null,
    max_orders_per_month: null as number | null,
    order: 0,
    is_active: true,
    features: [] as string[],
    enabled_features: [] as string[],
});

const isSlugModified = ref(false);

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start
        .replace(/-+$/, '');            // Trim - from end
};

watch(() => form.name_en, (newVal) => {
    if (!isSlugModified.value) {
        form.slug = slugify(newVal);
    }
});

watch(() => form.slug, (newVal) => {
    if (!newVal) {
        isSlugModified.value = false;
    }
});

const getLimitFeatureString = (type: 'max_restaurants' | 'max_users' | 'max_orders_per_month', value: any) => {
    if (value !== null && value !== undefined && value !== '') {
        const num = Number(value);
        if (type === 'max_restaurants') {
            if (num === 1) return 'plan_features.one_restaurant';
            if (num === 3) return 'plan_features.three_restaurants';
            return `plan_features.restaurants_limit:${num}`;
        }
        if (type === 'max_users') {
            if (num === 5) return 'plan_features.staff_limit_5';
            return `plan_features.users_limit:${num}`;
        }
        if (type === 'max_orders_per_month') {
            return `plan_features.orders_limit:${num}`;
        }
    } else {
        if (type === 'max_restaurants') return 'plan_features.unlimited_restaurants';
        if (type === 'max_users') return 'plan_features.unlimited_staff';
        return 'plan_features.unlimited_orders';
    }
    return '';
};

const lastLimitFeatures = ref({
    max_restaurants: '',
    max_users: '',
    max_orders_per_month: ''
});

const syncLimitFeature = (type: 'max_restaurants' | 'max_users' | 'max_orders_per_month', value: any) => {
    const newValue = getLimitFeatureString(type, value);
    const oldVal = lastLimitFeatures.value[type];
    
    if (oldVal === newValue) return;

    const fIndex = form.features.indexOf(oldVal);
    if (fIndex !== -1) {
        if (newValue) {
            form.features[fIndex] = newValue;
        } else {
            form.features.splice(fIndex, 1);
        }
    } else {
        if (newValue && !form.features.includes(newValue)) {
            form.features.push(newValue);
        }
    }

    lastLimitFeatures.value[type] = newValue;
};

// Sync system limit inputs into the public features list
watch(() => form.max_restaurants, (newVal) => {
    syncLimitFeature('max_restaurants', newVal);
}, { immediate: true });

watch(() => form.max_users, (newVal) => {
    syncLimitFeature('max_users', newVal);
}, { immediate: true });

watch(() => form.max_orders_per_month, (newVal) => {
    syncLimitFeature('max_orders_per_month', newVal);
}, { immediate: true });

const featureDetails = {
    pos: {
        title: 'POS System',
        description: 'Point of sale register, billing, and order terminal.'
    },
    inventory: {
        title: 'Inventory & Waste',
        description: 'Ingredient stock levels, wastage tracking, and suppliers.'
    },
    loyalty: {
        title: 'Loyalty Program',
        description: 'Manage customers points, tiers, and rewards system.'
    },
    marketing: {
        title: 'Marketing Automation',
        description: 'Automated SMS, emails, coupons, and client list reach.'
    },
    feedback: {
        title: 'Customer Feedback',
        description: 'Gather tableside reviews and food quality surveys.'
    },
    delivery: {
        title: 'Delivery Integrations',
        description: 'Hub for aggregators (Talabat, Deliveroo) and logistics.'
    },
    analytics: {
        title: 'Advanced Analytics',
        description: 'Deeper dashboards, performance stats, and CSV reports.'
    },
    kds: {
        title: 'Kitchen Display',
        description: 'Live order tracking screens for the kitchen crew.'
    },
    qr_ordering: {
        title: 'QR Menu & Table',
        description: 'Tableside QR menu codes for self-ordering by customers.'
    }
};

const getFeatureTitle = (key: string) => {
    return (featureDetails as any)[key]?.title || props.availableFeatures[key] || key;
};

const getFeatureDescription = (key: string) => {
    return (featureDetails as any)[key]?.description || 'Toggle capability for the tenant subscription.';
};

const toggleFeature = (key: string) => {
    const index = form.enabled_features.indexOf(key);
    const featureKey = `plan_features.${key}`;
    
    if (index === -1) {
        form.enabled_features.push(key);
        if (!form.features.includes(featureKey)) {
            form.features.push(featureKey);
        }
    } else {
        form.enabled_features.splice(index, 1);
        const fIndex = form.features.indexOf(featureKey);
        if (fIndex !== -1) {
            form.features.splice(fIndex, 1);
        }
    }
};

const route = (window as any).route;

const submit = () => {
    form.post(route('admin.plans.store'));
};
</script>
