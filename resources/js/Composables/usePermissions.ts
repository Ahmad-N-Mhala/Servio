import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const hasPermission = (permission: string) => {
        const user = usePage().props.auth.user;
        if (!user || !user.permissions) {
            return false;
        }
        return user.permissions.includes(permission);
    };

    const hasRole = (role: string) => {
        const user = usePage().props.auth.user;
        if (!user || !user.roles) {
            return false;
        }
        return user.roles.includes(role);
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
