import ApplicationLogo from '@/Components/ApplicationLogo';
import EarlyAccessNotice from '@/Components/EarlyAccessNotice';
import Footer from '@/Components/Footer';
import NavMenu from '@/Components/NavMenu';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
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
                data-dusk="main"
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
