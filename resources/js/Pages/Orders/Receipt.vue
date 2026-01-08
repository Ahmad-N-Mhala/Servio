<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col items-center justify-center p-4 print:p-0 print:bg-white print:h-auto print:min-h-0">
        <!-- Controls (Hidden in Print) -->
        <div class="mb-6 print:hidden flex gap-4 no-print">
            <button 
                @click="print" 
                class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark focus:bg-primary-dark active:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" />
                </svg>
                {{ $t('common.print') }} {{ $t('receipt.receipt') || 'Receipt' }}
            </button>
            <button 
                @click="close" 
                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
            >
                {{ $t('common.close') }}
            </button>
        </div>

        <!-- Receipt Content -->
        <div class="receipt-wrapper bg-white print:w-full print:absolute print:top-0 print:left-0">
            <ReceiptPreview 
                :template="template" 
                :order="orderComputed" 
                :logo="logo" 
                :restaurant-name="restaurantName"
                :google-map-location="props.google_map_location"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';

useI18n();

const props = defineProps<{
    order: any;
    template: any;
    logo?: string;
    restaurantName?: string;
    google_map_location?: string;
}>();

// Ensure order items are mapped correctly if structure differs
const orderComputed = computed(() => {
    // If order items structure needs mapping for ReceiptPreview, do it here.
    // ReceiptPreview expects order to have: customer_name, order_number, total, subtotal, discount, tax, currency, items: [{quantity, name, price}]
    // The backend provides exactly this structure (items relation has menuItem). 
    // Wait, backend Order model `items` has `menu_item_id`. But we loaded `items.menuItem`.
    // ReceiptPreview expects `item.name`. But `Order` -> `items` -> `menuItem`.
    // Let's verify ReceiptPreview logic.
    return props.order;
});

const print = () => {
    window.print();
};

const close = () => {
    window.close();
};

onMounted(() => {
    // Optional: Auto print if opened in popup
    // setTimeout(() => window.print(), 500);
});
</script>

<style>
@media print {
    @page {
        margin: 0;
        size: auto;
    }
    body {
        background-color: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .no-print {
        display: none !important;
    }
    /* Ensure receipt wrapper takes priority */
    .receipt-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        overflow: visible !important;
    }
    
    /* Hide everything else */
    body > :not(.receipt-wrapper) {
        display: none !important;
    }
}
</style>
