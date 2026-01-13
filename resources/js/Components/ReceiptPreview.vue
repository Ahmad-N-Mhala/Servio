<template>
    <div :class="['receipt-preview', `font-${template.font_size}`]" :style="{ width: template.paper_width + 'mm' }" :dir="template.receipt_language === 'ar' ? 'rtl' : 'ltr'">
        <!-- Header -->
        <div :class="`text-${template.header_alignment}`" class="mb-4">
            <div v-if="template.show_logo" class="mb-2 flex justify-center">
                <img v-if="logo" :src="logo" alt="Restaurant Logo" class="max-w-[80px] max-h-[80px] object-contain grayscale" />
                <div v-else class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            
            <h2 v-if="template.show_restaurant_name" class="font-bold text-lg">{{ restaurantName || 'Restaurant Name' }}</h2>
            <p v-if="template.header_text" class="text-sm mt-1 whitespace-pre-wrap">{{ template.header_text }}</p>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Order Info -->
        <div class="text-sm space-y-1 mb-3">
            <div v-if="template.show_order_number">
                <div class="flex justify-between font-bold text-base border-b border-dashed border-gray-300 pb-1">
                    <span class="font-semibold">{{ template.show_order_number_label || 'Order #:' }}</span>
                    <span>{{ displayOrder.number }}</span>
                </div>
            </div>
            <div v-if="template.show_date_time" class="flex justify-between">
                <span class="font-semibold">{{ rt('common.date', 'Date') }}:</span>
                <span>{{ displayOrder.date }}</span>
            </div>
            <div v-if="template.show_table_number && displayOrder.table" class="flex justify-between">
                <span class="font-semibold">{{ rt('nav.tables', 'Table') }}:</span>
                <span>{{ displayOrder.table }}</span>
            </div>
            <div v-if="template.show_customer_name" class="flex justify-between">
                <span class="font-semibold">{{ rt('orders.customer', 'Customer') }}:</span>
                <span>{{ displayOrder.customer }}</span>
            </div>
            <div v-if="template.show_server_name" class="flex justify-between">
                <span class="font-semibold">{{ rt('reports.waiter', 'Server') }}:</span>
                <span>{{ displayOrder.server }}</span>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Items -->
        <div class="mb-3">
            <table class="w-full text-xs" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <thead>
                    <tr class="border-b-2 border-dashed border-gray-400">
                        <th class="text-left py-1" :style="{ width: colWidths.item }">{{ rt('common.items', 'ITEM') }}</th>
                        <th class="text-center py-1" :style="{ width: colWidths.qty }">{{ rt('common.qty', 'Qty') }}</th>
                        <th class="text-right py-1" :style="{ width: colWidths.price }">{{ rt('common.price', 'PRICE') }}</th>
                        <th class="text-right py-1" :style="{ width: colWidths.total }">{{ rt('common.total', 'TOTAL') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr v-for="(item, index) in displayOrder.items" :key="index">
                        <td class="py-1 pr-1 break-words whitespace-normal" style="vertical-align: top;">
                             <div class="font-medium leading-tight">{{ item.name }}</div>
                             <div v-if="template.show_item_notes && item.notes" class="text-xs text-gray-600 italic mt-0.5">{{ item.notes }}</div>
                             <div v-if="item.extras && item.extras.length" class="text-[10px] text-gray-500 mt-0.5">
                                <template v-for="(extra, i) in item.extras" :key="i">
                                    <div>+ {{ extra.name || (extra.split ? extra : 'Extra') }}</div>
                                </template>
                             </div>
                        </td>
                        <td class="text-center py-1 whitespace-nowrap" style="vertical-align: top;">{{ item.quantity }}</td>
                        <td class="text-right py-1 whitespace-nowrap" style="vertical-align: top;">{{ formatPrice(item.unit_price) }}</td>
                        <td class="text-right py-1 font-bold whitespace-nowrap" style="vertical-align: top;">{{ formatPrice(item.price) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Totals -->
        <div class="text-sm space-y-1 mb-3">
            <div v-if="template.show_subtotal" class="flex justify-between">
                <span>{{ rt('common.subtotal', 'Subtotal') }}:</span>
                <span>{{ formatPrice(displayOrder.subtotal) }}</span>
            </div>
            <div v-if="template.show_tax && Number(displayOrder.tax) > 0" class="flex justify-between">
                <span>{{ rt('pos.tax', 'Tax') }}:</span>
                <span>{{ formatPrice(displayOrder.tax) }}</span>
            </div>
            <div v-if="template.show_discount && Number(displayOrder.discount) > 0" class="flex justify-between text-red-600">
                <span>{{ rt('pos.discount', 'Discount') }}:</span>
                <span>-{{ formatPrice(displayOrder.discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-400">
                <span>{{ rt('common.total', 'TOTAL') }}:</span>
                <span>{{ formatPrice(displayOrder.total) }} {{ displayOrder.currency }}</span>
            </div>
            <div v-if="template.show_payment_method" class="flex justify-between text-xs pt-1">
                <span>{{ rt('pos.payment_method', 'Payment') }}:</span>
                <span class="font-semibold uppercase">{{ displayOrder.payment_method ? rt('pos.' + displayOrder.payment_method.toLowerCase(), displayOrder.payment_method) : rt('pos.pending', 'Pending') }}</span>
            </div>
        </div>
        
        <!-- Loyalty Points -->
        <div v-if="template.show_loyalty_points && displayOrder.customer_id" class="text-sm space-y-1 mb-3 pt-2 border-t border-dashed border-gray-300">
            <div class="flex justify-between">
                <span>{{ rt('loyalty.points_earned', 'Points Earned') }}:</span>
                <span class="font-bold">+{{ displayOrder.points_earned }}</span>
            </div>
            <div class="flex justify-between">
                <span>{{ rt('loyalty.total_balance', 'New Balance') }}:</span>
                <span class="font-bold">{{ displayOrder.points_balance }}</span>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Footer -->
        <div :class="`text-${template.footer_alignment}`" class="text-sm">
            <p v-if="template.footer_text" class="mb-2 whitespace-pre-wrap">{{ template.footer_text }}</p>
            <p v-if="template.contact_info" class="text-xs text-gray-600 whitespace-pre-wrap">{{ template.contact_info }}</p>
            
            <div v-if="template.show_qr_code && (qrCodeDataUrl || googleMapLocation)" class="mt-3 flex justify-center">
                <!-- Placeholder QR Code or Real one if we implement generation -->
                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center overflow-hidden">
                    <img v-if="qrCodeDataUrl" :src="qrCodeDataUrl" class="w-full h-full object-cover">
                    <svg v-else class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h-2v3h-3v2h5v-5zM13 13h2v2h-2v-2zm2 2h2v2h-2v-2zm2 2h2v2h-2v-2zm0-4h2v2h-2v-2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { get } from 'lodash';

const { messages } = useI18n();

const props = defineProps<{
    template: any;
    logo?: string | null;
    restaurantName?: string;
    order?: any;
    googleMapLocation?: string | null;
}>();

const receiptLocale = computed(() => {
    return props.template?.receipt_language || 'en';
});

// Custom translation helper for receipt language
const rt = (key: string, fallback?: string) => {
    const lang = receiptLocale.value;
    
    // 1. Try exact match in receipt language
    // We use lodash get to access nested keys like 'common.date'
    const translated = get(messages.value[lang], key);
    
    // 2. Fallback to English if missing
    if (!translated) {
        const enTranslated = get(messages.value['en'], key);
        return enTranslated || fallback || key;
    }
    
    return translated;
};

const qrCodeDataUrl = ref<string | null>(null);

const generateQrCode = async () => {
    if (props.googleMapLocation) {
        try {
            // @ts-ignore
            const qrcodeModule = await import('qrcode');
            const toDataURL = qrcodeModule.default?.toDataURL || qrcodeModule.toDataURL;
            if (toDataURL) {
                console.log('Generating QR code for:', props.googleMapLocation);
                qrCodeDataUrl.value = await toDataURL(props.googleMapLocation, { margin: 1, width: 100 });
                console.log('QR Code generated successfully');
            } else {
                console.error('QR Code library missing toDataURL method');
            }
        } catch (e) {
            console.error('QR Gen Error', e);
        }
    } else {
        console.warn('QR Code Generation Skipped: No googleMapLocation provided in props', props);
        qrCodeDataUrl.value = null; 
    }
};

onMounted(() => {
    if (props.template?.show_qr_code) {
        generateQrCode();
    }
});

watch(() => props.googleMapLocation, generateQrCode);
watch(() => props.template?.show_qr_code, (val) => {
    if (val) generateQrCode();
});

const formatPrice = (val: any) => {
    return Number(val || 0).toFixed(2);
};


// ... existing computed properties ...

const colWidths = computed(() => {
    // Hard constraints to prevent breaking the layout
    const MIN_QTY_WIDTH = 13;   // %
    const MIN_PRICE_WIDTH = 20; // %
    const MIN_TOTAL_WIDTH = 25; // %
    const RESERVED_SPACE = MIN_QTY_WIDTH + MIN_PRICE_WIDTH + MIN_TOTAL_WIDTH; // 58%
    
    // Get user preference, default to 40%
    let itemWidth = Number(props.template?.item_name_width || 40);
    
    // Cap item width so it doesn't starve the data columns
    // If the user sets it too high, we silently enforce the limit to keep the receipt readable
    if (itemWidth > (100 - RESERVED_SPACE)) {
        itemWidth = 100 - RESERVED_SPACE;
    }
    
    const remaining = 100 - itemWidth;
    const weightTotal = RESERVED_SPACE;

    return {
        item: `${itemWidth}%`,
        qty: `${(MIN_QTY_WIDTH / weightTotal) * remaining}%`,
        price: `${(MIN_PRICE_WIDTH / weightTotal) * remaining}%`,
        total: `${(MIN_TOTAL_WIDTH / weightTotal) * remaining}%`
    };
});

const displayOrder = computed(() => {
    if (props.order) {
        return {
            id: props.order.id,
            number: props.order.order_number,
            transaction_number: props.order.transaction_number || '-',
            // Update date formatting to use receiptLocale
            date: new Date(props.order.created_at).toLocaleString(receiptLocale.value === 'ar' ? 'ar-AE' : 'en-US', { dateStyle: 'medium', timeStyle: 'short' }),
            table: props.order.table?.name || null,
            customer: props.order.customer_name || rt('common.guest', 'Guest'),
            server: props.order.waiter?.name || '-',
            items: props.order.items.map((item: any) => {
                let name = item.menuItem?.name || item.menu_item?.name || 'Unknown Item';
                const lang = receiptLocale.value; // Use receipt language for item names too

                if (typeof name === 'string' && name.startsWith('{')) {
                    try {
                        const parsed = JSON.parse(name);
                        name = parsed[lang] || parsed['en'] || name;
                    } catch (e) {}
                } else if (typeof name === 'object') {
                    name = name[lang] || name['en'] || 'Unknown';
                }
                
                return {
                    name: name,
                    quantity: item.quantity,
                    unit_price: item.unit_price, 
                    price: item.total_price || (item.quantity * item.unit_price), 
                    notes: item.notes,
                    extras: item.extras 
                };
            }),
            subtotal: props.order.subtotal,
            tax: props.order.tax,
            discount: props.order.discount_amount,
            total: props.order.total,
            payment_method: props.order.payment_method,
            currency: props.order.currency || 'AED',
            qr_code_url: null,
            customer_id: props.order.customer_id,
            points_earned: props.order.points_earned || 0,
            points_balance: props.order.customer?.loyalty_points?.balance ?? 0
        };
    } else {
        // Dummy Data for Preview
        return {
            id: '64f7a8b29c', // Dummy ID
            number: 'ORD-12345',
            transaction_number: props.template.show_order_number ? '1001' : '---',
            date: new Date().toLocaleString(receiptLocale.value === 'ar' ? 'ar-AE' : 'en-US'),
            table: 'T-5',
            customer: 'John Doe',
            server: 'Jane Smith',
            items: [
                { name: 'Double Cheese Burger Special', quantity: 2, unit_price: 25.00, price: 50.00, notes: 'No onions' },
                { name: 'Fries', quantity: 1, unit_price: 15.00, price: 15.00, notes: '' },
                { name: 'Coca Cola', quantity: 2, unit_price: 5.00, price: 10.00, notes: '' },
            ],
            subtotal: 75.00,
            tax: 3.75,
            discount: 5.00,
            total: 73.75,
            payment_method: 'CASH',
            currency: 'AED',
            qr_code_url: null,
            customer_id: 'dummy_cust',
            points_earned: 75,
            points_balance: 1250
        };
    }
});
</script>

<style>
.receipt-preview {
    font-family: 'Courier New', monospace;
    color: #000;
    line-height: 1.4;
    margin: 0 auto;
    background: white;
    padding: 10px;
    box-sizing: border-box;
}

.font-small {
    font-size: 10px;
}

.font-medium {
    font-size: 12px;
}

.font-large {
    font-size: 14px;
}

/* Print Specific */
@media print {
    .receipt-preview {
        width: 100% !important;
        margin: 0 !important;
        overflow-wrap: break-word;
        word-break: break-all;
    }
}
</style>
