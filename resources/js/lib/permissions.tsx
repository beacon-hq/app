import { Permission } from '@/Application';
import { usePage } from '@inertiajs/react';

export class Gate {
    /**
     * Determine if the current user has a specific permission
     */
    public static can(permission: Permission | string): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return this.hasPermission(auth.permissions, permission);
    }

    /**
     * Determine if the current user has any of the given permissions
     */
    public static canAny(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.some((permission) => this.hasPermission(auth.permissions, permission));
    }

    public static canOnly(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        // Check to see if the user has any permissions that are not in the list
        return auth.permissions.every((permission) => this.hasPermission(permissions, permission));
    }

    /**
     * Determine if the current user has all of the given permissions
     */
    public static canAll(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.every((permission) => this.hasPermission(auth.permissions, permission));
    }

    /**
     * Check if user has the permission using wildcard matching
     */
    private static hasPermission(
        userPermissions: Permission | string[],
        requiredPermission: Permission | string,
    ): boolean {
        // Direct match
        if (userPermissions.includes(requiredPermission as Permission)) {
            return true;
        }

        const parts = requiredPermission.split('.');

        // Check if user has more specific permission than required (wildcard matching)
        for (let i = 1; i <= parts.length; i++) {
            const wildcard = (parts.slice(0, i).join('.') + '.*') as Permission;
            if (userPermissions.includes(wildcard)) {
                return true;
            }
        }

        // Check if required permission is a wildcard and user has any matching specific permissions
        if (requiredPermission.endsWith('.*')) {
            const prefix = requiredPermission.slice(0, -2); // Remove '.*'
            for (const userPerm of userPermissions) {
                if (typeof userPerm === 'string' && userPerm.startsWith(prefix) && userPerm !== requiredPermission) {
                    return true;
                }
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
