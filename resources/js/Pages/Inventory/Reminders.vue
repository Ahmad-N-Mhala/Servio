<template>
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('inventory.reminders', 'Inventory Reminders') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Low Stock Alerts -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex flex-col h-full">
                        <div class="p-6 bg-rose-50 border-b border-rose-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-rose-800 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ $t('inventory.low_stock_alerts') }}
                            </h3>
                            <span class="bg-rose-200 text-rose-800 py-1 px-3 rounded-full text-xs font-bold">
                                {{ $t('inventory.low_stock_items_count', { count: lowStockIngredients.length }) }}
                            </span>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto max-h-[600px] space-y-4">
                            <div v-if="lowStockIngredients.length === 0" class="text-center text-gray-500 py-8">
                                <p>{{ $t('inventory.no_low_stock_msg') }}</p>
                            </div>
                            <div v-for="item in lowStockIngredients" :key="item.id" class="p-4 border border-rose-100 rounded-xl bg-white shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                                <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ getLocaleName(item.name) }}</h4>
                                        <p class="text-xs text-gray-500">{{ $t('inventory.reorder_level') }}: {{ item.reorder_level }} {{ item.unit }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-2xl font-bold text-rose-600">{{ item.current_stock }}</span>
                                        <span class="text-xs text-gray-400">{{ item.unit }}</span>
                                    </div>
                                </div>
                                <!-- Progress Bar for Balance -->
                                <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                                    <div 
                                        class="bg-rose-500 h-2 rounded-full transition-all duration-500" 
                                        :style="{ width: Math.min((item.current_stock / item.reorder_level) * 100, 100) + '%' }"
                                    ></div>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <Link :href="route('inventory.index')" class="text-xs text-rose-600 hover:text-rose-800 font-medium">
                                        {{ $t('inventory.restock_now') }} &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiry Alerts -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex flex-col h-full">
                        <div class="p-6 bg-amber-50 border-b border-amber-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $t('inventory.expiring_soon') }}
                            </h3>
                             <span class="bg-amber-200 text-amber-800 py-1 px-3 rounded-full text-xs font-bold">
                                {{ $t('inventory.expiring_batches_count', { count: expiringBatches.length }) }}
                            </span>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto max-h-[600px] space-y-4">
                            <div v-if="expiringBatches.length === 0" class="text-center text-gray-500 py-8">
                                <p>{{ $t('inventory.no_expiring_msg') }}</p>
                            </div>
                            <div v-for="batch in expiringBatches" :key="batch.id" class="p-4 border border-amber-100 rounded-xl bg-white shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 w-1 bg-amber-500"></div>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ getLocaleName(batch.ingredient_name) }}</h4>
                                        <p class="text-xs text-gray-500">{{ $t('inventory.batch_label') }}: {{ batch.batch_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="block font-bold" :class="batch.days_remaining <= 0 ? 'text-red-600' : 'text-amber-600'">
                                            {{ batch.days_remaining <= 0 ? $t('inventory.expired') : $t('inventory.days_left', { days: batch.days_remaining }) }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ batch.expiration_date }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm bg-gray-50 p-2 rounded-lg">
                                    <span class="text-gray-600">{{ $t('inventory.quantity_remaining') }}:</span>
                                    <span class="font-bold text-gray-800">{{ batch.quantity_remaining }} {{ batch.unit }}</span>
                                </div>
                                 <div class="mt-2 flex justify-end">
                                    <Link :href="route('waste.index')" class="text-xs text-amber-600 hover:text-amber-800 font-medium">
                                        {{ $t('inventory.record_waste') }} &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    lowStockIngredients: any[];
    expiringBatches: any[];
}>();

const { locale } = useI18n();
const route = (window as any).route;

const getLocaleName = (name: any) => {
    if (typeof name === 'object' && name !== null) {
        return name[locale.value] || name['en'] || 'Unknown';
    }
    return name;
};
</script>
