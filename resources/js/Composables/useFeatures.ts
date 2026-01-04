import { usePage } from '@inertiajs/vue3';

export function useFeatures() {
    const page = usePage();

    const hasFeature = (feature: string): boolean => {
        const restaurant = (page.props as any).current_restaurant;
        const subscription = (page.props as any).current_subscription;

        // 1. Check Plan Features (Primary Source)
        const planFeatures = subscription?.plan?.enabled_features || [];
        if (planFeatures.includes(feature)) {
            return true;
        }

        // 2. Check Restaurant Settings (Individual Overrides)
        const settingsFeatures = restaurant?.settings?.enabled_features || [];
        if (settingsFeatures.includes(feature)) {
            return true;
        }

        return false;
    };

    return {
        hasFeature,
    };
}
