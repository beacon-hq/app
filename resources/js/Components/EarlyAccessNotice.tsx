import { cn } from '@/lib/utils';
import React from 'react';

const EarlyAccessNotice = ({ className }: { className?: string }) => {
    return (
        <>
            {window.location.hostname !== 'beacon-hq.dev' && !navigator.userAgent.includes('Laravel/Dusk') && (
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
