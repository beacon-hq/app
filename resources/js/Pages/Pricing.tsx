import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { cn } from '@/lib/utils';
import React, { useEffect, useRef, useState } from 'react';

type RGBColor = {
    r: number;
    g: number;
    b: number;
};

export default function Pricing() {
    const bgCanvas = useRef<HTMLCanvasElement | null>(null);
    const pricingRef = useRef<HTMLDivElement | null>(null);
    const [canvasHeight, setCanvasHeight] = useState(30);
    const scrollTimeoutRef = useRef<number>();
    const animationFrameRef = useRef<number>();
    const lastScrollTime = useRef<number>(0);

    useEffect(() => {
        // Handle scroll effect for canvas with variable speed
        const handleScroll = () => {
            const now = Date.now();

            // Reduce scroll delay for upward movement
            const pricingRect = pricingRef.current?.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const isMovingUp = window.scrollY < lastScrollY.current;
            // Faster response when scrolling up
            const scrollDelay = isMovingUp ? 8 : pricingRect && pricingRect.top <= windowHeight * 0.5 ? 16 : 12;

            if (now - lastScrollTime.current < scrollDelay) {
                return;
            }
            lastScrollTime.current = now;
            lastScrollY.current = window.scrollY;

            if (scrollTimeoutRef.current) {
                cancelAnimationFrame(scrollTimeoutRef.current);
            }

            scrollTimeoutRef.current = requestAnimationFrame(() => {
                if (pricingRef.current) {
                    const pricingRect = pricingRef.current.getBoundingClientRect();
                    const windowHeight = window.innerHeight;

                    if (pricingRect.top <= windowHeight && pricingRect.top >= 0) {
                        const scrollProgress = pricingRect.top / windowHeight;
                        // Increase speed multiplier for upward movement
                        const speedMultiplier = isMovingUp ? 2.5 : scrollProgress < 0.5 ? 1.5 : 1;
                        const newHeight = 32 + scrollProgress * 35 * speedMultiplier; // Reduced from 55 to 35
                        setCanvasHeight(Math.min(65, newHeight)); // Changed from 100 to 60
                    } else if (pricingRect.top > windowHeight) {
                        setCanvasHeight(65); // Changed from 100 to 60
                    } else {
                        setCanvasHeight(3);
                    }
                }
            });
        };

        const lastScrollY = { current: window.scrollY };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();

        return () => {
            window.removeEventListener('scroll', handleScroll);
            if (scrollTimeoutRef.current) {
                cancelAnimationFrame(scrollTimeoutRef.current);
            }
        };
    }, []);

    useEffect(() => {
        const c = bgCanvas.current;
        if (c !== null) {
            const $ = c.getContext('2d');
            if (!$) return;

            const pixelSize = 2; // Increase pixel size to reduce drawing operations
            const width = Math.ceil(35 / pixelSize);
            const height = Math.ceil(35 / pixelSize);

            c.width = width;
            c.height = height;

            // Define gradient colors from the logo
            const gradientColors: RGBColor[] = [
                { r: 0, g: 226, b: 129 }, // #00e281
                { r: 0, g: 231, b: 164 }, // #00e7a4
                { r: 0, g: 236, b: 199 }, // #00ecc7
                { r: 0, g: 241, b: 237 }, // #00f1ed
                { r: 0, g: 216, b: 245 }, // #00d8f5
                { r: 0, g: 185, b: 250 }, // #00b9fa
                { r: 0, g: 154, b: 255 }, // #009aff
            ];

            // Function to interpolate between two colors
            const interpolateColor = (color1: RGBColor, color2: RGBColor, factor: number) => {
                return {
                    r: Math.floor(color1.r + factor * (color2.r - color1.r)),
                    g: Math.floor(color1.g + factor * (color2.g - color1.g)),
                    b: Math.floor(color1.b + factor * (color2.b - color1.b)),
                };
            };

            // Function to get a color from the gradient based on position
            const getGradientColor = (position: number) => {
                // Ensure position is between 0 and 1
                position = Math.max(0, Math.min(1, position));

                // Calculate which segment of the gradient we're in
                const segment = position * (gradientColors.length - 1);
                const index = Math.floor(segment);
                const factor = segment - index;

                // If we're at the last color, return it
                if (index >= gradientColors.length - 1) {
                    return gradientColors[gradientColors.length - 1];
                }

                // Otherwise interpolate between two adjacent colors
                return interpolateColor(gradientColors[index], gradientColors[index + 1], factor);
            };

            const col = function (x: number, y: number, r: number, g: number, b: number) {
                $.fillStyle = `rgb(${r},${g},${b})`;
                $.fillRect(x, y, pixelSize, pixelSize);
            };

            let t = 0;
            let lastDrawTime = 0;

            const run = function (timestamp: number) {
                // Limit animation to 30fps
                if (timestamp - lastDrawTime < 33) {
                    animationFrameRef.current = requestAnimationFrame(run);
                    return;
                }

                lastDrawTime = timestamp;

                // Only run animation if canvas is in viewport
                const rect = c.getBoundingClientRect();
                if (rect.bottom < 0 || rect.top > window.innerHeight) {
                    animationFrameRef.current = requestAnimationFrame(run);
                    return;
                }

                for (let x = 0; x <= width; x++) {
                    for (let y = 0; y <= height; y++) {
                        const distFromCenter = Math.sqrt(Math.pow(x - width / 2, 2) + Math.pow(y - height, 2));
                        const val = (Math.sin(distFromCenter / 3 + t) + 1) / 2;
                        const color = getGradientColor(val);
                        col(x, y, color.r, color.g, color.b);
                    }
                }
                t = t + 0.04;
                animationFrameRef.current = requestAnimationFrame(run);
            };

            animationFrameRef.current = requestAnimationFrame(run);

            return () => {
                if (animationFrameRef.current) {
                    cancelAnimationFrame(animationFrameRef.current);
                }
            };
        }
    }, []);

    return (
        <>
            <canvas
                id="canv"
                width="32"
                height="32"
                className="w-full fixed bottom-0 left-0 opacity-25 transition-all duration-300 ease-in-out pointer-events-none"
                style={{
                    clipPath: 'ellipse(80% 60% at 50% 100%)',
                    height: `${canvasHeight}vh`,
                }}
                ref={bgCanvas}
            ></canvas>
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
                                        'hover:[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent animate-border',
                                        'hover:scale-110',
                                        'transition-all duration-200',
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
                            <a href="mailto:sales@beacon-hq.dev" className="text-blue-600 underline">
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
                    <div className="w-3xl mx-auto px-6">
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
                <section className="bg-teal-100 dark:bg-teal-800 w-full py-20 text-center text-primary">
                    <h2 className="text-4xl font-bold mb-4">Get three months free, then just $5/month.</h2>
                    <p className="text-lg mb-8">
                        Unlimited flags, apps, environments, and users — for the price of a coffee.
                    </p>
                    <a href="/register">
                        <Button size="lg">Start Free Trial</Button>
                    </a>
                </section>
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
