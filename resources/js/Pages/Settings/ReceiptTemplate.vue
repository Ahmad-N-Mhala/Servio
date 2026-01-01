<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">POS Receipt Template</h1>
                <p class="mt-2 text-sm text-gray-600">Customize your receipt layout and content for POS printing</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left: Settings Form -->
                <div class="space-y-6">
                    <!-- Header Section -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            Header Section
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" v-model="form.show_logo" class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm font-medium text-gray-700">Show Restaurant Logo</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" v-model="form.show_restaurant_name" class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm font-medium text-gray-700">Show Restaurant Name</span>
                                </label>
                            </div>

                            <Input 
                                v-model="form.header_text"
                                label="Custom Header Text"
                                type="textarea"
                                rows="2"
                                placeholder="e.g., Thank you for dining with us!"
                            />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Header Alignment</label>
                                <select v-model="form.header_alignment" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details Section -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            Order Information
                        </h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_order_number" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Transaction Number</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_date_time" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Date & Time</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_table_number" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Table Number</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_customer_name" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Customer Name</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_server_name" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Server Name</span>
                            </label>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            Items Display
                        </h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_item_notes" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Item Notes</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Name Width</label>
                                <select v-model="form.item_name_width" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="50">50%</option>
                                    <option value="60">60%</option>
                                    <option value="70">70%</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Section -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            Totals & Payment
                        </h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_subtotal" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Subtotal</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_tax" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Tax</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_discount" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Discount (if applied)</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_payment_method" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show Payment Method</span>
                            </label>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                            </div>
                            Footer Section
                        </h3>
                        
                        <div class="space-y-4">
                            <Input 
                                v-model="form.footer_text"
                                label="Footer Message"
                                type="textarea"
                                rows="3"
                                placeholder="e.g., Thank you for your visit! Please come again."
                            />

                            <Input 
                                v-model="form.contact_info"
                                label="Contact Information"
                                type="textarea"
                                rows="2"
                                placeholder="e.g., Tel: +971 50 123 4567"
                            />

                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.show_qr_code" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">Show QR Code (for feedback/review)</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Footer Alignment</label>
                                <select v-model="form.footer_alignment" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Paper Size -->
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Receipt Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <Input 
                                    v-model="form.next_order_number"
                                    label="Start Transaction/Order Number From"
                                    type="number"
                                    placeholder="e.g. 1001"
                                />
                                <p class="text-xs text-gray-500 mt-1">First order will use this number, then continue counting sequentially.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Paper Width</label>
                                <select v-model="form.paper_width" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="58">58mm (Small)</option>
                                    <option value="80">80mm (Standard)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Font Size</label>
                                <select v-model="form.font_size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex gap-4">
                        <Button 
                            @click="saveTemplate" 
                            :loading="form.processing"
                            class="flex-1"
                        >
                            Save Template
                        </Button>
                        <Button 
                            @click="resetToDefault" 
                            variant="secondary"
                            :disabled="form.processing"
                        >
                            Reset to Default
                        </Button>
                    </div>
                </div>

                <!-- Right: Live Preview -->
                <div class="lg:sticky lg:top-8 h-fit">
                    <div class="glass-card rounded-2xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Live Preview</h3>
                        
                        <div id="receipt-preview-container" class="bg-white border-2 border-gray-300 rounded-lg p-6 shadow-inner" :style="{ width: form.paper_width === '58' ? '220px' : '300px', margin: '0 auto' }">
                            <ReceiptPreview 
                                :template="form" 
                                :logo="restaurant_logo"
                                :restaurant-name="restaurant_name"
                            />
                        </div>

                        <div class="mt-4 text-center">
                            <button @click="printPreview" class="text-sm text-primary hover:text-primary-hover font-medium flex items-center gap-2 mx-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Test Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';

const route = (window as any).route;

const props = defineProps<{
    template?: any;
    restaurant_logo?: string | null;
    restaurant_name?: string;
    next_order_number?: number | null;
}>();

const form = useForm({
    // Transaction
    next_order_number: props.next_order_number ?? null,

    // Header
    show_logo: props.template?.show_logo ?? true,
    show_restaurant_name: props.template?.show_restaurant_name ?? true,
    header_text: props.template?.header_text ?? '',
    header_alignment: props.template?.header_alignment ?? 'center',
    
    // Order Info
    show_order_number: props.template?.show_order_number ?? true,
    show_date_time: props.template?.show_date_time ?? true,
    show_table_number: props.template?.show_table_number ?? true,
    show_customer_name: props.template?.show_customer_name ?? true,
    show_server_name: props.template?.show_server_name ?? false,
    
    // Items
    show_item_notes: props.template?.show_item_notes ?? true,
    item_name_width: props.template?.item_name_width ?? '60',
    
    // Totals
    show_subtotal: props.template?.show_subtotal ?? true,
    show_tax: props.template?.show_tax ?? true,
    show_discount: props.template?.show_discount ?? true,
    show_payment_method: props.template?.show_payment_method ?? true,
    
    // Footer
    footer_text: props.template?.footer_text ?? 'Thank you for your visit!',
    contact_info: props.template?.contact_info ?? '',
    show_qr_code: props.template?.show_qr_code ?? false,
    footer_alignment: props.template?.footer_alignment ?? 'center',
    
    // Settings
    paper_width: props.template?.paper_width ?? '80',
    font_size: props.template?.font_size ?? 'medium',
});

const saveTemplate = () => {
    form.post(route('settings.receipt-template.store'), {
        onSuccess: () => {
            // Success handled by backend
        }
    });
};

const resetToDefault = () => {
    if (confirm('Are you sure you want to reset to default template? This will discard all your customizations.')) {
        form.reset();
    }
};

const printPreview = () => {
    const content = document.getElementById('receipt-preview-container');
    if (!content) return;

    // Create a hidden iframe
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0px';
    iframe.style.height = '0px';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow?.document;
    if (!doc) return;

    // Write content
    doc.open();
    doc.write('<html><head><title>Receipt Print</title>');
    
    // Copy styles from current page to iframe
    const styles = document.querySelectorAll('style, link[rel="stylesheet"]');
    styles.forEach(style => {
        doc.write(style.outerHTML);
    });

    // Add specific print styles
    const width = form.paper_width === '58' ? '58mm' : '80mm';
    doc.write(`
        <style>
            @media print {
                @page { margin: 0; size: auto; }
                body { margin: 0; padding: 0; }
            }
            body { 
                background: white; 
                width: ${width}; 
                margin: 0 auto;
                padding-bottom: 20mm; /* Extra space at bottom */
            }
            /* Override container styles for printing */
            #receipt-preview-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                margin: 0 !important;
            }
            img {
                -webkit-filter: grayscale(100%); /* Standard thermal look */
                filter: grayscale(100%);
            }
        </style>
    `);
    
    doc.write('</head><body>');
    doc.write(content.outerHTML);
    doc.write('</body></html>');
    doc.close();

    // Print after resources load
    iframe.contentWindow?.focus();
    setTimeout(() => {
        iframe.contentWindow?.print();
        // Remove iframe after printing (give user time to interact with dialog)
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000); // Keep it briefly
    }, 500);
};
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .glass-card, .glass-card * {
        visibility: visible;
    }
    .glass-card {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
