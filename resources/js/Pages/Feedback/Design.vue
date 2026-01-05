<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import UnsplashPicker from '@/Components/UnsplashPicker.vue';

const props = defineProps<{
    settings?: Record<string, any>;
    restaurant?: { id: string; name: string; slug: string; logo?: string } | null;
}>();

const design = props.settings?.feedback_design || {};
const route = (window as any).route;

const form = useForm({
    page_title: design.page_title || 'We Value Your Feedback',
    welcome_message: design.welcome_message || 'Please let us know how your experience was.',
    theme_color: design.theme_color || '#1e3a8a', // default primary-900 (blue)
    text_color: design.text_color || '#ffffff',
    header_logo: null as File | null,
    background_image: null as File | null,
    reset_logo: false, // Flag to signal logo reset
});

// Use custom logo if set, otherwise fall back to restaurant logo, then placeholder
const defaultLogo = design.header_logo || props.restaurant?.logo || '/images/logo-placeholder.png';
const logoPreview = ref(defaultLogo);
const bgPreview = ref(design.background_image || null);

const showCropper = ref(false);
const croppingImage = ref<string | null>(null);
const cropperType = ref<'logo' | 'background'>('logo');
const cropperRef = ref<any>(null);
const showUnsplashPicker = ref(false);

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

const handleBgUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        croppingImage.value = URL.createObjectURL(file);
        cropperType.value = 'background';
        showCropper.value = true;
        target.value = '';
    }
};

const saveCrop = () => {
    const { canvas } = cropperRef.value.getResult();
    if (canvas) {
        canvas.toBlob((blob: Blob) => {
            const file = new File([blob], cropperType.value === 'logo' ? 'logo.png' : 'background.png', { type: 'image/png' });
            const previewUrl = URL.createObjectURL(file);

            if (cropperType.value === 'logo') {
                form.header_logo = file;
                logoPreview.value = previewUrl;
            } else {
                form.background_image = file;
                bgPreview.value = previewUrl;
            }
            showCropper.value = false;
        }, 'image/png');
    }
};

const handleUnsplashSelect = async (image: any) => {
    try {
        const imageUrl = image.urls.regular;
        const response = await fetch(imageUrl);
        const blob = await response.blob();
        const file = new File([blob], `unsplash-${image.id}.jpg`, { type: 'image/jpeg' });
        
        croppingImage.value = URL.createObjectURL(file);
        cropperType.value = 'background';
        showCropper.value = true;
    } catch (e) {
        console.error(e);
        alert('Failed to load image');
    }
};

const resetToDefaultLogo = () => {
    if (props.restaurant?.logo) {
        logoPreview.value = props.restaurant.logo;
        form.header_logo = null;
        form.reset_logo = true; // Signal backend to clear custom logo
    }
};

const submit = () => {
    // Get current locale from URL
    const locale = window.location.pathname.split('/')[1] || 'en';
    const url = `/${locale}/feedback/settings`;
    
    console.log('Submitting to URL:', url);
    console.log('Current pathname:', window.location.pathname);
    console.log('Extracted locale:', locale);
    
    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            alert('Design saved successfully!');
        },
        onError: (errors) => {
            console.error('Save failed:', errors);
            alert('Failed to save design. Please try again.');
        }
    });
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Controls -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Page Design</h3>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <Input v-model="form.page_title" label="Header Title" placeholder="e.g. Rate Us!" />
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Welcome Message</label>
                            <textarea 
                                v-model="form.welcome_message"
                                rows="2"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            ></textarea>
                        </div>

                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Theme Color (Header)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" v-model="form.theme_color" class="h-9 w-9 rounded border cursor-pointer" />
                                    <input type="text" v-model="form.theme_color" class="block w-full rounded-lg border-gray-300 py-1.5 text-xs" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Text Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" v-model="form.text_color" class="h-9 w-9 rounded border cursor-pointer" />
                                    <input type="text" v-model="form.text_color" class="block w-full rounded-lg border-gray-300 py-1.5 text-xs" />
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-4 space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                                    <button 
                                        v-if="design.header_logo && restaurant?.logo" 
                                        type="button" 
                                        @click="resetToDefaultLogo"
                                        class="text-xs text-primary font-medium hover:underline"
                                    >
                                        Reset to Restaurant Logo
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">
                                    {{ design.header_logo ? 'Using custom logo' : (restaurant?.logo ? 'Using restaurant logo (default)' : 'No logo set') }}
                                </p>
                                <input type="file" accept="image/*" @change="handleLogoUpload" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Background Image</label>
                                    <button type="button" @click="showUnsplashPicker = true" class="text-xs text-primary font-medium hover:underline">Select from Unsplash</button>
                                </div>
                                <input type="file" accept="image/*" @change="handleBgUpload" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <Button :loading="form.processing" type="submit" class="w-full justify-center">Save Design</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview -->
        <div class="lg:col-span-7 lg:sticky lg:top-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Live Mobile Preview</h3>
            
            <div class="flex justify-center items-center min-h-[600px] bg-gray-50 rounded-[2.5rem] p-8 border border-gray-200">
                
                <!-- Phone Frame -->
                <div class="w-full max-w-sm bg-white rounded-[2rem] shadow-2xl overflow-hidden border-8 border-gray-900 relative aspect-[9/18]">
                    
                    <!-- Dynamic Header with Background Image -->
                    <div 
                        class="p-6 text-center relative overflow-hidden transition-colors duration-500"
                        :style="{ 
                            backgroundColor: form.theme_color, 
                            color: form.text_color,
                            backgroundImage: bgPreview ? `url(${bgPreview})` : 'none',
                            backgroundSize: 'cover',
                            backgroundPosition: 'center'
                        }"
                    >
                        <!-- Overlay for better text readability when background image is present -->
                        <div v-if="bgPreview" class="absolute inset-0 bg-black/40"></div>
                        
                        <!-- Background Decoration (if no custom BG image) -->
                        <div v-if="!bgPreview" class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="currentColor" />
                            </svg>
                        </div>
                         
                        <div class="relative z-10">
                            <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-xl flex items-center justify-center mb-3 overflow-hidden border-4 border-white/40">
                                <img :src="logoPreview" class="w-full h-full object-cover" />
                            </div>
                            <h2 class="text-xl font-bold tracking-tight mb-1 drop-shadow-md">{{ form.page_title }}</h2>
                            <p class="text-xs font-medium tracking-wide uppercase opacity-90">{{ form.welcome_message }}</p>
                        </div>
                    </div>

                    <!-- Content Mock -->
                    <div class="p-6">
                        <div class="text-center mb-6">
                             <h3 class="text-lg font-bold text-gray-800 mb-4">How was your experience?</h3>
                             <div class="flex justify-center gap-2 mb-6">
                                <span v-for="i in 5" :key="i" class="text-yellow-400 text-3xl">★</span>
                             </div>
                             
                             <!-- Comment textarea mock -->
                             <div class="bg-gray-100 rounded-xl h-24 w-full mb-4 border border-gray-200"></div>
                             
                             <!-- Submit button with theme color -->
                             <div 
                                class="h-12 w-full rounded-xl flex items-center justify-center text-white font-semibold shadow-lg"
                                :style="{ backgroundColor: form.theme_color }"
                             >
                                Submit Feedback
                             </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modals -->
        <Modal :show="showCropper" @close="showCropper = false" title="Adjust Image">
             <div class="h-96 w-full bg-gray-100 rounded-xl overflow-hidden">
                <Cropper ref="cropperRef" class="h-full w-full" :src="croppingImage" :stencil-props="{ aspectRatio: cropperType === 'logo' ? 1 : 16/9 }" />
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <Button variant="secondary" @click="showCropper = false">{{ $t('common.cancel') }}</Button>
                <Button @click="saveCrop">Apply</Button>
            </div>
        </Modal>

        <UnsplashPicker :show="showUnsplashPicker" @close="showUnsplashPicker = false" @select="handleUnsplashSelect" />
    </div>
</template>
