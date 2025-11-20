import Footer from '@/components/Footer';
import NavMenu from '@/components/NavMenu';
import useAnalytics from '@/hooks/use-analytics';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import React, { PropsWithChildren, useEffect } from 'react';

export default function HomeLayout({ className, children }: PropsWithChildren & { className?: string }) {
    const features = usePage().props.features;

    useAnalytics(features['analytics.enabled'], features['analytics.src']);

    return (
        <div className="flex flex-col h-screen">
            <NavMenu />
            <main
                className={cn(
                    'flex grow flex-col items-center pt-0 sm:justify-center text-primary dark:text-secondary bg-background dark:bg-secondary',
                    className,
                )}
                data-test="main"
            >
                {children}
            </main>
            <Footer />
        </div>
    );
}
