import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { setup } from './stores/tenant';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import en from './locales/en/index';
import ar from './locales/ar/index';
import fr from './locales/fr/index';
import es from './locales/es/index';
import de from './locales/de/index';
import zh from './locales/zh/index';

// Configure Axios to send CSRF token with every request
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// We rely on the XSRF-TOKEN cookie which Laravel maintains automatically.
// Do NOT manually set X-CSRF-TOKEN from the meta tag here, as it becomes stale
// and causes 419 errors on long-lived pages (like KDS/Status screens) or after login.

// Configure Laravel Echo for real-time broadcasting (only if credentials are available)
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: any;
    }
}

window.Pusher = Pusher;

// Only initialize Echo if Pusher credentials are configured
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        console.log('✅ Laravel Echo initialized successfully');
    } catch (error) {
        console.warn('⚠️ Failed to initialize Laravel Echo:', error);
    }
} else {
    console.info('ℹ️ Laravel Echo not initialized - Pusher credentials not configured. Using fallback polling.');
}

const appName = import.meta.env.VITE_APP_NAME || 'Servio';

const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'en',
    fallbackLocale: 'en',
    messages: {
        en,
        ar,
        fr,
        es,
        de,
        zh,
    },
});

// Dark mode disabled - forcing light mode as per user request
function disableDarkMode() {
    document.documentElement.classList.remove('dark');
}
disableDarkMode();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
        .catch((error) => {
            // Handle chunk load errors (e.g., after deployment when old chunks are deleted)
            if (error.message && (error.message.includes('Failed to fetch') || error.message.includes('404'))) {
                console.warn('⚠️ Chunk load failed, reloading page to fetch new version...');
                window.location.reload();
                return Promise.reject(error);
            }
            throw error;
        }) as any,
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        app.use(plugin);
        app.use(pinia);
        app.use(i18n);
        app.use(ZiggyVue);

        // Merge backend translations
        const backendTranslations = props.initialPage.props.translations as any;
        if (backendTranslations) {
            if (backendTranslations.en) {
                i18n.global.mergeLocaleMessage('en', backendTranslations.en);
            }
            if (backendTranslations.ar) {
                i18n.global.mergeLocaleMessage('ar', backendTranslations.ar);
            }
        }

        setup(pinia);

        app.mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});

// Comprehensive CSRF token management to prevent 419 errors
import { router } from '@inertiajs/vue3';


// Global error handler for 419 CSRF token mismatch errors

// Global error handler for 419 CSRF token mismatch errors
router.on('error', (event: any) => {
    // Check if it's a 419 error (CSRF token mismatch)
    // The error event from Inertia doesn't have response directly, but we can check the errors object
    // If errors is empty and the request failed, it's likely a 419
    if (event.detail && event.detail.errors && Object.keys(event.detail.errors).length === 0) {
        console.error('Possible CSRF token mismatch detected (419 error)');

        // Show user-friendly message
        alert('Your session has expired. The page will reload to refresh your session. Please try your action again.');

        // Reload the page to get a fresh CSRF token
        window.location.reload();
    }
});

// Update translations on navigation (since setup() only runs on initial load)
router.on('success', (event: any) => {
    const backendTranslations = event.detail.page.props.translations as any;
    if (backendTranslations) {
        if (backendTranslations.en) {
            i18n.global.mergeLocaleMessage('en', backendTranslations.en);
        }
        if (backendTranslations.ar) {
            i18n.global.mergeLocaleMessage('ar', backendTranslations.ar);
        }
    }
});
