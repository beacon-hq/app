import ApplicationLogo from '@/components/ApplicationLogo';
import EarlyAccessNotice from '@/components/EarlyAccessNotice';
import Footer from '@/components/Footer';
import NavMenu from '@/components/NavMenu';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import React, { PropsWithChildren } from 'react';

export default function Guest({ className, children }: PropsWithChildren & { className?: string }) {
    return (
        <div className="flex flex-col h-screen">
            <EarlyAccessNotice className="top-17" />
            {document.cookie.indexOf('hide-menu-bar') === -1 && <NavMenu showLogo />}
            <main
                className={cn(
                    'flex grow flex-col items-center pt-6 sm:justify-center sm:pt-0 text-primary dark:text-secondary bg-background dark:bg-secondary',
                    className,
                )}
                data-test="main"
            >
                <div>
                    <Link href="/">
                        <ApplicationLogo className="h-48 w-96 mt-12 fill-current text-gray-500" />
                    </Link>
                </div>

                {children}
            </main>
            <Footer />
        </div>
    );
}
