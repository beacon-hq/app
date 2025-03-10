import {
    FeatureFlagStatus,
    Policy,
    PolicyCollection,
    PolicyDefinition,
    PolicyDefinitionCollection,
    PolicyDefinitionType,
} from '@/Application';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function localDateTime(dateTime: string | null): string {
    // If the date is null, return an empty string
    if (dateTime === null) return '';

    // format the dateTime using Intl.DateTimeFormat
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

export function localDate(date: string | null): string {
    // If the date is null, return an empty string
    if (date === null) return '';

    // format the date using Intl.DateTimeFormat
    return new Intl.DateTimeFormat('default', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(new Date(date));
}

export function localYear(date: string | null): string {
    // If the date is null, return an empty string
    if (date === null) return '';

    // format the date using Intl.DateTimeFormat
    return new Intl.DateTimeFormat('default', {
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        year: 'numeric',
    }).format(new Date(date));
}

export const flattenPolicy = function (policy: Policy, policies: PolicyCollection, keepAll: boolean = false) {
    const seenPolicies = new Set<string>();

    const flatten = (policy: Policy): Policy => {
        if (seenPolicies.has(policy.slug as string)) {
            throw new Error(`Circular dependency detected for policy: ${policy.slug}`);
        }
        seenPolicies.add(policy.slug as string);

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
                const subPolicy = policies.filter((p) => p.slug === current.subject)[0];
                if (!keepAll) {
                    return [...previous, ...(flatten(subPolicy).definition ?? [])];
                }
                return [...previous, current, ...(flatten(subPolicy).definition ?? [])];
            }
            return previous;
        }, []);

        seenPolicies.delete(policy.slug as string);
        return { ...policy, definition: definition as PolicyDefinitionCollection };
    };

    return flatten(policy);
};

// Create a function to detect if a policy contains another policy, recursively
export const containsPolicy = function (
    policy: Policy,
    target: Policy | FeatureFlagStatus,
    policies: PolicyCollection,
): boolean {
    // If the policy is the target, return true
    if (policy.id === target.id) return true;

    // If the policy has no definition, return false
    if (!policy.definition) return false;

    try {
        const flatPolicy = flattenPolicy(policy, policies, true);

        // If the policy contains the target, return true
        if (flatPolicy.definition?.some((definition) => definition.subject === target.id)) {
            return true;
        }
    } catch (e) {
        // Default to true on recursion
        return false;
    }

    // If no sub-policies contain the target, return false
    return false;
};
