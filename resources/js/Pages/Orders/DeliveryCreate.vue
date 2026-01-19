<template>
    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ $t('nav.orders_create') }} (POS/Delivery)</h1>
            </div>

            <form @submit.prevent="createOrder" class="space-y-8">
                <!-- Stock Error Display -->
                <div v-if="form.errors.items" class="glass-card rounded-2xl p-6 border-2 border-red-300 bg-red-50">
                    <div class="flex items-start gap-3">
                         <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-bold text-red-900 mb-2">⚠️ Stock Issue</h4>
                            <ul class="space-y-1 text-sm text-red-800">
                                <li v-for="(error, idx) in (Array.isArray(form.errors.items) ? form.errors.items : [form.errors.items])" :key="idx">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Card -->
                <div class="glass-card rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        {{ $t('common.guest') }} {{ $t('dashboard_page.details') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">{{ $t('staff.phone') }}</label>
                            <div class="relative group" dir="ltr">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-medium z-10 select-none pointer-events-none border-r border-gray-200 pr-3 my-2">{{ phoneCode }}</span>
                                <input 
                                    v-model="phoneInput"
                                    type="tel"
                                    maxlength="15"
                                    @input="handlePhoneInput"
                                    @blur="lookupCustomer"
                                    :placeholder="'501234567'"
                                    class="w-full text-left rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-sm focus:border-primary focus:ring-4 focus:ring-primary/10 py-3 pl-20 pr-4 transition-all hover:border-slate-300 dark:hover:border-slate-600"
                                />
                            </div>
                        </div>
                        <Input 
                            v-model="form.customer_name"
                            :label="$t('staff.name')"
                            type="text"
                            placeholder="Optional"
                            :error="form.errors.customer_name"
                        />
                    </div>
                </div>

                <!-- Order Type Selection -->
                <div class="glass-card rounded-2xl p-6 relative z-20">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $t('orders.type') }}</h3>
                    <div class="flex gap-4 mb-6">
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" v-model="form.type" value="delivery" class="peer sr-only" />
                            <div class="p-4 rounded-xl border-2 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 text-center">
                                <span class="font-semibold text-gray-700 peer-checked:text-primary">{{ $t('orders.delivery') }}</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" v-model="form.type" value="takeaway" class="peer sr-only" />
                            <div class="p-4 rounded-xl border-2 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 text-center">
                                <span class="font-semibold text-gray-700 peer-checked:text-primary">{{ $t('orders.takeaway') }}</span>
                            </div>
                        </label>
                    </div>

                    <div v-if="form.type === 'delivery'" class="animate-fade-in-up space-y-4">
                        <Select
                             v-model="form.delivery_provider"
                             :label="$t('orders.delivery_provider_label')"
                             :options="deliveryProviderOptions"
                             placeholder="Select Provider / اختر المزود"
                             :error="form.errors.delivery_provider"
                        />
                        <Input
                             v-model="form.delivery_order_id"
                             :label="$t('orders.external_order_id')"
                             placeholder="#12345"
                             :error="form.errors.delivery_order_id"
                        />
                    </div>
                </div>

                <!-- Menu Items Card -->
                <div class="glass-card rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $t('nav.menu') }}</h3>
                    
                    <div class="space-y-6">
                        <div v-for="category in categoriesList" :key="category.id">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                {{ getLocaleName(category.name) }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    class="group flex flex-col bg-white border border-gray-100 hover:border-primary/50 hover:shadow-lg rounded-2xl overflow-hidden transition-all duration-300 relative"
                                    :class="{'opacity-75': item.inventory_status?.sold_out}"
                                >
                                    <!-- Stock Warning Overlay -->
                                    <div 
                                        v-if="item.inventory_status?.sold_out" 
                                        class="absolute inset-x-0 top-0 z-10 w-full h-48 bg-gray-900/10 backdrop-blur-[1px] flex items-center justify-center"
                                    >
                                        <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                                            {{ $t('common.sold_out') }}
                                        </span>
                                    </div>

                                    <!-- Image Area -->
                                    <div class="relative w-full aspect-[4/3] bg-gray-50 overflow-hidden">
                                        <img 
                                            v-if="item.image"
                                            :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image" 
                                            class="w-full h-full object-cover" 
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>

                                    <!-- Content Area -->
                                    <div class="flex-1 p-4 flex flex-col">
                                        <div class="flex-1 mb-2">
                                            <h5 class="font-bold text-gray-900 line-clamp-1">
                                                {{ getLocaleName(item.name) }}
                                            </h5>
                                            <p class="text-sm font-medium text-primary mt-1">
                                                {{ currencyCode }} {{ item.price.toFixed(2) }}
                                            </p>
                                        </div>

                                        <!-- Controls -->
                                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-dashed border-gray-100">
                                            <button 
                                                type="button"
                                                @click="removeItem(item)"
                                                :disabled="!getQty(item.id)"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                                :class="getQty(item.id) ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                                            >
                                                -
                                            </button>
                                            
                                            <span class="font-bold text-gray-900 min-w-[1.5rem] text-center">
                                                {{ getQty(item.id) || 0 }}
                                            </span>

                                            <button 
                                                type="button"
                                                @click="addItem(item)"
                                                :disabled="item.inventory_status?.sold_out"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all relative"
                                                :class="!item.inventory_status?.sold_out ? 'bg-primary text-white hover:bg-primary-hover shadow-md' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div v-if="cart.length > 0" class="glass-card rounded-2xl p-6 border-2 border-primary/20">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $t('orders.summary') }}</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div v-for="(item, index) in cart" :key="index" class="py-3 border-b border-gray-100 last:border-0">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ item.name }}</span>
                                        <span class="text-gray-500">× {{ item.qty }}</span>
                                    </div>
                                    <div v-if="item.extras && item.extras.length > 0" class="mt-1 text-xs text-blue-600 font-medium">
                                        <div v-for="(ex, i) in item.extras" :key="i">
                                            + {{ ex.name }} ({{ currencyCode }} {{ ex.price.toFixed(2) }})
                                        </div>
                                    </div>
                                    <div v-if="item.notes" class="mt-1 text-xs text-gray-600 italic bg-amber-50 px-2 py-1 rounded">
                                        📝 {{ item.notes }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                     <button 
                                        type="button"
                                        @click="openNotesModal(item)"
                                        class="p-1.5 text-gray-400 hover:text-primary rounded-lg"
                                    >
                                        📝
                                    </button>
                                    <span class="font-semibold min-w-[4rem] text-right">{{ currencyCode }} {{ getItemTotal(item).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 pt-3 border-t-2 border-gray-200">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ $t('common.subtotal') }}</span>
                            <span>{{ currencyCode }} {{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>{{ $t('pos.tax') }} (5%)</span>
                            <span>{{ currencyCode }} {{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-primary pt-2">
                            <span>{{ $t('common.total') }}</span>
                            <span>{{ currencyCode }} {{ total.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="glass-card rounded-2xl p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $t('orders.special_instructions') }}</label>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3 px-4"
                        placeholder="Any special requests..."
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <Link :href="route('orders.index')" class="flex-1 block">
                        <Button type="button" variant="secondary" block size="lg">{{ $t('common.cancel') }}</Button>
                    </Link>
                    <div class="flex-1">
                        <Button 
                            type="submit" 
                            block 
                            :loading="form.processing"
                            :disabled="cart.length === 0"
                        >
                            {{ $t('nav.orders_create') }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Customize Item Modal -->
        <Modal :show="showCustomizeModal" @close="showCustomizeModal = false" :title="customizingItem ? getLocaleName(customizingItem.name) : $t('menu.customize')" size="md">
            <div v-if="customizingItem" class="space-y-6">
                 <!-- Extras -->
                <div v-if="customizingItem.extras && customizingItem.extras.length > 0">
                    <h4 class="font-bold text-gray-900 mb-3">{{ $t('menu.add_extras') }}</h4>
                    <div class="space-y-2">
                        <div 
                            v-for="extra in customizingItem.extras" 
                            :key="extra.id"
                            @click="toggleExtra(extra)"
                            class="flex items-center justify-between p-3 rounded-lg border-2 cursor-pointer transition-all"
                            :class="selectedExtras.some(e => e.id === extra.id) 
                                ? 'border-primary bg-primary/5' 
                                : 'border-gray-100 hover:border-gray-300'"
                        >
                            <span class="font-medium text-gray-700">
                                {{ getLocaleName(extra.name) }}
                            </span>
                            <span class="text-primary font-bold">+ {{ currencyCode }} {{ Number(extra.price).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 italic py-4">
                    {{ $t('menu.no_options') }}
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="button" variant="secondary" @click="showCustomizeModal = false" class="flex-1">{{ $t('common.cancel') }}</Button>
                    <Button type="button" @click="addCustomizedItem" class="flex-1">{{ $t('menu.add_to_order') }}</Button>
                </div>
            </div>
        </Modal>

        <!-- Item Notes Modal -->
        <Modal :show="showNotesModal" @close="closeNotesModal" title="Item Notes" size="md">
            <div class="space-y-4">
                <div v-if="editingCartItem">
                    <p class="font-semibold text-gray-900 mb-2">{{ editingCartItem.name }} × {{ editingCartItem.qty }}</p>
                    <textarea 
                        v-model="tempNotes"
                        rows="4"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary py-3 px-4"
                        placeholder="Item instructions..."
                    ></textarea>
                </div>
                <div class="flex gap-3 pt-4">
                    <Button type="button" variant="secondary" @click="closeNotesModal" class="flex-1">{{ $t('common.cancel') }}</Button>
                    <Button type="button" @click="saveNotes" class="flex-1">{{ $t('orders.save_note') }}</Button>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import Select from '@/Components/Select.vue';

const { locale } = useI18n();
const route = (window as any).route;
const page = usePage();

const props = defineProps({
    menuCategories: { type: Array as () => any[], default: () => [] },
    customers: { type: Array, default: () => [] },
    currency: { type: String, default: 'AED' },
    deliveryProviders: { type: Array as () => any[], default: () => [] },
});

// Computed
const categoriesList = computed(() => props.menuCategories || []);
const currencyCode = computed(() => (page.props.current_restaurant as any)?.currency || props.currency || 'AED');

const deliveryProviderOptions = computed(() => {
    // internal option
    const options = [
        { label: 'Internal Delivery (توصيل داخلي)', value: 'Internal' }
    ];

    if (props.deliveryProviders && props.deliveryProviders.length > 0) {
        // Add DB providers
        // Admin can rename them to include Arabic text if desired
        props.deliveryProviders.forEach(p => {
            options.push({
                label: p.name,
                value: p.name
            });
        });
    } else {
        // Fallback defaults if nothing in DB
        options.push(
            { label: 'Talabat (طلبات)', value: 'Talabat' },
            { label: 'Uber Eats (أوبر إيتس)', value: 'Uber Eats' },
            { label: 'Deliveroo (ديليفيرو)', value: 'Deliveroo' },
            { label: 'Careem Food (كريم فود)', value: 'Careem Food' },
            { label: 'Noon Food (نون فود)', value: 'Noon Food' },
            { label: 'Zomato (زوماتو)', value: 'Zomato' },
            { label: 'HungerStation (هنقرستيشن)', value: 'HungerStation' },
            { label: 'Jahez (جاهز)', value: 'Jahez' },
            { label: 'Keeta (كيتا)', value: 'Keeta' }
        );
    }
    return options;
});

// State
const cart = ref<any[]>([]);
const showCustomizeModal = ref(false);
const customizingItem = ref<any>(null);
const selectedExtras = ref<any[]>([]);
const showNotesModal = ref(false);
const editingCartItem = ref<any>(null);
const tempNotes = ref('');

const form = useForm({
    customer_phone: '',
    customer_name: '',
    type: 'delivery',
    delivery_provider: '',
    delivery_order_id: '',
    items: [] as any[],
    subtotal: 0,
    tax: 0,
    discount_amount: 0,
    total: 0,
    notes: '',
});

// Methods
const phoneCode = computed(() => (page.props.current_restaurant as any)?.phone_code || '+971');
const phoneInput = ref('');

const handlePhoneInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const value = target.value.replace(/\D/g, '');
    phoneInput.value = value;
    if (value) {
        form.customer_phone = phoneCode.value + value;
    } else {
        form.customer_phone = '';
    }
};

const lookupCustomer = () => {
    // Placeholder for future lookup logic
};

const getLocaleName = (name: any) => {
    if (typeof name === 'string') return name;
    return name[locale.value] || Object.values(name)[0] || '';
};

const getQty = (itemId: number) => {
    return cart.value
        .filter(i => i.id === itemId)
        .reduce((sum, i) => sum + i.qty, 0);
};

const addItem = (item: any) => {
    if ((item.extras && item.extras.length > 0) || item.type === 'meal') {
        customizingItem.value = item;
        selectedExtras.value = [];
        showCustomizeModal.value = true;
        return;
    }

    const existing = cart.value.find(i => i.id === item.id && (!i.extras || i.extras.length === 0));
    if (existing) {
        existing.qty++;
    } else {
        cart.value.push({
            id: item.id,
            name: getLocaleName(item.name),
            price: item.price,
            qty: 1,
            notes: '',
            type: item.type || 'item',
            extras: []
        });
    }
};

const removeItem = (item: any) => {
    const idx = cart.value.findIndex(i => i.id === item.id);
    if (idx !== -1) {
        if (cart.value[idx].qty > 1) {
            cart.value[idx].qty--;
        } else {
            cart.value.splice(idx, 1);
        }
    }
};

const toggleExtra = (extra: any) => {
    const idx = selectedExtras.value.findIndex(e => e.id === extra.id);
    if (idx > -1) {
        selectedExtras.value.splice(idx, 1);
    } else {
        selectedExtras.value.push(extra);
    }
};

const addCustomizedItem = () => {
    if (!customizingItem.value) return;
    const item = customizingItem.value;
    
    cart.value.push({
        id: item.id,
        name: getLocaleName(item.name),
        price: item.price,
        qty: 1,
        notes: '',
        type: item.type || 'item',
        extras: selectedExtras.value.map(e => ({
            id: e.id,
            name: getLocaleName(e.name),
            price: Number(e.price)
        }))
    });
    
    showCustomizeModal.value = false;
    customizingItem.value = null;
    selectedExtras.value = [];
};

const openNotesModal = (item: any) => {
    editingCartItem.value = item;
    tempNotes.value = item.notes || '';
    showNotesModal.value = true;
};

const closeNotesModal = () => {
    showNotesModal.value = false;
    editingCartItem.value = null;
};

const saveNotes = () => {
    if (editingCartItem.value) {
        editingCartItem.value.notes = tempNotes.value;
    }
    closeNotesModal();
};

const subtotal = computed(() => 
    cart.value.reduce((sum: number, item: any) => {
        const itemTotal = (Number(item.price) + (item.extras?.reduce((s: any, e: any) => s + Number(e.price), 0) || 0)) * item.qty;
        return sum + itemTotal;
    }, 0)
);

const tax = computed(() => subtotal.value * 0.05); // 5% VAT hardcoded for now or use prop
const total = computed(() => subtotal.value + tax.value);

const getItemTotal = (item: any) => {
    const extrasTotal = item.extras?.reduce((sum: number, e: any) => sum + Number(e.price), 0) || 0;
    return (Number(item.price) + extrasTotal) * item.qty;
};

const createOrder = () => {
    form.items = cart.value.map(item => ({
        menu_item_id: item.id,
        quantity: item.qty,
        unit_price: item.price,
        notes: item.notes,
        extras: item.extras
    }));
    form.subtotal = subtotal.value;
    form.tax = tax.value;
    form.total = total.value;
    
    form.post(route('pos-orders.store'), {
        onSuccess: () => {
            cart.value = [];
            form.reset();
        }
    });
};
</script>
