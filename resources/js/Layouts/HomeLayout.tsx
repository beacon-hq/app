import Footer from '@/components/Footer';
import NavMenu from '@/components/NavMenu';
import { cn } from '@/lib/utils';
import React, { PropsWithChildren } from 'react';

export default function HomeLayout({ className, children }: PropsWithChildren & { className?: string }) {
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
