import { ProductCollection, Subscription } from '@/Application';
import ProductCard from '@/components/ProductCard';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { motion, useReducedMotion, useScroll, useTransform } from 'framer-motion';
import React, { useRef } from 'react';

export default function Pricing({
    products,
    activeSubscription,
}: {
    products: ProductCollection;
    activeSubscription?: Subscription;
}) {
    const pricingRef = useRef<HTMLDivElement | null>(null);
    const prefersReducedMotion = useReducedMotion();
    const { scrollYProgress } = useScroll({
        target: pricingRef,
        offset: ['start end', 'end start'],
    });

    const height = useTransform(scrollYProgress, [0, 0.5, 1], ['65vh', '32vh', '3vh']);

    return (
        <>
            <motion.div
                className="fixed bottom-0 left-0 w-full opacity-25 pointer-events-none"
                style={{
                    height,
                    background: 'linear-gradient(45deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                    clipPath: 'ellipse(80% 50% at 50% 100%)',
                }}
                {...(!prefersReducedMotion && {
                    animate: {
                        background: [
                            'linear-gradient(45deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                            'linear-gradient(225deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                        ],
                    },
                    transition: {
                        duration: 3,
                        repeat: Infinity,
                        repeatType: 'reverse',
                        ease: 'linear',
                    },
                })}
            />
            <div>
                {/* Pricing Table */}
                <section className="py-12" ref={pricingRef}>
                    <div className="max-w-7xl mx-auto px-6">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                            {products.map((product) => (
                                <ProductCard
                                    key={product.name}
                                    product={product}
                                    activeSubscription={activeSubscription}
                                    onClick={() => {
                                        router.post(route('checkout.change', product.id));
                                    }}
                                />
                            ))}
                        </div>

                        <p className="mt-6 text-center text-primary">
                            Need more than 2 million evaluations?{' '}
                            <a href="mailto:sales@beaconhq.io" className="underline">
                                Contact us
                            </a>{' '}
                            for a custom Enterprise plan.
                        </p>

                        <div className="text-center text-sm text-gray-400 mt-8">
                            <em>Credit Card Required.</em> After your three-month free trial, the Solo plan continues at
                            $5/month. Cancel anytime.
                        </div>
                    </div>
                </section>

                {/* Feature Checklist */}
                <section className="bg-secondary w-full py-20">
                    <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 px-6">
                        <div>
                            <h2 className="text-3xl font-bold text-primary mb-6">Focus on building, not billing.</h2>
                            <p className="text-lg text-primary">
                                Every Beacon plan gives you the freedom to launch, test, and ship without worrying about
                                seat limits or project caps.
                            </p>
                        </div>

                        <div>
                            <ul className="space-y-4">
                                {features.map((feature, index) => (
                                    <li key={index} className="flex items-start">
                                        <div className="flex-shrink-0">
                                            <svg
                                                className="w-6 h-6 text-green-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <span className="ml-3 text-primary">{feature}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

const features = [
    'Unlimited Feature Flags',
    'Unlimited Applications',
    'Unlimited Environments',
    'Unlimited Team Members',
    'Real-Time Feature Flag Updates',
    'Analytics',
];
