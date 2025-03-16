import { Permission } from '@/Application';
import { usePage } from '@inertiajs/react';

export class Gate {
    /**
     * Determine if the current user has a specific permission
     */
    public static can(permission: Permission): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return this.hasPermission(auth.permissions, permission);
    }

    /**
     * Determine if the current user has any of the given permissions
     */
    public static canAny(permissions: Array<Permission>): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.some((permission) => this.hasPermission(auth.permissions, permission));
    }

    /**
     * Determine if the current user has all of the given permissions
     */
    public static canAll(permissions: Array<Permission>): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.every((permission) => this.hasPermission(auth.permissions, permission));
    }

    /**
     * Check if user has the permission using wildcard matching
     */
    private static hasPermission(userPermissions: Permission[], requiredPermission: Permission): boolean {
        // Direct match
        if (userPermissions.includes(requiredPermission)) {
            return true;
        }

        const parts = requiredPermission.split('.');

        for (let i = 1; i <= parts.length; i++) {
            const wildcard = (parts.slice(0, i).join('.') + '.*') as Permission;
            if (userPermissions.includes(wildcard)) {
                return true;
            }
        }

        if (parts.length > 1) {
            const basePermission = parts[0] as Permission;
            if (userPermissions.includes(basePermission)) {
                return true;
            }
        }

        return false;
    }
}
