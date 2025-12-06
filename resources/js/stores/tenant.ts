import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTenantStore = defineStore('tenant', () => {
    const currentRestaurant = ref<any>(null);
    const locale = ref<string>('en');
    const isRtl = ref<boolean>(false);

    const setRestaurant = (restaurant: any) => {
        currentRestaurant.value = restaurant;
    };

    const setLocale = (newLocale: string) => {
        locale.value = newLocale;
    };

    const setIsRtl = (rtl: boolean) => {
        isRtl.value = rtl;
    };

    return {
        currentRestaurant,
        locale,
        isRtl,
        setRestaurant,
        setLocale,
        setIsRtl,
    };
});

export function setup(_pinia: any) {
    // Setup logic if needed
}

