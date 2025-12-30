import '../css/app.css';

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

const appName = import.meta.env.VITE_APP_NAME || 'Servio';

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

// Dark mode disabled - forcing light mode as per user request
function disableDarkMode() {
    document.documentElement.classList.remove('dark');
}
disableDarkMode();

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
        color: '#4F46E5',
    },
});

