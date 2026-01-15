<template>
    <div :dir="locale === 'ar' ? 'rtl' : 'ltr'" class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 sm:px-6 py-12 relative">
        <!-- Language Switcher -->
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 rtl:right-auto rtl:left-4 rtl:sm:left-6">
            <button 
                @click="toggleLanguage" 
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
                <span class="uppercase">{{ locale }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                </svg>
            </button>
        </div>

        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-6">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $t('subscription.expired_title', 'Plan Expired') }}
                </h2>
                
                <p class="text-gray-600 dark:text-gray-300 mb-8">
                    {{ $t('subscription.expired_message', 'Your subscription plan has expired. Access to the system is restricted.') }}
                    <br class="mb-2" />
                    {{ $t('subscription.contact_support', 'Please contact the support team to renew your subscription.') }}
                </p>

                <div class="space-y-4 bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg mb-8">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">
                        {{ $t('landing.contact_us', 'Contact Support') }}
                    </h3>
                    
                    <a :href="'mailto:' + supportEmail" class="flex items-center gap-3 text-gray-600 dark:text-gray-300 hover:text-primary transition-colors justify-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ supportEmail }}</span>
                    </a>
                    
                    <a :href="'tel:' + supportPhone" class="flex items-center gap-3 text-gray-600 dark:text-gray-300 hover:text-primary transition-colors justify-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span dir="ltr">{{ supportPhone }}</span>
                    </a>
                </div>

                <div class="flex justify-center">
                    <button 
                        @click="logout" 
                        class="px-6 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        {{ $t('auth.logout', 'Sign Out') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

// Ziggy
declare const route: any;

const { t, locale } = useI18n();
const page = usePage();

const supportEmail = computed(() => page.props.system_settings?.support_email || 'support@kenildock.com');
const supportPhone = computed(() => page.props.system_settings?.support_phone || '+9715049460976');

const logout = () => {
    router.post(route('logout'));
};

const toggleLanguage = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    
    // Quick URL patch for LaravelLocalization
    const currentPath = window.location.pathname; 
    const segments = currentPath.split('/'); 
    // Format: /en/servio/... or /ar/servio/...
    // segments[0] is empty, [1] is locale
    if (segments[1] && (segments[1] === 'en' || segments[1] === 'ar')) {
        segments[1] = newLocale;
        window.location.href = segments.join('/');
    } else {
         // Fallback if structure is weird, force proper structure
         window.location.href = `/${newLocale}/servio`;
    }
};
</script>
