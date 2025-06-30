import { Permission } from '@/Application';
import { Auth } from '@/types';
import { usePage } from '@inertiajs/react';

export class Gate {
    public static can(permission: Permission | string): boolean {
        const auth = usePage().props.auth as Auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return this.hasPermission(auth.permissions, permission);
    }

    public static canAny(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth as Auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.some((permission) => this.hasPermission(auth.permissions, permission));
    }

    public static canOnly(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth as Auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return auth.permissions.every((permission) => this.hasPermission(permissions, permission));
    }

    public static canAll(permissions: Array<Permission | string>): boolean {
        const auth = usePage().props.auth as Auth;

        if (!auth.user || !auth.permissions) {
            return false;
        }

        return permissions.every((permission) => this.hasPermission(auth.permissions, permission));
    }

    private static hasPermission(
        userPermissions: Permission | string[],
        requiredPermission: Permission | string,
    ): boolean {
        if (userPermissions.includes(requiredPermission as Permission)) {
            return true;
        }

        const parts = requiredPermission.split('.');

        for (let i = 1; i <= parts.length; i++) {
            const wildcard = (parts.slice(0, i).join('.') + '.*') as Permission;
            if (userPermissions.includes(wildcard)) {
                return true;
            }
        }

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
