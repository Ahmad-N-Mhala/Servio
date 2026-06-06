<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('loyalty.sms_logs') || 'SMS & OTP Logs' }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('loyalty.sms_description') || 'Track all loyalty verification messages and SMS history' }}</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('loyalty.index')" class="btn-secondary">
                        {{ $t('common.back') }}
                    </Link>
                </div>
            </div>

            <!-- Logs Table -->
            <Table
                :title="$t('loyalty.comm_history') || 'Communication History'"
                :columns="tableColumns"
                :data="logs.data || []"
                :pagination="logs"
                :emptyMessage="$t('charts.no_data')"
            >
                <template #cell-recipient="{ row }">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ row.recipient }}</span>
                    </div>
                </template>
                <template #cell-type="{ row }">
                    <span class="text-xs font-bold uppercase px-2 py-1 rounded bg-gray-100 text-gray-600">
                        {{ row.type }}
                    </span>
                </template>
                <template #cell-message="{ row }">
                    <p class="text-sm text-gray-600 max-w-xs truncate" :title="row.message">
                        {{ row.message }}
                    </p>
                </template>
                <template #cell-status="{ row }">
                    <span 
                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full cursor-help relative group/tooltip"
                        :class="{
                            'bg-green-100 text-green-800': row.status === 'sent',
                            'bg-red-100 text-red-800': row.status === 'failed',
                            'bg-yellow-100 text-yellow-800': row.status === 'pending'
                        }"
                    >
                        {{ $t('common.' + row.status) || row.status.toUpperCase() }}
                        <div v-if="row.status === 'failed' && row.error_message" 
                             class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg hidden group-hover/tooltip:block z-50 text-center">
                            {{ row.error_message }}
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </span>
                </template>
                <template #cell-sent_at="{ row }">
                    <span class="text-sm text-gray-500">
                        {{ formatDateTime(row.sent_at) }}
                    </span>
                </template>
            </Table>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Table from '@/Components/Table.vue';
import { formatDateTime } from '@/Utils/dateHelper';

const { locale, t } = useI18n();

const props = defineProps<{
    logs: any;
}>();

const route = (window as any).route;

const tableColumns = computed(() => [
    { key: 'recipient', label: t('loyalty.recipient') || 'Recipient', sortable: true },
    { key: 'type', label: t('loyalty.msg_type') || 'Message Type', sortable: true },
    { key: 'message', label: t('loyalty.content') || 'Content', sortable: true },
    { key: 'status', label: t('common.status'), sortable: true },
    { key: 'sent_at', label: t('common.sent_at') || 'Sent At', sortable: true }
]);
</script>
