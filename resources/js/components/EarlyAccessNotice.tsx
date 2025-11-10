import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import React from 'react';

const EarlyAccessNotice = ({ className }: { className?: string }) => {
    const { isDev } = usePage().props as { isDev: boolean };
    if (isDev) {
        return null;
    }

    return (
        <>
            {window.location.hostname !== 'beaconhq.io' && !navigator.userAgent.includes('Pest/') && (
                <div className={cn('bg-yellow-100 w-full text-center', className)}>
                    <div className="max-w-7xl mx-auto px-12 py-4">
                        <p className="text-yellow-800 text-sm">
                            <strong className="font-semibold">Early Access Notice:</strong> You are using the early
                            access version of Beacon HQ.
                            <strong>Changes will persist in your production system.</strong>
                        </p>
                    </div>
                </div>
            )}
        </>
    );
};

export default EarlyAccessNotice;
