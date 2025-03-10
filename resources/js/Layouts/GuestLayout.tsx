import ApplicationLogo from '@/Components/ApplicationLogo';
import Footer from '@/Components/Footer';
import { Link } from '@inertiajs/react';
import React, { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex flex-col h-screen">
            <main className="flex grow flex-col items-center pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
                <div>
                    <Link href="/">
                        <ApplicationLogo className="h-48 w-96 fill-current text-gray-500" />
                    </Link>
                </div>

                {children}
            </main>
            <Footer />
        </div>
    );
}
