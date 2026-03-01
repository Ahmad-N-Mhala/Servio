import { createI18n } from 'vue-i18n';
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    messages: {
        en: {
            plans: {
                "Multi-Store Plan_name": "Test translated"
            }
        }
    }
});
console.log(i18n.global.t('plans.Multi-Store Plan_name'));
