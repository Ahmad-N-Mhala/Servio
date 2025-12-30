<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
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
    loyalty_logo: null as File | null,
    loyalty_banner: null as File | null,
    
    // Earning Rules (Only for Program Mode)
    earning_method_type: props.earningMethod?.type || 'order_total',
    earning_points: props.earningMethod?.points || 1,
    earning_currency_amount: props.earningMethod?.currency_amount || 1,
});

const logoPreview = ref(initialData.loyalty_logo || '/images/logo-placeholder.png');
const bannerPreview = ref(initialData.loyalty_banner || null);

// Re-sync if reward changes (when modal opens with new reward)
import { watch } from 'vue';
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
        
        logoPreview.value = design.loyalty_logo || '/images/logo-placeholder.png';
        bannerPreview.value = design.loyalty_banner || null;
    }
}, { deep: true });

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
        const canvas = await html2canvas(cardPreviewRef.value, {
            useCORS: true,
            scale: 3, // Higher quality for print
            backgroundColor: form.loyalty_theme_color, // Ensure background is captured
            logging: false
        });

        const imgData = canvas.toDataURL('image/png');
        
        // Calculate dimensions to fit A4 or similar
        const imgWidth = 210; // A4 width in mm
        const pageHeight = 297; // A4 height in mm
        
        // Use aspect ratio of the captured canvas
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        // Center image vertically if it's smaller, or just fit to page
        // For a card flyer, we typically want it centered
        
        const pdf = new jsPDF('p', 'mm', 'a4');
        
        // Calculate vertical center
        const yPos = (pageHeight - imgHeight) / 2;
        
        // Add image
        pdf.addImage(imgData, 'PNG', 0, Math.max(0, yPos), imgWidth, imgHeight);
        
        // Open PDF in new tab
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
                    <h3 class="text-xl font-bold text-gray-900">Program Design</h3>
                    <Button type="button" variant="secondary" @click="printCard">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </Button>
                </div>
                
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Earning Rules Section -->
                    <div v-if="!isRewardMode" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Earning Rules</h4>
                        
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div 
                                @click="form.earning_method_type = 'order_total'"
                                class="cursor-pointer border-2 rounded-xl p-3 text-center transition-all"
                                :class="form.earning_method_type === 'order_total' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <div class="font-bold text-gray-900 text-sm">Per Spend</div>
                                <div class="text-[10px] text-gray-500">Points based on bill total</div>
                            </div>

                            <div 
                                @click="form.earning_method_type = 'visit'"
                                class="cursor-pointer border-2 rounded-xl p-3 text-center transition-all"
                                :class="form.earning_method_type === 'visit' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <div class="font-bold text-gray-900 text-sm">Per Visit</div>
                                <div class="text-[10px] text-gray-500">Fixed points per order</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Points to Earn</label>
                                <input 
                                    v-model="form.earning_points"
                                    type="number"
                                    min="1"
                                    class="block w-full rounded-lg border-gray-300 py-1.5 text-sm focus:ring-primary focus:border-primary"
                                />
                            </div>
                            <div v-if="form.earning_method_type === 'order_total'">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Per Currency Unit</label>
                                <input 
                                    v-model="form.earning_currency_amount"
                                    type="number"
                                    min="1"
                                    class="block w-full rounded-lg border-gray-300 py-1.5 text-sm focus:ring-primary focus:border-primary"
                                    placeholder="1"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Branding Section -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Visual Identity</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Theme Color</label>
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
                                <label class="block text-xs font-medium text-gray-700 mb-2">Text Color</label>
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
                            :label="isRewardMode ? 'Reward Title' : 'Program Name'"
                            :placeholder="isRewardMode ? 'e.g. Free Coffee' : 'e.g. VIP Club'"
                        />

                        <Input 
                            v-model="form.loyalty_card_title"
                            :label="isRewardMode ? 'Subtitle / Points' : 'Card Title'"
                            :placeholder="isRewardMode ? 'e.g. 100 Points' : 'e.g. Gold Member'"
                        />

                        <Input 
                            v-model="form.loyalty_card_description"
                            label="Tagline"
                            placeholder="e.g. Turn your meals into rewards"
                        />

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                            <textarea 
                                v-model="form.loyalty_terms"
                                rows="3"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="*Terms apply..."
                            ></textarea>
                        </div>
                        
                         <Input 
                            v-model="form.loyalty_qr_link"
                            label="QR Code Link"
                            placeholder="https://your-website.com/join"
                            type="url"
                        />
                    </div>

                    <!-- Images -->
                    <div class="space-y-4 border-t pt-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Imagery</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Brand Logo</label>
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="handleLogoUpload"
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                            />
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Card Background</label>
                                <button type="button" @click="showUnsplashPicker = true" class="text-xs text-primary hover:text-primary-hover font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Select from Unsplash
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
                        <Button :loading="form.processing" type="submit" class="w-full justify-center">Save Configuration</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Side (Center in Print) -->
        <div class="lg:col-span-7 lg:sticky lg:top-8 printable-area">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 no-print">Live Preview</h3>
            
            <div class="flex justify-center items-center min-h-[600px] bg-gray-100/50 rounded-[2rem] p-8 printable-container">
                <!-- Fancy Card Design -->
                <div 
                    ref="cardPreviewRef"
                    class="w-full max-w-md aspect-[3/4.8] rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col text-center"
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
                    <div class="relative z-10 pt-10 px-8 pb-6 flex-1 flex flex-col items-center">
                        <div class="w-28 h-28 rounded-full bg-white p-1 shadow-xl mb-6 ring-4 ring-white/30">
                            <div class="w-full h-full rounded-full overflow-hidden bg-gray-50 flex items-center justify-center">
                                <img 
                                    :src="logoPreview" 
                                    class="w-full h-full object-cover"
                                    @error="logoPreview = '/images/logo-placeholder.png'"
                                />
                            </div>
                        </div>

                        <h2 v-if="form.loyalty_program_name" class="text-3xl font-black uppercase tracking-tight mb-2 leading-none drop-shadow-md">
                            {{ form.loyalty_program_name }}
                        </h2>
                        
                        <p v-if="form.loyalty_card_title" class="text-sm opacity-90 font-medium tracking-wide uppercase mb-8">
                            {{ form.loyalty_card_title }}
                        </p>

                        <!-- Earning Explanation Box -->
                        <div v-if="!isRewardMode" class="w-full bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-inner mb-6 transform transition-all hover:scale-105">
                            <div class="text-xs font-bold uppercase tracking-widest opacity-70 mb-2">How it works</div>
                            <div class="text-xl font-bold">
                                Get <span class="text-2xl">{{ form.earning_points }} Points</span>
                            </div>
                            <div class="text-sm opacity-90 mt-1">
                                <span v-if="form.earning_method_type === 'order_total'">
                                    for every {{ currency }} {{ form.earning_currency_amount || 1 }} you spend
                                </span>
                                <span v-else>
                                    for every visit
                                </span>
                            </div>
                        </div>

                        <p v-if="form.loyalty_card_description" class="text-sm leading-relaxed opacity-90 mb-auto italic">
                            "{{ form.loyalty_card_description }}"
                        </p>
                    </div>

                     <!-- QR Section (Live) -->
                    <div v-if="form.loyalty_qr_link" class="relative z-10 bg-white text-gray-900 p-8 rounded-t-[2.5rem] shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.3)] w-full">
                        <div class="absolute top-3 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-gray-300 rounded-full"></div>
                        
                        <div class="text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">{{ isRewardMode ? 'Scan to Redeem' : 'Scan to Join' }}</p>
                            <div class="bg-gray-50 border p-4 rounded-xl inline-block shadow-sm mx-auto">
                                <img 
                                    :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(form.loyalty_qr_link)}`"
                                    class="w-32 h-32"
                                    alt="QR Code"
                                />
                            </div>
                             <p v-if="form.loyalty_terms" class="text-[10px] text-gray-400 mt-6 mx-auto max-w-[200px] hover:text-gray-600 transition-colors">
                                {{ form.loyalty_terms }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Fallback Terms if No QR -->
                    <div v-else-if="form.loyalty_terms" class="relative z-10 w-full text-center pb-8 pt-4 px-6">
                         <p class="text-[10px] text-white/70 mt-4 mx-auto max-w-[200px] hover:text-white transition-colors border-t border-white/20 pt-4">
                            {{ form.loyalty_terms }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cropper Modal -->
        <Modal :show="showCropper" @close="showCropper = false" :title="cropperType === 'logo' ? 'Adjust Logo' : 'Adjust Background'" size="lg">
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
                    <Button variant="secondary" @click="showCropper = false">Cancel</Button>
                    <Button @click="saveCrop">Confirm & Use</Button>
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
