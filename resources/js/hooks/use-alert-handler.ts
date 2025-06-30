import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

type Alert = {
    status: 'success' | 'error' | 'warning' | 'info';
    message: string;
    timestamp: number;
} | null;

const DISPLAYED_ALERT_KEY = 'beacon_displayed_alert';

export function useAlertHandler() {
    const { alert } = usePage().props as { alert: Alert };
    const displayedAlertRef = useRef<string | null>(
        typeof window !== 'undefined' ? sessionStorage.getItem(DISPLAYED_ALERT_KEY) : null
    );

    useEffect(() => {
        if (!alert?.message) return;

        const alertKey = `${alert.status}-${alert.message}-${alert.timestamp}`;

        console.log('Alert Key:', alertKey);
        console.log('Ref Current:', displayedAlertRef.current);

        if (displayedAlertRef.current === alertKey) {
            return;
        }

        displayedAlertRef.current = alertKey;
        if (typeof window !== 'undefined') {
            sessionStorage.setItem(DISPLAYED_ALERT_KEY, alertKey);
        }

        console.log('Ref Current Updated:', displayedAlertRef.current);

        const toastFunctions = {
            success: toast.success,
            error: toast.error,
            warning: toast.warning,
            info: toast.info,
        } as const;

        const toastFunction = toastFunctions[alert.status] || toast.info;
        toastFunction(alert.message);
    }, [alert?.status, alert?.message, alert?.timestamp]);
}
