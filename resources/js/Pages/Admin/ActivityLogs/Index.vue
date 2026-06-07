<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">{{ $t('activity_logs.title') }}</h1>
            </div>

            <!-- Date Range Picker Filter -->
            <DateRangePicker 
                :initial-start-date="startDate"
                :initial-end-date="endDate"
                @update="onDateRangeUpdate"
            />

            <Table 
                :columns="columns"
                :data="logs.data"
                :pagination="logs"
                v-model:search="search"
                :title="$t('activity_logs.title')"
            >
                <template #header-actions>
                    <div class="flex items-center gap-3">
                        <div class="w-48">
                            <Select
                                v-model="actionFilter"
                                :options="actionOptions"
                                :placeholder="$t('activity_logs.filter_action')"
                                class="text-sm"
                            />
                        </div>
                        <div class="w-48">
                            <Select
                                v-model="restaurantFilter"
                                :options="restaurantOptions"
                                :placeholder="$t('activity_logs.filter_restaurant')"
                                class="text-sm"
                            />
                        </div>
                    </div>
                </template>

                <!-- User Email Cell -->
                <template #cell-user_email="{ row }">
                     <div class="flex flex-col">
                         <span class="text-sm text-gray-900 font-medium">{{ row.user_email }}</span>
                         <span v-if="row.target_email" class="text-xs text-gray-400 mt-0.5">
                             {{ $t('activity_logs.target_user') || 'Target' }}: {{ row.target_email }}
                         </span>
                     </div>
                </template>

                <!-- Restaurant Name Cell -->
                <template #cell-restaurant_name="{ row }">
                     <span class="text-sm text-gray-500">{{ row.restaurant_name }}</span>
                </template>

                <!-- Action Cell -->
                <template #cell-action="{ row }">
                     <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                         {{ row.action }}
                     </span>
                </template>

                <!-- Old Value Cell -->
                <template #cell-old_value="{ row }">
                    <div class="space-y-1 max-w-xs text-xs text-gray-500 max-h-40 overflow-y-auto pr-1">
                        <div v-for="(change, field) in row.changes" :key="field" class="flex flex-col py-0.5 border-b border-gray-100 last:border-0">
                            <span class="font-semibold text-gray-600">{{ formatLogKey(field) }}:</span>
                            <span class="break-all text-red-500 line-through pl-2">{{ formatLogValue(field, change.old) }}</span>
                        </div>
                        <div v-if="!row.changes || Object.keys(row.changes).length === 0" class="text-gray-400 italic">
                            {{ $t('activity_logs.no_changes') }}
                        </div>
                    </div>
                </template>

                <!-- New Value Cell -->
                <template #cell-new_value="{ row }">
                    <div class="space-y-1 max-w-xs text-xs text-gray-900 max-h-40 overflow-y-auto pr-1">
                        <div v-for="(change, field) in row.changes" :key="field" class="flex flex-col py-0.5 border-b border-gray-100 last:border-0">
                            <span class="font-semibold text-gray-700">{{ formatLogKey(field) }}:</span>
                            <span class="break-all text-green-600 font-medium pl-2">{{ formatLogValue(field, change.new) }}</span>
                        </div>
                        <div v-if="!row.changes || Object.keys(row.changes).length === 0" class="text-gray-400 italic">
                            {{ $t('activity_logs.no_changes') }}
                        </div>
                    </div>
                </template>

                <!-- UAE Timestamp Cell -->
                <template #cell-uae_datetime="{ row }">
                    <span class="text-sm text-gray-500 font-mono">{{ row.uae_datetime }}</span>
                </template>
            </Table>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import Table from '@/Components/Table.vue';
import Select from '@/Components/Select.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
// @ts-ignore
import debounce from 'lodash/debounce';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    logs: {
        data: any[];
        links: any[];
    };
    restaurants: Array<{ id: string; name: string }>;
    actionTypes: string[];
    filters: {
        search?: string;
        restaurant_id?: string;
        action_type?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const search = ref(props.filters.search || '');
const restaurantFilter = ref(props.filters.restaurant_id || '');
const actionFilter = ref(props.filters.action_type || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const columns = computed(() => [
    { key: 'user_email', label: t('activity_logs.user_email'), sortable: false },
    { key: 'restaurant_name', label: t('activity_logs.restaurant_name'), sortable: false },
    { key: 'action', label: t('activity_logs.action'), sortable: false },
    { key: 'old_value', label: t('activity_logs.old_value'), sortable: false },
    { key: 'new_value', label: t('activity_logs.new_value'), sortable: false },
    { key: 'uae_datetime', label: t('activity_logs.timestamp'), sortable: false },
]);

const restaurantOptions = computed(() => {
    return [
        { label: t('activity_logs.all_restaurants'), value: '' },
        ...props.restaurants.map(r => ({ label: r.name, value: r.id }))
    ];
});

const actionOptions = computed(() => {
    return [
        { label: t('activity_logs.all_actions'), value: '' },
        ...props.actionTypes.map(act => ({ label: act, value: act }))
    ];
});

const route = (window as any).route;

const handleFilter = debounce(() => {
    router.get(route('admin.activity-logs.index'), {
        search: search.value,
        restaurant_id: restaurantFilter.value,
        action_type: actionFilter.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true });
}, 300);

watch([search, restaurantFilter, actionFilter, startDate, endDate], handleFilter);

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    startDate.value = range.startDate;
    endDate.value = range.endDate;
};

const formatLogKey = (key: string) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const formatLogValue = (key: string, value: any) => {
    if (value === null || value === undefined) return 'N/A';
    if (typeof value === 'boolean') {
        return value ? t('common.active') || 'Active' : t('common.inactive') || 'Inactive';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return String(value);
};
</script>
