import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Vue composable for checking user permissions.
 * 
 * Usage:
 *   const { can, canAny, role } = usePermissions();
 *   if (can('products.create')) { ... }
 */
export function usePermissions() {
    const page = usePage();
    const user = computed(() => page.props.auth?.user);
    const role = computed(() => user.value?.role || 'siswa');
    const permissions = computed(() => user.value?.permissions || {});

    /**
     * Check if the current user has a specific permission.
     * Admin and Master roles always return true.
     */
    const can = (permission) => {
        if (['admin', 'master'].includes(role.value)) return true;
        return permissions.value[permission] === true;
    };

    /**
     * Check if the current user has any of the given permissions.
     */
    const canAny = (perms) => perms.some(p => can(p));

    /**
     * Check if the current user has all of the given permissions.
     */
    const canAll = (perms) => perms.every(p => can(p));

    return { can, canAny, canAll, role, permissions, user };
}
