<template>
    <div :class="['receipt-preview', `font-${template.font_size}`]" :style="{ width: template.paper_width + 'mm' }">
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
                <div class="flex justify-between text-xs text-gray-500 pb-1">
                    <span class="font-semibold">Order #:</span>
                    <span class="uppercase">{{ displayOrder.number }}</span>
                </div>
                <div class="flex justify-between font-bold text-base border-t border-dashed border-gray-300 pt-1">
                    <span class="font-semibold">{{ template.show_order_number_label || 'Transaction #:' }}</span>
                    <span>{{ displayOrder.transaction_number }}</span>
                </div>
            </div>
            <div v-if="template.show_date_time" class="flex justify-between">
                <span class="font-semibold">Date:</span>
                <span>{{ displayOrder.date }}</span>
            </div>
            <div v-if="template.show_table_number && displayOrder.table" class="flex justify-between">
                <span class="font-semibold">Table:</span>
                <span>{{ displayOrder.table }}</span>
            </div>
            <div v-if="template.show_customer_name" class="flex justify-between">
                <span class="font-semibold">Customer:</span>
                <span>{{ displayOrder.customer }}</span>
            </div>
            <div v-if="template.show_server_name" class="flex justify-between">
                <span class="font-semibold">Server:</span>
                <span>{{ displayOrder.server }}</span>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Items -->
        <div class="mb-3">
            <div class="text-xs font-bold mb-2 flex">
                <span :style="{ width: template.item_name_width + '%' }">ITEM</span>
                <span class="flex-1 text-center">QTY</span>
                <span class="flex-1 text-right">PRICE</span>
            </div>
            
            <div class="space-y-2 text-sm">
                <div v-for="(item, index) in displayOrder.items" :key="index">
                    <div class="flex">
                        <span :style="{ width: template.item_name_width + '%' }" class="font-medium pr-2">{{ item.name }}</span>
                        <span class="flex-1 text-center">{{ item.quantity }}</span>
                        <span class="flex-1 text-right">{{ formatPrice(item.price) }}</span>
                    </div>
                    <div v-if="template.show_item_notes && item.notes" class="text-xs text-gray-600 italic ml-2">
                        {{ item.notes }}
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Totals -->
        <div class="text-sm space-y-1 mb-3">
            <div v-if="template.show_subtotal" class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ formatPrice(displayOrder.subtotal) }}</span>
            </div>
            <div v-if="template.show_tax && Number(displayOrder.tax) > 0" class="flex justify-between">
                <span>Tax:</span>
                <span>{{ formatPrice(displayOrder.tax) }}</span>
            </div>
            <div v-if="template.show_discount && Number(displayOrder.discount) > 0" class="flex justify-between text-red-600">
                <span>Discount:</span>
                <span>-{{ formatPrice(displayOrder.discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-400">
                <span>TOTAL:</span>
                <span>{{ formatPrice(displayOrder.total) }} {{ displayOrder.currency }}</span>
            </div>
            <div v-if="template.show_payment_method" class="flex justify-between text-xs pt-1">
                <span>Payment:</span>
                <span class="font-semibold uppercase">{{ displayOrder.payment_method || 'PENDING' }}</span>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-400 my-3"></div>

        <!-- Footer -->
        <div :class="`text-${template.footer_alignment}`" class="text-sm">
            <p v-if="template.footer_text" class="mb-2 whitespace-pre-wrap">{{ template.footer_text }}</p>
            <p v-if="template.contact_info" class="text-xs text-gray-600 whitespace-pre-wrap">{{ template.contact_info }}</p>
            
            <div v-if="template.show_qr_code" class="mt-3 flex justify-center">
                <!-- Placeholder QR Code or Real one if we implement generation -->
                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center overflow-hidden">
                    <img v-if="displayOrder.qr_code_url" :src="displayOrder.qr_code_url" class="w-full h-full object-cover">
                    <svg v-else class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h-2v3h-3v2h5v-5zM13 13h2v2h-2v-2zm2 2h2v2h-2v-2zm2 2h2v2h-2v-2zm0-4h2v2h-2v-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    template: any;
    logo?: string | null;
    restaurantName?: string;
    order?: any;
}>();

const formatPrice = (val: any) => {
    return Number(val || 0).toFixed(2);
};

const displayOrder = computed(() => {
    if (props.order) {
        return {
            id: props.order.id,
            number: props.order.order_number,
            transaction_number: props.order.transaction_number || '-',
            date: new Date(props.order.created_at).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }),
            table: props.order.table?.name || null,
            customer: props.order.customer_name || 'Guest',
            server: props.order.waiter?.name || '-',
            items: props.order.items.map((item: any) => {
                let name = item.menuItem?.name || item.menu_item?.name || 'Unknown Item';
                if (typeof name === 'string' && name.startsWith('{')) {
                    try {
                        const parsed = JSON.parse(name);
                        name = parsed.en || parsed.ar || name;
                    } catch (e) {}
                } else if (typeof name === 'object') {
                    name = name.en || name.ar || 'Unknown';
                }
                
                return {
                    name: name,
                    quantity: item.quantity,
                    price: item.total_price || (item.quantity * item.unit_price), 
                    notes: item.notes
                };
            }),
            subtotal: props.order.subtotal,
            tax: props.order.tax,
            discount: props.order.discount_amount,
            total: props.order.total,
            payment_method: props.order.payment_method,
            currency: props.order.currency || 'AED',
            qr_code_url: null // Can be added if needed
        };
    } else {
        // Dummy Data for Preview
        return {
            id: '64f7a8b29c', // Dummy ID
            number: 'ORD-12345',
            transaction_number: props.template.show_order_number ? '1001' : '---',
            date: new Date().toLocaleString(),
            table: 'T-5',
            customer: 'John Doe',
            server: 'Jane Smith',
            items: [
                { name: 'Burger', quantity: 2, price: 50.00, notes: 'No onions' },
                { name: 'Fries', quantity: 1, price: 15.00, notes: '' },
                { name: 'Coca Cola', quantity: 2, price: 10.00, notes: '' },
            ],
            subtotal: 75.00,
            tax: 3.75,
            discount: 5.00,
            total: 73.75,
            payment_method: 'CASH',
            currency: 'AED',
            qr_code_url: null
        };
    }
});
</script>

<style scoped>
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
        padding: 0;
    }
}
</style>
