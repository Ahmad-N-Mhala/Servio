import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const hasPermission = (permission: string) => {
        const { auth } = usePage().props as any;
        if (!auth?.user?.permissions) {
            return false;
        }
        return auth.user.permissions.includes(permission);
    };

    const hasRole = (role: string) => {
        const { auth } = usePage().props as any;
        if (!auth?.user?.roles) {
            return false;
        }
        return auth.user.roles.includes(role);
    };

    const hasAnyPermission = (permissions: string[]) => {
        return permissions.some(permission => hasPermission(permission));
    };

    return {
        hasPermission,
        hasRole,
        hasAnyPermission
    };
}
