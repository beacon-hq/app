import { useEffect, useRef, useState } from 'react';

export function useOnboardingStatus(isActive: boolean) {
    const [apiOnboardingStatus, setApiOnboardingStatus] = useState<boolean | null>(null);
    const interval = useRef<any>(null);

    useEffect(() => {
        if (!isActive) return;

        const pollOnboardingStatus = async () => {
            try {
                const response = await fetch('/api/onboarding/status');
                const data = await response.json();
                setApiOnboardingStatus(data.onboarding);
            } catch (error) {
                console.error('Failed to fetch onboarding status:', error);
            }
        };

        interval.current = setInterval(pollOnboardingStatus, 3000);
    }, [isActive]);

    useEffect(() => {
        if (apiOnboardingStatus === false || isActive === false) {
            clearInterval(interval.current);
        }
    }, [apiOnboardingStatus, isActive]);

    return { apiOnboardingStatus };
}
