import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { DateTime, Duration } from 'luxon';

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
