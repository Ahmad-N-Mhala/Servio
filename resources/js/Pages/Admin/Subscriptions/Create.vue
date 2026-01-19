<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign Subscription
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <form @submit.prevent="submit">
                        <!-- Restaurant -->
                        <div class="mb-4">
                            <Select
                                v-model="form.restaurant_id"
                                label="Restaurant *"
                                :options="restaurantOptions"
                                placeholder="Select Restaurant"
                                :error="form.errors.restaurant_id"
                            />
                        </div>

                        <!-- Plan -->
                        <div class="mb-4">
                            <Select
                                v-model="form.plan_id"
                                label="Subscription Plan *"
                                :options="planOptions"
                                placeholder="Select Plan"
                                :error="form.errors.plan_id"
                            />
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <Input
                                v-model="form.starts_at"
                                label="Start Date *"
                                type="date"
                                :error="form.errors.starts_at"
                            />
                        </div>

                        <!-- End Date -->
                        <div class="mb-4">
                            <Input
                                v-model="form.ends_at"
                                label="End Date (Optional)"
                                type="date"
                                :error="form.errors.ends_at"
                            />
                            <p class="text-xs text-gray-500 mt-1">Leave blank for ongoing subscription</p>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <Select
                                v-model="form.status"
                                :label="$t('common.status') + ' *'"
                                :options="statusOptions"
                                :error="form.errors.status"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <Link :href="route('admin.subscriptions.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">{{ $t('common.cancel') }}</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                Assign Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';

const { t } = useI18n();

const props = defineProps<{
    restaurants: Array<any>;
    plans: Array<any>;
}>();

const route = (window as any).route;

const form = useForm({
    restaurant_id: '',
    plan_id: '',
    starts_at: new Date().toISOString().split('T')[0],
    ends_at: '',
    status: 'active',
});

const restaurantOptions = computed(() => {
    return props.restaurants.map(r => ({ label: r.name, value: r.id }));
});

const planOptions = computed(() => {
    return props.plans.map(p => ({ label: `${p.name} - $${p.price_monthly}/month`, value: p.id }));
});

const statusOptions = computed(() => [
    { label: t('common.active'), value: 'active' },
    { label: 'Cancelled', value: 'cancelled' },
    { label: 'Expired', value: 'expired' },
]);

const submit = () => {
    form.post(route('admin.subscriptions.store'));
};
</script>
