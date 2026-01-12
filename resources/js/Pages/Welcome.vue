<template>
    <div :dir="locale === 'ar' ? 'rtl' : 'ltr'" class="min-h-screen bg-white font-sans text-gray-900 selection:bg-primary/20 selection:text-primary">
        
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <Logo class="h-10 w-auto text-primary" :show-text="false" />
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-purple-600 hidden sm:block">Servio</span>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-8 rtl:space-x-reverse">
                        <a href="#about" class="text-gray-600 hover:text-primary transition-colors font-medium text-sm">{{ $t('landing.about_us') }}</a>
                        <a href="#modules" class="text-gray-600 hover:text-primary transition-colors font-medium text-sm">{{ $t('landing.features') }}</a>
                        <a href="#pricing" class="text-gray-600 hover:text-primary transition-colors font-medium text-sm">{{ $t('landing.plans_pricing') }}</a>
                        
                        <div class="flex items-center gap-4 border-l border-gray-200 pl-6 rtl:border-r rtl:border-l-0 rtl:pr-6 rtl:pl-0">
                            <!-- Language Switcher -->
                             <button 
                                @click="toggleLanguage" 
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors flex items-center gap-2 text-sm font-semibold"
                            >
                                <span class="uppercase">{{ locale }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                                </svg>
                            </button>

                            <!-- Login CTA -->
                            <a :href="route('login')" class="px-5 py-2.5 rounded-xl bg-gray-900 text-white font-bold text-sm hover:bg-gray-800 transition-all shadow-lg shadow-gray-200">
                                {{ $t('landing.get_started') }}
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center gap-4">
                         <button 
                            @click="toggleLanguage" 
                            class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-sm font-bold uppercase"
                        >
                            {{ locale }}
                        </button>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 p-4 space-y-4">
                <a href="#about" class="block text-gray-600 font-medium">{{ $t('landing.about_us') }}</a>
                <a href="#modules" class="block text-gray-600 font-medium">{{ $t('landing.features') }}</a>
                <a href="#pricing" class="block text-gray-600 font-medium">{{ $t('landing.plans_pricing') }}</a>
                <a :href="route('login')" class="block w-full text-center px-4 py-3 bg-primary text-white font-bold rounded-xl">{{ $t('landing.get_started') }}</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-20 pb-32 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-purple-500/5 -z-10"></div>
            <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-b from-primary/10 to-transparent blur-3xl opacity-50 -z-10 transform translate-x-1/2 -translate-y-1/2"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">

                
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 leading-tight mb-6">
                    {{ $t('landing.hero_title') }}
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-500 mb-10 max-w-3xl mx-auto leading-relaxed">
                    {{ $t('landing.hero_subtitle') }}
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a :href="route('login')" class="px-8 py-4 rounded-2xl bg-primary text-white font-bold text-lg hover:bg-primary-hover hover:scale-105 transition-all shadow-xl shadow-primary/20">
                        {{ $t('landing.get_started') }}
                    </a>
                    <a href="#pricing" class="px-8 py-4 rounded-2xl bg-white text-gray-900 border border-gray-200 font-bold text-lg hover:bg-gray-50 hover:border-gray-300 transition-all">
                        {{ $t('landing.view_pricing') }}
                    </a>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div :class="locale === 'ar' ? 'lg:order-2' : ''">
                        <div class="relative">
                            <div class="absolute inset-0 bg-primary/20 rounded-3xl transform rotate-3 scale-105"></div>
                             
                            <!-- Dynamic Screenshot Slideshow -->
                            <div class="relative w-full aspect-[4/3] bg-gray-100 rounded-3xl overflow-hidden shadow-2xl shadow-primary/20 transform rotate-1 transition-all duration-500 hover:rotate-0 group">
                                <template v-if="screenshots && screenshots.length > 0">
                                    <div 
                                        v-for="(shot, index) in screenshots" 
                                        :key="shot.id"
                                        class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                                        :class="currentScreenshotIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                                    >
                                        <img :src="shot.image_path" class="w-full h-full object-cover" />
                                    </div>
                                    
                                    <!-- Canvas/Gradient Overlay for text readability if needed, but keeping clean for now -->

                                    <!-- Navigation Arrows -->
                                    <button 
                                        v-if="screenshots.length > 1"
                                        @click="prevScreenshot"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-black/20 hover:bg-black/40 text-white backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                                        :aria-label="$t('common.previous')"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    </button>
                                    
                                    <button 
                                        v-if="screenshots.length > 1"
                                        @click="nextScreenshot"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-black/20 hover:bg-black/40 text-white backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                                        :aria-label="$t('common.next')"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>

                                    <!-- Simple Dots for navigation -->
                                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-20" v-if="screenshots.length > 1">
                                        <button 
                                            v-for="(_, index) in screenshots" 
                                            :key="index"
                                            @click="currentScreenshotIndex = index"
                                            class="h-1.5 rounded-full transition-all duration-300"
                                            :class="currentScreenshotIndex === index ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80 w-1.5'"
                                        ></button>
                                    </div>
                                </template>
                                
                                <img v-else src="/images/dashboard-hero.png" alt="Servio Dashboard" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                    <div :class="locale === 'ar' ? 'lg:order-1' : ''">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                            {{ getLocaleText(landingSettings?.about_us_title) || $t('landing.about_title_default') }}
                        </h2>
                        <div class="prose prose-lg text-gray-500">
                             <p class="whitespace-pre-line leading-relaxed">
                                {{ getLocaleText(landingSettings?.about_us_description) || $t('landing.about_us_description_default') }}
                            </p>
                        </div>
                        

                    </div>
                </div>
            </div>
        </section>

        <!-- Modules Section -->
        <section id="modules" class="py-24 bg-gray-50/50">
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $t('landing.our_modules') }}</h2>
                    <p class="text-gray-500 text-lg">{{ $t('landing.modules_description') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="module in modules" 
                        :key="module.id" 
                        class="bg-white p-8 rounded-3xl border border-gray-100 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/5 transition-all group duration-300"
                    >
                        <div class="w-14 h-14 bg-primary/5 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary/10 transition-all duration-300">
                            {{ module.icon || '📦' }}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-primary transition-colors">
                            {{ getLocaleText(module.title) }}
                        </h3>
                        <p class="text-gray-500 leading-relaxed">
                            {{ getLocaleText(module.description) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                  <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $t('landing.plans_pricing') }}</h2>
                     <div class="flex items-center justify-center gap-4 bg-gray-100 p-1 rounded-xl w-fit mx-auto mt-6">
                        <button 
                            @click="billingCycle = 'monthly'"
                            :class="billingCycle === 'monthly' ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-700'"
                            class="px-6 py-2 rounded-lg text-sm transition-all"
                        >
                            {{ $t('landing.monthly') }}
                        </button>
                        <button 
                             @click="billingCycle = 'yearly'"
                            :class="billingCycle === 'yearly' ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-700'"
                            class="px-6 py-2 rounded-lg text-sm transition-all flex items-center gap-2"
                        >
                            {{ $t('landing.yearly') }}
                            <span class="text-[10px] bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">{{ $t('landing.discount_20') }}</span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.id"
                        class="relative p-8 rounded-3xl border transition-all duration-300 flex flex-col"
                        :class="plan.is_featured ? 'border-primary shadow-2xl shadow-primary/10 bg-white ring-1 ring-primary' : 'border-gray-100 bg-gray-50/30 hover:border-gray-200'"
                    >
                        <div v-if="plan.is_featured" class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-primary text-white text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg whitespace-nowrap">
                            {{ $t('landing.most_popular') }}
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ plan.slug ? ($t('plans.' + plan.slug) !== 'plans.' + plan.slug ? $t('plans.' + plan.slug) : plan.name) : plan.name }}</h3>
                            <p class="text-sm text-gray-500 h-10 line-clamp-2">{{ plan.description }}</p>
                        </div>

                        <div class="mb-8 flex items-end gap-1">
                             <span class="text-4xl font-black text-gray-900">
                                {{ plan.currency || $t('landing.currency') }} {{ billingCycle === 'monthly' ? plan.price_monthly : plan.price_yearly }}
                             </span>
                             <span class="text-gray-500 font-medium mb-1">
                                {{ billingCycle === 'monthly' ? $t('landing.per_month') : $t('landing.per_year') }}
                             </span>
                        </div>

                        <div class="space-y-4 mb-8 flex-grow">
                             <!-- Features List - Assuming plan.features is array -->
                            <div v-for="(feature, idx) in plan.features" :key="idx" class="flex items-start gap-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $t(feature) !== feature ? $t(feature) : feature }}</span>
                            </div>
                        </div>

                        <button 
                            @click="openRegisterModal(plan)"
                            class="w-full py-3 rounded-xl font-bold transition-all text-center"
                            :class="plan.is_featured ? 'bg-primary text-white hover:bg-primary-hover shadow-lg shadow-primary/20 hover:shadow-xl' : 'bg-gray-900 text-white hover:bg-gray-800'"
                        >
                            {{ $t('landing.select_plan') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>



        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                     <div class="flex items-center gap-2">
                        <Logo class="h-8 w-auto text-gray-400" :show-text="false" />
                        <span class="text-lg font-bold text-gray-900">Servio</span>
                    </div>
                    <div class="flex gap-8 text-sm text-gray-500">
                         <a href="#" class="hover:text-primary transition-colors">{{ $t('landing.about_us') }}</a>
                         <a href="#" class="hover:text-primary transition-colors">{{ $t('landing.contact_support') }}</a>
                         <a href="#pricing" class="hover:text-primary transition-colors">{{ $t('landing.plans_pricing') }}</a>
                    </div>
                    <div class="text-sm text-gray-400">
                        &copy; {{ new Date().getFullYear() }} Servio. {{ $t('landing.copyright') }}
                    </div>
                </div>
            </div>
        </footer>

        <!-- Registration Modal -->
        <Modal :show="showRegisterModal" @close="showRegisterModal = false">
             <div class="p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mx-auto mb-6 shadow-sm border border-emerald-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                     <h3 class="text-3xl font-extrabold text-gray-900 mb-2">{{ $t('landing.register_interest') }}</h3>
                     <p class="text-gray-500 font-medium text-lg">
                        {{ $t('landing.for_plan', { plan: getPlanDisplayName(selectedPlan) }) }}
                     </p>
                </div>

                <form @submit.prevent="submitInterest" class="space-y-4">
                     <div>
                        <Input 
                            v-model="form.name"
                            :label="$t('landing.full_name')"
                            required
                            :error="form.errors.name"
                        />
                    </div>
                     <div>
                        <Input 
                            v-model="form.email"
                            :label="$t('landing.email_address')"
                            type="email"
                            required
                            :error="form.errors.email"
                        />
                    </div>
                    <div>
                        <Input 
                            v-model="form.phone"
                            :label="$t('landing.phone_number')"
                            type="tel"
                            required
                            :error="form.errors.phone"
                        />
                    </div>
                     <div>
                        <Input 
                            v-model="form.restaurant_name"
                            :label="$t('landing.restaurant_name')"
                            required
                            :error="form.errors.restaurant_name"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('landing.message') }}</label>
                        <textarea 
                            v-model="form.message"
                            rows="3"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                        ></textarea>
                    </div>

                    <div class="pt-4">
                         <Button class="w-full justify-center py-3 text-lg" :loading="form.processing">
                            {{ $t('landing.submit') }}
                        </Button>
                    </div>
                </form>
             </div>
        </Modal>

        <!-- Success/Toast Notification (Simple Implementation) -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="successMessage" class="fixed bottom-0 right-0 p-6 z-50">
                <div class="bg-green-600 rounded-xl shadow-lg p-4 flex items-center gap-3 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-bold">{{ successMessage }}</p>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const props = defineProps<{
    plans: any[];
    modules: any[];
    screenshots: any[];
    deliveryProviders: any[];
    landingSettings: Record<string, any>;
}>();

const { t, locale } = useI18n();
const route = (window as any).route;

const mobileMenuOpen = ref(false);
const billingCycle = ref<'monthly' | 'yearly'>('monthly');
const showRegisterModal = ref(false);
const selectedPlan = ref<any>(null);
const successMessage = ref('');
const currentScreenshotIndex = ref(0);
let screenshotInterval: any = null;

onMounted(() => {
    if (props.screenshots && props.screenshots.length > 1) {
        screenshotInterval = setInterval(() => {
            currentScreenshotIndex.value = (currentScreenshotIndex.value + 1) % props.screenshots.length;
        }, 5000);
    }
});

onUnmounted(() => {
    if (screenshotInterval) clearInterval(screenshotInterval);
});

const nextScreenshot = () => {
    if (!props.screenshots || props.screenshots.length === 0) return;
    currentScreenshotIndex.value = (currentScreenshotIndex.value + 1) % props.screenshots.length;
};

const prevScreenshot = () => {
    if (!props.screenshots || props.screenshots.length === 0) return;
    currentScreenshotIndex.value = (currentScreenshotIndex.value - 1 + props.screenshots.length) % props.screenshots.length;
};

const form = useForm({
    plan_id: '',
    plan_name: '',
    name: '',
    email: '',
    phone: '',
    restaurant_name: '',
    message: ''
});


const toggleLanguage = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    
    // Quick URL patch for LaravelLocalization
    const currentPath = window.location.pathname; 
    const segments = currentPath.split('/'); 
    // Format: /en/servio/...
    // segments[0] is empty, [1] is locale, [2] is servio
    if (segments[1] && (segments[1] === 'en' || segments[1] === 'ar')) {
        segments[1] = newLocale;
        window.location.href = segments.join('/');
    } else {
         // Fallback if structure is weird, force proper structure
         window.location.href = `/${newLocale}/servio`;
    }
};

const getLocaleText = (obj: any) => {
    if (!obj) return '';
    if (typeof obj === 'string') return obj;
    return obj[locale.value] || obj['en'] || '';
};

const getPlanDisplayName = (plan: any) => {
    if (!plan) return '';
    if (plan.slug) {
        const localized = t('plans.' + plan.slug);
        if (localized && localized !== 'plans.' + plan.slug) {
            return localized;
        }
    }
    return plan.name || '';
};

const openRegisterModal = (plan: any) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    form.plan_name = plan.name;
    showRegisterModal.value = true;
};

const submitInterest = () => {
    form.post(route('register.interest'), {
        preserveScroll: true,
        onSuccess: () => {
            showRegisterModal.value = false;
            form.reset();
            successMessage.value = t('landing.form_success');
            setTimeout(() => successMessage.value = '', 5000);
        }
    });
};
</script>

<style scoped>
html {
    scroll-behavior: smooth;
}
</style>
