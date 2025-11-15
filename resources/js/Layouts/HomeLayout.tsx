import Footer from '@/components/Footer';
import NavMenu from '@/components/NavMenu';
import { cn } from '@/lib/utils';
import React, { PropsWithChildren, useEffect } from 'react';

export default function HomeLayout({ className, children }: PropsWithChildren & { className?: string }) {
    useEffect(() => {
        // @ts-ignore
        let _mtm = (globalThis._mtm = globalThis?._mtm ?? []);

        _mtm.push({ 'mtm.startTime': Date.now(), event: 'mtm.Start' });
        (function () {
            let d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = 'https://analytics.beaconhq.io/js/container_4h5LNw9x.js';
            s.parentNode?.insertBefore(g, s);
        })();
    });

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
