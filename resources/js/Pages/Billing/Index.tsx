import { Product, ProductCollection } from '@/Application';
import ProductCard from '@/Components/ProductCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import React from 'react';

export default function Index({
    products,
    subscription,
}: PageProps & { products: ProductCollection; subscription: Product }) {
    console.log(subscription);
    return (
        <AuthenticatedLayout breadcrumbs={[{ name: 'Billing', icon: 'CreditCard' }]}>
            <Head title="Billing Settings" />
            <div className="py-10">
                <div className="p-6 text-gray-900">
                    <h1 className="text-2xl font-semibold mb-6">Manage Your Subscription</h1>
                    <p className="mb-12">You can upgrade or downgrade your plan at any time.</p>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {products.map((product) => {
                            return (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                    onClick={() => {
                                        router.put(route('checkout.update', product.id));
                                    }}
                                    activeSubscription={subscription}
                                />
                            );
                        })}
                    </div>
                    <p className="mt-6 text-center text-primary">
                        Need more than 2 million evaluations?{' '}
                        <a href="mailto:sales@beacon-hq.dev" className="underline">
                            Contact us
                        </a>{' '}
                        for a custom Enterprise plan.
                    </p>
                    <p className="text-xs mt-12 text-center text-primary">
                        Your bill will be prorated based on the time remaining in your current billing cycle. Any
                        current trial period will be cancelled upon upgrading.
                    </p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
