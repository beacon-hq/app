import { ProductCollection } from '@/Application';
import ApplicationLogo from '@/Components/ApplicationLogo';
import NavMenu from '@/Components/NavMenu';
import Pricing from '@/Pages/Home/Components/Pricing';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import React, { useRef } from 'react';

export default function Index({ auth, products, docsUrl }: PageProps & { products: ProductCollection }) {
    useScrollToLocation();
    const pricingRef = useRef(null);

    return (
        <>
            <Head title="Choose a Plan" />
            <NavMenu auth={auth} docsUrl={docsUrl} />

            <div>
                <Link href="/">
                    <ApplicationLogo className="h-48 w-96 fill-current text-gray-500 mx-auto mt-12" />
                </Link>
            </div>

            <section id="pricing" className="scroll-mt-8" ref={pricingRef}>
                <div className="prose dark:prose-invert w-fit mx-auto text-center">
                    <h1 className="mb-0">Choose a Plan to Get Started</h1>
                    <p className="text-sm mt-0">Cancel or change your plan at any time.</p>
                </div>
                <Pricing products={products} />
            </section>
        </>
    );
}
