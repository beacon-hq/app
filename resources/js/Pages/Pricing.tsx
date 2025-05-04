import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { cn } from '@/lib/utils';
import { motion, useReducedMotion, useScroll, useTransform } from 'framer-motion';
import React, { useRef } from 'react';

type RGBColor = {
    r: number;
    g: number;
    b: number;
};

export default function Pricing() {
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
                    clipPath: 'ellipse(80% 60% at 50% 100%)',
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
                            {plans.map((plan) => (
                                <Card
                                    key={plan.name}
                                    className={cn(
                                        'text-center relative flex flex-col justify-between',
                                        'hover:[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent motion-safe:animate-border',
                                        'hover:motion-safe:scale-110',
                                        'transition-all duration-500',
                                        'relative',
                                    )}
                                >
                                    <CardContent className="rounded-xl shadow-lg bg-secondary">
                                        <CardHeader>
                                            {plan.free && (
                                                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-[#07e38f] text-white text-xs font-bold uppercase scale-120 px-3 py-1 rounded-full">
                                                    3 Months Free
                                                </div>
                                            )}
                                            <CardTitle>
                                                <h2 className="text-xl font-semibold text-primary">{plan.name}</h2>
                                            </CardTitle>
                                        </CardHeader>
                                        <CardDescription>
                                            <p className="mt-2 text-3xl font-bold text-primary">{plan.price}</p>
                                            <p className="mt-2 text-primary">{plan.description}</p>
                                            <p className="mt-2 text-primary/60 text-sm">{plan.extra}</p>
                                            <a href="/register">
                                                <span className="absolute inset-0"></span>
                                                <Button className="mt-4 cursor-pointer">
                                                    {plan.free && 'Start Free Trial'}
                                                    {!plan.free && 'Choose Plan'}
                                                </Button>
                                            </a>
                                        </CardDescription>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        <p className="mt-6 text-center text-primary">
                            Need more than 2 million evaluations?{' '}
                            <a href="mailto:sales@beacon-hq.dev" className="underline">
                                Contact us
                            </a>{' '}
                            for a custom Enterprise plan.
                        </p>

                        <div className="text-center text-sm text-gray-400 mt-8">
                            After your three-month free trial, the Solo plan continues at $5/month. Cancel anytime.
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

                {/* FAQ Section */}
                <section className="py-20">
                    <div className="w-3xl max-w-full mx-auto px-6">
                        <h2 className="text-3xl font-bold text-primary text-center mb-12">
                            Frequently Asked Questions
                        </h2>
                        <Accordion type="single" collapsible>
                            {faqs.map((faq, index) => (
                                <AccordionItem key={index} value={`item-${index}`}>
                                    <AccordionTrigger className="text-lg font-semibold text-primary">
                                        {faq.question}
                                    </AccordionTrigger>
                                    <AccordionContent className="text-primary">{faq.answer}</AccordionContent>
                                </AccordionItem>
                            ))}
                        </Accordion>
                    </div>
                </section>

                {/* Final CTA */}
                <motion.section
                    className="w-full py-20 text-center text-primary relative"
                    style={{
                        background:
                            'linear-gradient(15deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                    }}
                    {...(!prefersReducedMotion && {
                        animate: {
                            background: [
                                'linear-gradient(0deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                                'linear-gradient(30deg, #00e281, #00e7a4, #00ecc7, #00f1ed, #00d8f5, #00b9fa, #009aff)',
                            ],
                        },
                        transition: {
                            duration: 30,
                            repeat: Infinity,
                            ease: 'linear',
                        },
                    })}
                >
                    <h2 className="text-4xl font-bold mb-4 text-white">Get three months free, then just $5/month.</h2>
                    <p className="text-lg mb-8 text-white">
                        Unlimited flags, apps, environments, and users — for the price of a coffee.
                    </p>
                    <a href="/register">
                        <Button size="lg">Start Free Trial</Button>
                    </a>
                </motion.section>
            </div>
        </>
    );
}

const plans = [
    {
        name: 'Solo',
        price: '$5/mo',
        description: '10,000 feature flag evaluations',
        extra: '$5/10,000 evaluations after that',
        free: true,
    },
    {
        name: 'Developer',
        price: '$19/mo',
        extra: '$4/10,000 evaluations after that',
        description: '100,000 feature flag evaluations',
    },
    {
        name: 'Growth',
        price: '$79/mo',
        extra: '$35/100,000 evaluations after that',
        description: '500,000 feature flag evaluations',
    },
    {
        name: 'Scale',
        price: '$199/mo',
        extra: '$25/100,000 evaluations after that',
        description: '2M feature flag evaluations',
    },
];

const features = [
    'Unlimited Feature Flags',
    'Unlimited Applications',
    'Unlimited Environments',
    'Unlimited Team Members',
    'Real-Time Feature Flag Updates',
    'Analytics',
];

const faqs = [
    {
        question: 'What happens after my three-month free trial?',
        answer: 'You can continue using Beacon for just $5/month. We’ll remind you before your trial ends so you can cancel with a single click.',
    },
    {
        question: 'What counts as a feature flag evaluation?',
        answer: 'An evaluation happens every time your app requests the value of a feature flag from Beacon. Cached calls do not count.',
    },
    {
        question: 'What happens if I go over my feature flag evaluation limit?',
        answer: 'You’ll be billed for every 10,000 or 100,000 evaluations (based on your plan). We’ll notify you when you’re close.',
    },
    {
        question: 'Can I have unlimited users and environments?',
        answer: 'Yes. All plans include unlimited users, apps, flags, and environments.',
    },
    {
        question: 'Is there a free trial?',
        answer: 'Yes — the Solo plan gives you three months free, upgrade or downgrade your plan at any time.',
    },
    {
        question: 'When can I cancel my subscription?',
        answer: 'You can cancel your subscription at any time. Your plan will expire at the end of the current billing period.',
    },
    {
        question: 'Can I self-host Beacon?',
        answer: 'Yes, absolutely, Beacon is open source.',
    },
];
