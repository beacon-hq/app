import {
    FeatureFlagStatus,
    Organization,
    Policy,
    PolicyCollection,
    PolicyDefinition,
    PolicyDefinitionCollection,
    PolicyDefinitionType,
    Team,
} from '@/Application';
import { router } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function localDateTime(dateTime?: string | null): string {
    if (dateTime === null || dateTime === undefined) return '';

    return new Intl.DateTimeFormat('default', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(new Date(dateTime));
}

export function localDate(date?: string | null | undefined): string {
    if (date === null || date === undefined) return '';

    return new Intl.DateTimeFormat('default', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(new Date(date));
}

export function localYear(date: string | null): string {
    if (date === null) return '';

    return new Intl.DateTimeFormat('default', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        year: 'numeric',
    }).format(new Date(date));
}

export const flattenPolicy = function (policy: Policy, policies: PolicyCollection, keepAll: boolean = false) {
    const seenPolicies = new Set<string>();

    const flatten = (policy: Policy): Policy => {
        if (seenPolicies.has(policy.id as string)) {
            throw new Error(`Circular dependency detected for policy: ${policy.id}`);
        }
        seenPolicies.add(policy.id as string);

        const definition = policy.definition?.reduce(function (
            previous: PolicyDefinition[],
            current: PolicyDefinition,
        ): PolicyDefinition[] {
            if (
                current.type === PolicyDefinitionType.EXPRESSION ||
                (keepAll && current.type === PolicyDefinitionType.OPERATOR)
            ) {
                return [...previous, current];
            }

            if (current.type === PolicyDefinitionType.POLICY) {
                const subPolicy = policies.filter((p) => p.id === current.subject)[0];
                if (!keepAll) {
                    return [...previous, ...(flatten(subPolicy).definition ?? [])];
                }
                return [...previous, current, ...(flatten(subPolicy).definition ?? [])];
            }
            return previous;
        }, []);

        seenPolicies.delete(policy.id as string);
        return { ...policy, definition: definition as PolicyDefinitionCollection };
    };

    return flatten(policy);
};

export const containsPolicy = function (
    policy: Policy,
    target: Policy | FeatureFlagStatus,
    policies: PolicyCollection,
): boolean {
    if (policy.id === target.id) return true;

    if (!policy.definition) return false;

    try {
        const flatPolicy = flattenPolicy(policy, policies, true);

        if (flatPolicy.definition?.some((definition) => definition.subject === target.id)) {
            return true;
        }
    } catch (e) {
        return false;
    }

    return false;
};

export const chooseTeam = (team: Team) => {
    router.post(`${route('teams.choose')}`, { team: team }, { preserveState: false });
};

export const chooseOrganization = (organization: Organization) => {
    router.post(
        route('organizations.choose'),
        { id: organization.id },
        {
            preserveState: false,
            onError: () => router.get(route('dashboard')),
        },
    );
};
