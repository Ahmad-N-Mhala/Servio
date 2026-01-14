<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import QRCode from 'qrcode';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import UnsplashPicker from '@/Components/UnsplashPicker.vue';

const props = defineProps<{
    settings?: Record<string, any>;
    earningMethod?: Record<string, any>;
    mode?: 'program' | 'reward';
    reward?: Record<string, any>;
}>();

const page = usePage();
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');
const isRewardMode = computed(() => props.mode === 'reward');

const initialData = isRewardMode.value ? (props.reward?.design || {}) : (props.settings || {});

const form = useForm({
    loyalty_program_name: initialData.loyalty_program_name || (isRewardMode.value ? (props.reward?.name?.en || 'Reward Name') : 'Restaurant Rewards'),
    loyalty_card_title: initialData.loyalty_card_title || (isRewardMode.value ? `${props.reward?.points_required || 0} Pts` : 'Loyalty Member'),
    loyalty_card_description: initialData.loyalty_card_description || (isRewardMode.value ? (props.reward?.description || 'Redeem this reward') : 'Get exclusive perks and cashback.'),
    loyalty_theme_color: initialData.loyalty_theme_color || '#1e1b4b', // Default Indigo-950
    loyalty_text_color: initialData.loyalty_text_color || '#ffffff',
    loyalty_terms: initialData.loyalty_terms || (isRewardMode.value ? '*Valid for one time use.' : '*Points expire after 1 year.'),
    loyalty_qr_link: initialData.loyalty_qr_link || '',
    loyalty_scan_text: initialData.loyalty_scan_text || '',
    loyalty_qr_size: initialData.loyalty_qr_size || 'md',
    loyalty_panel_height: initialData.loyalty_panel_height || 'md',
    loyalty_logo: null as File | null,
    loyalty_banner: null as File | null,
    
    // Earning Rules (Only for Program Mode)
    earning_method_type: props.earningMethod?.type || 'order_total',
    earning_points: props.earningMethod?.points || 1,
    earning_currency_amount: props.earningMethod?.currency_amount || 1,
    min_spent: props.earningMethod?.min_spent || 0,
});

const logoPreview = ref(initialData.loyalty_logo || '/images/placeholder-logo.svg');
const bannerPreview = ref(initialData.loyalty_banner || null);

// QR Code Generation (Local Data URL for html2canvas compatibility)
// QR Code Generation (Local Canvas preferred for html2canvas)
const qrDataUrl = ref<string>('');
const useCanvas = ref(false);
const qrCanvas = ref<HTMLCanvasElement | null>(null);

const generateQR = async (link: string) => {
    const text = link || 'https://example.com';
    useCanvas.value = false;

    // Ensure DOM is ready if canvas is missing
    if (!qrCanvas.value) await nextTick();

    try {
        // Robust import check
        // @ts-ignore
        const qrcodeLib = QRCode?.default || QRCode;
        
        // Try Canvas First
        if (qrCanvas.value && qrcodeLib && typeof qrcodeLib.toCanvas === 'function') {
            await qrcodeLib.toCanvas(qrCanvas.value, text, { width: 300, margin: 2 });
            useCanvas.value = true;
            // Key fix: Update data URL from canvas so printCard can use it
            qrDataUrl.value = qrCanvas.value.toDataURL('image/png');
            return;
        }

        // Fallback to Data URL
        if (qrcodeLib && typeof qrcodeLib.toDataURL === 'function') {
            qrDataUrl.value = await qrcodeLib.toDataURL(text, { width: 300, margin: 2 });
        } else if (typeof qrcodeLib === 'function') {
             // @ts-ignore
             qrDataUrl.value = await qrcodeLib(text, { width: 300, margin: 2 });
        } else {
             // Fallback
             qrDataUrl.value = `https://quickchart.io/qr?text=${encodeURIComponent(text)}&size=300&margin=2`;
        }
    } catch (e) {
        console.error('QR Generation error:', e);
        qrDataUrl.value = `https://quickchart.io/qr?text=${encodeURIComponent(text)}&size=300&margin=2`;
    }
};

watch(() => form.loyalty_qr_link, (newVal) => {
    generateQR(newVal);
});

onMounted(() => {
    setTimeout(() => {
         generateQR(form.loyalty_qr_link);
    }, 100);
});

// Watch for changes in Reward to update form data
watch(() => props.reward, (newReward) => {
    if (isRewardMode.value && newReward) {
        const design = newReward.design || {};
        form.loyalty_program_name = design.loyalty_program_name || newReward.name?.en || '';
        form.loyalty_card_title = design.loyalty_card_title || `${newReward.points_required} Pts`;
        form.loyalty_card_description = design.loyalty_card_description || newReward.description || '';
        form.loyalty_theme_color = design.loyalty_theme_color || '#1e1b4b';
        form.loyalty_text_color = design.loyalty_text_color || '#ffffff';
        form.loyalty_terms = design.loyalty_terms || '';
        form.loyalty_qr_link = design.loyalty_qr_link || '';
        form.loyalty_scan_text = design.loyalty_scan_text || '';
        form.loyalty_qr_size = design.loyalty_qr_size || 'md';
        form.loyalty_panel_height = design.loyalty_panel_height || 'md';
        
        logoPreview.value = design.loyalty_logo || '/images/placeholder-logo.svg';
        bannerPreview.value = design.loyalty_banner || null;
        generateQR(form.loyalty_qr_link);
    }
}, { deep: true, immediate: true });

const appRoute = (window as any).route;

// ... (Rest of cropper state remains same) ...

const showCropper = ref(false);
const croppingImage = ref<string | null>(null);
const cropperType = ref<'logo' | 'banner'>('logo');
const cropperRef = ref<any>(null);

const handleLogoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        croppingImage.value = URL.createObjectURL(file);
        cropperType.value = 'logo';
        showCropper.value = true;
        target.value = '';
    }
};

const handleBannerUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        croppingImage.value = URL.createObjectURL(file);
        cropperType.value = 'banner';
        showCropper.value = true;
        target.value = '';
    }
};

const saveCrop = () => {
    const { canvas } = cropperRef.value.getResult();
    if (canvas) {
        canvas.toBlob((blob: Blob) => {
            const file = new File([blob], cropperType.value === 'logo' ? 'logo.png' : 'banner.png', { type: 'image/png' });
            const previewUrl = URL.createObjectURL(file);

            if (cropperType.value === 'logo') {
                form.loyalty_logo = file;
                logoPreview.value = previewUrl;
            } else {
                form.loyalty_banner = file;
                bannerPreview.value = previewUrl;
            }
            showCropper.value = false;
        }, 'image/png');
    }
};

const emit = defineEmits(['success']);

const submit = () => {
    const routeName = isRewardMode.value ? 'loyalty.rewards.update-design' : 'loyalty.settings.update';
    const routeParams = isRewardMode.value ? props.reward?.id : undefined;

    form.post(appRoute(routeName, routeParams), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit('success');
        }
    });
};

const handleImageError = (event: Event, fallbackUrl: string) => {
    const target = event.target as HTMLImageElement;
    if (target.src !== fallbackUrl) {
         target.src = fallbackUrl;
    }
};

const cardPreviewRef = ref<HTMLElement | null>(null);

const printCard = async () => {
    if (!cardPreviewRef.value) return;

    try {
        // 1. Capture the Card Design
        // We use html2canvas for the layout, but we assume the QR might be missing or glitchy
        const designCanvas = await html2canvas(cardPreviewRef.value, {
            useCORS: true,
            scale: 3, 
            backgroundColor: form.loyalty_theme_color, 
            logging: false, 
            ignoreElements: (element) => element.classList.contains('no-print')
        });

        const designImgData = designCanvas.toDataURL('image/png');
        
        // 2. Setup PDF
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = 210;
        const pageHeight = 297;
        
        // 3. Add Design Image (Centered)
        const printWidth = 120; // Fixed width for consistent print size
        const ratio = printWidth / designCanvas.width; // Ratio of PDF-mm to Canvas-pixels
        const printHeight = designCanvas.height * ratio;
        
        const xPos = (pageWidth - printWidth) / 2;
        const yPos = (pageHeight - printHeight) / 2;
        
        pdf.addImage(designImgData, 'PNG', xPos, yPos, printWidth, printHeight);

        // 4. Smart Overlay (DOM-mapping):
        // This calculates the EXACT position of the QR code as seen on screen
        // and maps it to the PDF coordinates, accounting for all text/padding changes automatically.
        const qrContent = useCanvas.value ? qrCanvas.value : document.getElementById('qr-code-img');
        
        if (qrDataUrl.value && qrContent && cardPreviewRef.value) {
            try {
                // Measure positions in viewport pixels
                const cardRect = cardPreviewRef.value.getBoundingClientRect();
                const qrRect = qrContent.getBoundingClientRect();
                
                // Calculate position relative to the card container
                const relX = qrRect.left - cardRect.left;
                const relY = qrRect.top - cardRect.top;
                
                // Calculate the scaling factor (PDF mm per Screen Pixel)
                // We map the Card's onscreen width to the fixed Print Width (120mm)
                const mmPerPixel = printWidth / cardRect.width;
                
                const pdfQrX = xPos + (relX * mmPerPixel);
                const pdfQrY = yPos + (relY * mmPerPixel);
                const pdfQrW = qrRect.width * mmPerPixel;
                const pdfQrH = qrRect.height * mmPerPixel;

                // Add to PDF
                pdf.addImage(qrDataUrl.value, 'PNG', pdfQrX, pdfQrY, pdfQrW, pdfQrH);

            } catch (qrErr) {
                console.error('Smart QR Overlay failed:', qrErr);
            }
        }
        
        // Open PDF
        window.open(pdf.output('bloburl'), '_blank');

    } catch (error) {
        console.error('Printing failed:', error);
        alert('Could not generate print preview. Please try again.');
    }
};

const showUnsplashPicker = ref(false);

const handleUnsplashSelect = async (image: any) => {
    try {
        const imageUrl = image.urls.regular; // Use regular size for quality
        // Fetch the image to convert to a Blob/File
        const response = await fetch(imageUrl);
        const blob = await response.blob();
        const file = new File([blob], `unsplash-${image.id}.jpg`, { type: 'image/jpeg' });
        
        // Open the cropper for the new unsplash image
        // We treat it as a background (banner) image for now as that makes most sense
        croppingImage.value = URL.createObjectURL(file);
        cropperType.value = 'banner';
        showCropper.value = true;
        
        // We don't directly set it to form until cropped, but the cropper flow handles that via saveCrop logic
        // However, saveCrop uses the file input clearing logic which isn't relevant here, but `croppingImage` is what matters.
        
    } catch (e) {
        console.error('Error processing unsplash image:', e);
        alert('Failed to load image from Unsplash.');
    }
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- ... (Rest of your existing template) ... -->
        
        <!-- Controls Side (Hidden on Print) -->
        <div class="lg:col-span-5 space-y-6 no-print">
            <div class="glass-card p-6 rounded-2xl border border-gray-100 bg-white shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ $t('loyalty.program_design') }}</h3>
                    <Button type="button" variant="secondary" @click="printCard">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $t('loyalty.preview') }}
                    </Button>
                </div>
                
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Earning Rules Section -->
                    <div v-if="!isRewardMode" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">{{ $t('loyalty.earning_rules') }}</h4>
                        
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div 
                                @click="form.earning_method_type = 'order_total'"
                                class="cursor-pointer border-2 rounded-xl p-3 text-center transition-all"
                                :class="form.earning_method_type === 'order_total' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <div class="font-bold text-gray-900 text-sm">{{ $t('loyalty.per_spend') }}</div>
                                <div class="text-[10px] text-gray-500">{{ $t('loyalty.points_based_bill') }}</div>
                            </div>

                            <div 
                                @click="form.earning_method_type = 'visit'"
                                class="cursor-pointer border-2 rounded-xl p-3 text-center transition-all"
                                :class="form.earning_method_type === 'visit' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <div class="font-bold text-gray-900 text-sm">{{ $t('loyalty.per_visit') }}</div>
                                <div class="text-[10px] text-gray-500">{{ $t('loyalty.points_based_visit') }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ $t('loyalty.points_to_earn') }}</label>
                                <input 
                                    v-model="form.earning_points"
                                    type="number"
                                    min="1"
                                    class="block w-full rounded-lg border-gray-300 py-1.5 text-sm focus:ring-primary focus:border-primary"
                                />
                            </div>
                            <div v-if="form.earning_method_type === 'order_total'">
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ $t('loyalty.per_currency_unit') }}</label>
                                <input 
                                    v-model="form.earning_currency_amount"
                                    type="number"
                                    min="1"
                                    class="block w-full rounded-lg border-gray-300 py-1.5 text-sm focus:ring-primary focus:border-primary"
                                    placeholder="1"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ $t('loyalty.min_spend') }}</label>
                                <input 
                                    v-model="form.min_spent"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="block w-full rounded-lg border-gray-300 py-1.5 text-sm focus:ring-primary focus:border-primary"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Branding Section -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ $t('loyalty.visual_identity') }}</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">{{ $t('loyalty.theme_color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.loyalty_theme_color"
                                        class="h-9 w-9 cursor-pointer rounded-lg border-0 p-0 shadow-sm ring-1 ring-inset ring-gray-300"
                                    >
                                    <input 
                                        type="text" 
                                        v-model="form.loyalty_theme_color"
                                        class="block w-full rounded-lg border-gray-300 py-1.5 text-xs text-gray-600 focus:ring-primary focus:border-primary"
                                    >
                                </div>
                            </div>

                             <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">{{ $t('loyalty.text_color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="color" 
                                        v-model="form.loyalty_text_color"
                                        class="h-9 w-9 cursor-pointer rounded-lg border-0 p-0 shadow-sm ring-1 ring-inset ring-gray-300"
                                    >
                                    <input 
                                        type="text" 
                                        v-model="form.loyalty_text_color"
                                        class="block w-full rounded-lg border-gray-300 py-1.5 text-xs text-gray-600 focus:ring-primary focus:border-primary"
                                    >
                                </div>
                            </div>
                        </div>

                        <Input 
                            v-model="form.loyalty_program_name"
                            :label="isRewardMode ? $t('loyalty.reward_title') : $t('loyalty.program_name')"
                            :placeholder="isRewardMode ? 'e.g. Free Coffee' : 'e.g. VIP Club'"
                        />

                        <Input 
                            v-model="form.loyalty_card_title"
                            :label="isRewardMode ? $t('loyalty.subtitle_points') : $t('loyalty.card_title')"
                            :placeholder="isRewardMode ? 'e.g. 100 Points' : 'e.g. Gold Member'"
                        />

                        <Input 
                            v-model="form.loyalty_card_description"
                            :label="$t('loyalty.tagline')"
                            placeholder="e.g. Turn your meals into rewards"
                        />

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('loyalty.terms_conditions') }}</label>
                            <textarea 
                                v-model="form.loyalty_terms"
                                rows="3"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="*Terms apply..."
                            ></textarea>
                        </div>
                        
                         <Input 
                            v-model="form.loyalty_qr_link"
                            :label="$t('loyalty.qr_code_link')"
                            placeholder="https://your-website.com/join"
                            type="url"
                        />

                        <div class="grid grid-cols-2 gap-4">
                             <Input 
                                v-model="form.loyalty_scan_text"
                                label="Scan Prompt"
                                :placeholder="isRewardMode ? $t('loyalty.scan_to_redeem') : $t('loyalty.scan_to_join')"
                            />
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">QR Box Size</label>
                                <select 
                                    v-model="form.loyalty_qr_size"
                                    class="block w-full rounded-lg border-gray-300 py-2 text-sm focus:ring-primary focus:border-primary"
                                >
                                    <option value="sm">Small</option>
                                    <option value="md">Medium</option>
                                    <option value="lg">Large</option>
                                </select>
                            </div>
                            <!-- New Panel Height Control -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Panel Height</label>
                                <select 
                                    v-model="form.loyalty_panel_height"
                                    class="block w-full rounded-lg border-gray-300 py-2 text-sm focus:ring-primary focus:border-primary"
                                >
                                    <option value="sm">Short</option>
                                    <option value="md">Normal</option>
                                    <option value="lg">Tall</option>
                                    <option value="xl">Extra Tall</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="space-y-4 border-t pt-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ $t('loyalty.imagery') }}</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('loyalty.brand_logo') }}</label>
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="handleLogoUpload"
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                            />
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">{{ $t('loyalty.card_background') }}</label>
                                <button type="button" @click="showUnsplashPicker = true" class="text-xs text-primary hover:text-primary-hover font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ $t('loyalty.select_from_unsplash') }}
                                </button>
                            </div>
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="handleBannerUpload"
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                            />
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <Button :loading="form.processing" type="submit" class="w-full justify-center">{{ $t('loyalty.save_configuration') }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Side (Center in Print) -->
        <div class="lg:col-span-7 lg:sticky lg:top-8 printable-area">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 no-print">{{ $t('loyalty.live_preview') }}</h3>
            
            <div class="flex justify-center items-center min-h-[600px] bg-gray-100/50 rounded-[2rem] p-8 printable-container">
                <!-- Fancy Card Design -->
                <div 
                    ref="cardPreviewRef"
                    class="w-full max-w-[300px] aspect-[3/4.8] rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col text-center justify-between"
                    :style="{ backgroundColor: form.loyalty_theme_color, color: form.loyalty_text_color }"
                >
                    <!-- Glossy Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent pointer-events-none"></div>

                    <!-- Background Image -->
                     <div v-if="bannerPreview" class="absolute inset-0 z-0">
                        <img 
                            :src="bannerPreview" 
                            class="w-full h-full object-cover opacity-100"
                            @error="handleImageError($event, '/images/default-pattern.png')"
                        />
                         <!-- Optional Gradient to ensure text readability -->
                        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>
                    
                    <!-- Top Section -->
                    <div class="relative z-10 pt-6 px-6 pb-2 flex-1 flex flex-col items-center justify-center">
                        <div class="w-20 h-20 rounded-full bg-white p-1 shadow-xl mb-4 ring-4 ring-white/30 shrink-0">
                            <div class="w-full h-full rounded-full overflow-hidden bg-gray-50 flex items-center justify-center">
                                <img 
                                    :src="logoPreview" 
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>

                        <h3 class="font-black text-xl leading-tight mb-1 uppercase tracking-tight drop-shadow-md line-clamp-2">
                            {{ form.loyalty_program_name }}
                        </h3>
                        <p class="text-xs font-bold uppercase tracking-widest opacity-90 mb-2 drop-shadow-sm">
                            {{ form.loyalty_card_title }}
                        </p>

                        <!-- Earning Explanation Box -->
                        <div v-if="!isRewardMode" class="w-full bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 shadow-inner mb-2 transform transition-all hover:scale-105">
                            <div class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-1">{{ $t('loyalty.how_it_works') }}</div>
                            <div class="text-sm font-bold">
                                {{ $t('loyalty.get') }} <span class="text-base">{{ form.earning_points }} {{ $t('loyalty.points') }}</span>
                            </div>
                            <div class="text-[10px] opacity-90 mt-0.5">
                                <span v-if="form.earning_method_type === 'order_total'">
                                    {{ $t('loyalty.for_every') }} {{ currency }} {{ form.earning_currency_amount || 1 }} {{ $t('loyalty.you_spend') }}
                                </span>
                                <span v-else>
                                    {{ $t('loyalty.per_visit') }}
                                </span>
                            </div>
                        </div>

                        <p v-if="form.loyalty_card_description" class="text-xs leading-relaxed opacity-90 mb-auto italic line-clamp-2">
                            "{{ form.loyalty_card_description }}"
                        </p>
                    </div>

                     <!-- QR Section (Live) -->
                    <div 
                        v-if="form.loyalty_qr_link"
                        class="relative z-10 bg-white text-gray-900 rounded-t-[2.5rem] shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.3)] w-full transition-all px-6 shrink-0"
                        :class="{
                            'pt-2 pb-3': form.loyalty_panel_height === 'sm',
                            'pt-4 pb-6': form.loyalty_panel_height === 'md',
                            'pt-6 pb-8': form.loyalty_panel_height === 'lg',
                            'pt-8 pb-10': form.loyalty_panel_height === 'xl'
                        }"
                    >
                        <div class="absolute top-3 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-gray-300 rounded-full"></div>
                        
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                {{ form.loyalty_scan_text || (isRewardMode ? $t('loyalty.scan_to_redeem') : $t('loyalty.scan_to_join')) }}
                            </p>
                            <div 
                                class="bg-gray-50 border rounded-xl inline-block shadow-sm mx-auto transition-all"
                                :class="{
                                    'p-1': form.loyalty_qr_size === 'sm',
                                    'p-1.5': form.loyalty_qr_size === 'md',
                                    'p-2': form.loyalty_qr_size === 'lg'
                                }"
                            >
                                <canvas 
                                    v-show="useCanvas"
                                    ref="qrCanvas"
                                    class="mx-auto max-w-full object-contain"
                                    :class="{
                                        'w-10 h-10': form.loyalty_qr_size === 'sm',
                                        'w-12 h-12': form.loyalty_qr_size === 'md',
                                        'w-16 h-16': form.loyalty_qr_size === 'lg',
                                        'opacity-50': !form.loyalty_qr_link
                                    }"
                                ></canvas>
                                <img 
                                    v-show="!useCanvas"
                                    id="qr-code-img"
                                    :src="qrDataUrl"
                                    class="max-w-full object-contain"
                                    :class="{
                                        'w-10 h-10': form.loyalty_qr_size === 'sm',
                                        'w-12 h-12': form.loyalty_qr_size === 'md',
                                        'w-16 h-16': form.loyalty_qr_size === 'lg',
                                        'opacity-50': !form.loyalty_qr_link
                                    }"
                                    alt="QR Code"
                                    crossorigin="anonymous"
                                    width="128"
                                    height="128"
                                />
                            </div>
                            <p v-if="!form.loyalty_qr_link" class="text-[10px] text-red-400 mt-2 font-medium">
                                {{ $t('loyalty.qr_code_link_required') }}
                            </p>
                             <p v-else-if="form.loyalty_terms" class="text-[10px] text-gray-400 mt-2 mx-auto max-w-[180px] hover:text-gray-600 transition-colors line-clamp-2">
                                {{ form.loyalty_terms }}
                            </p>
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>

        <!-- Cropper Modal -->
        <Modal :show="showCropper" @close="showCropper = false" :title="cropperType === 'logo' ? $t('loyalty.adjust_logo') : $t('loyalty.adjust_background')" size="lg">
            <div class="space-y-4">
                <div class="h-96 w-full bg-gray-100 rounded-xl overflow-hidden">
                    <Cropper
                        ref="cropperRef"
                        class="h-full w-full"
                        :src="croppingImage"
                        :stencil-props="{
                            aspectRatio: cropperType === 'logo' ? 1/1 : 3/4.8,
                            movable: true,
                            resizable: true
                        }"
                        :resize-image="{
                            adjustStencil: false
                        }"
                    />
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="secondary" @click="showCropper = false">{{ $t('common.cancel') }}</Button>
                    <Button @click="saveCrop">{{ $t('loyalty.confirm_use') }}</Button>
                </div>
            </div>
        </Modal>


        <!-- Unsplash Picker Modal -->
        <UnsplashPicker 
            :show="showUnsplashPicker"
            @close="showUnsplashPicker = false"
            @select="handleUnsplashSelect"
        />
    </div>
</template>
