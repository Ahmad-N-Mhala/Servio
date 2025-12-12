import '../css/app.css';
// Rebuild trigger 2

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { setup } from './stores/tenant';
import axios from 'axios';
import en from './locales/en/index';
import ar from './locales/ar/index';
import fr from './locales/fr/index';
import es from './locales/es/index';
import de from './locales/de/index';
import zh from './locales/zh/index';

// Configure Axios to send CSRF token with every request
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
}

const appName = import.meta.env.VITE_APP_NAME || 'RestaurFy';

const i18n = createI18n({
    legacy: false,
    locale: 'en',
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

// Dark mode detection and setup
function setupDarkMode() {
    const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    function updateDarkMode(e: MediaQueryListEvent | MediaQueryList) {
        if (e.matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    // Set initial dark mode state
    updateDarkMode(darkModeMediaQuery);

    // Listen for changes in color scheme preference
    darkModeMediaQuery.addEventListener('change', updateDarkMode);
}

// Initialize dark mode
setupDarkMode();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')) as any,
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        app.use(plugin);
        app.use(pinia);
        app.use(i18n);
        app.use(ZiggyVue);

        setup(pinia);

        app.mount(el);
    },
    progress: {
        color: '#FF6B35',
    },
});

