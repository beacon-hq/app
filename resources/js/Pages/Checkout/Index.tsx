import { ProductCollection } from '@/Application';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Footer from '@/Components/Footer';
import NavMenu from '@/Components/NavMenu';
import Pricing from '@/Pages/Home/Components/Pricing';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import React, { useRef } from 'react';

export default function Index() {
    useScrollToLocation();
    const pricingRef = useRef(null);
    const products = usePage().props.products;

    return (
        <>
            <Head title="Choose a Plan" />
            <NavMenu />

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
                <Pricing products={products as ProductCollection} />
            </section>

            <Footer />
        </>
    );
}
