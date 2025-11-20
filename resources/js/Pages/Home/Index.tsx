import { ProductCollection } from '@/Application';
import HomeLayout from '@/Layouts/HomeLayout';
import { CentralizedControlAnimation } from '@/Pages/Home/Components/CentralizedControlAnimation';
import Pricing from '@/Pages/Home/Components/Pricing';
import { ThreeDMarquee } from '@/components/ui/3d-marquee';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { AuroraText } from '@/components/ui/aurora-text';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { cn } from '@/lib/utils';
import { Head, Link, usePage } from '@inertiajs/react';
import { AppWindowMac } from 'lucide-react';
import { useReducedMotion } from 'motion/react';
import React, { useEffect, useRef, useState } from 'react';

const uniqueImages = [
    '/images/hero/hero-applications-light.png',
    '/images/hero/hero-applications-dark.png',
    '/images/hero/hero-feature-flags-light.png',
    '/images/hero/hero-feature-flags-dark.png',
    '/images/hero/hero-dashboard-light.png',
    '/images/hero/hero-dashboard-dark.png',
];

const images = [...uniqueImages, ...uniqueImages, ...uniqueImages, ...uniqueImages, ...uniqueImages, ...uniqueImages];

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
        answer: (
            <>
                Yes, absolutely, Beacon is{' '}
                <Button type="button" className="p-0" variant="link">
                    <a href="https://github.com/beacon-hq/app">open source.</a>
                </Button>
            </>
        ),
    },
];

export default function Index() {
    useScrollToLocation();
    const pricingEnabled = usePage().props.features['pricing.enabled'];
    const { products, docsUrl } = usePage().props;

    const [isDark, setIsDark] = useState(false);

    const pricingRef = useRef(null);

    const [deploySwitchState, setDeploySwitchState] = useState(false);

    const motionReduced = useReducedMotion();

    const deploySwitchTimeout = useRef<any>(null);

    const toggleDeploySwitch = () => {
        clearTimeout(deploySwitchTimeout.current);
        setDeploySwitchState(!deploySwitchState);
    };

    const toggleAppearance = (event: any) => {
        const newIsDark = !isDark;
        setIsDark(newIsDark);
        localStorage.theme = newIsDark ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', newIsDark);
    };

    useEffect(() => {
        const isDarkMode =
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        setIsDark(isDarkMode);
        document.documentElement.classList.toggle('dark', isDarkMode);
    }, []);

    useEffect(() => {
        deploySwitchTimeout.current = setTimeout(
            () => {
                setDeploySwitchState(!deploySwitchState);
            },
            motionReduced ? 10000 : 3000,
        );
    }, [deploySwitchState]);

    return (
        <HomeLayout>
            <Head title="Welcome" />

            <header className="flex flex-col items-center mb-8">
                <div className="flex flex-col md:flex-row w-3/4 mx-auto items-center pt-20">
                    <div className="flex flex-col items-center gap-4">
                        <h1 className="text-4xl text-center w-full font-normal text-primary bg-clip-text inline-block px-4">
                            Supercharge your deployments with Beacon.
                            <AuroraText colors={['#00e281', '#00e7a4', '#00f1ed', '#00b9fa', '#009aff']} speed={4}>
                                Feature flag management for Laravel.
                            </AuroraText>
                        </h1>
                        <h2 className="text-xl text-primary text-center">
                            No deploys, no waiting, just real time control.
                        </h2>
                    </div>
                    <div className="mx-auto w-60 block md:w-1/2">
                        <svg
                            viewBox="-120 0 751 754"
                            xmlns="http://www.w3.org/2000/svg"
                            xmlSpace="preserve"
                            style={{
                                fillRule: 'evenodd',
                                clipRule: 'evenodd',
                                strokeLinecap: 'round',
                                strokeLinejoin: 'round',
                                strokeMiterlimit: '1.5',
                            }}
                            className="mx-auto"
                        >
                            <defs>
                                <linearGradient id="gradient-fill" y2="1">
                                    <stop offset="0" stopColor="#00e281" />
                                    <stop offset="0.16666666666666666" stopColor="#00e7a4" />
                                    <stop offset="0.3333333333333333" stopColor="#00ecc7" />
                                    <stop offset="0.5" stopColor="#00f1ed" />
                                    <stop offset="0.6666666666666666" stopColor="#00d8f5" />
                                    <stop offset="0.8333333333333334" stopColor="#00b9fa" />
                                    <stop offset="1" stopColor="#009aff" />
                                </linearGradient>
                                <linearGradient id="gradient-background-light">
                                    <stop offset="0" stopColor="#dadada" />
                                    <stop offset="1" stopColor="#c0c0c0" />
                                </linearGradient>
                                <linearGradient id="gradient-background-dark">
                                    <stop offset="0" stopColor="#a8a8a8" />
                                    <stop offset="1" stopColor="#636363" />
                                </linearGradient>
                            </defs>
                            <g>
                                <path
                                    d="M508.527,180.555l0,380.579c0,35.608 -28.909,64.517 -64.517,64.517l-379.492,0c-35.609,0 -64.518,-28.909 -64.518,-64.517l0,-380.579c0,-35.608 28.909,-64.518 64.518,-64.518l379.492,0c35.608,0 64.517,28.91 64.517,64.518Z"
                                    style={{ fill: '#ebebeb' }}
                                />
                                <path
                                    d="M508.527,180.555l0,380.579c0,35.608 -28.909,64.517 -64.517,64.517l-379.492,0c-35.609,0 -64.518,-28.909 -64.518,-64.517l0,-380.579c0,-35.608 28.909,-64.518 64.518,-64.518l379.492,0c35.608,0 64.517,28.91 64.517,64.518Z"
                                    className="dark:hidden"
                                    style={{ fill: 'url(#gradient-background-light)' }}
                                />
                                <path
                                    d="M508.527,180.555l0,380.579c0,35.608 -28.909,64.517 -64.517,64.517l-379.492,0c-35.609,0 -64.518,-28.909 -64.518,-64.517l0,-380.579c0,-35.608 28.909,-64.518 64.518,-64.518l379.492,0c35.608,0 64.517,28.91 64.517,64.518Z"
                                    className="hidden dark:block"
                                    style={{ fill: 'url(#gradient-background-dark)' }}
                                />
                                <path
                                    d="M69.431,143.628c-0.105,-21.726 11.411,-41.852 30.195,-52.77c18.784,-10.918 41.971,-10.963 60.797,-0.118c79.644,45.879 190.564,109.775 244.69,140.955c19.995,11.518 32.362,32.795 32.474,55.87c0.327,67.355 1.027,211.588 1.509,311.087c0.105,21.509 -11.296,41.434 -29.892,52.243c-18.597,10.809 -41.552,10.853 -60.19,0.117c-73.798,-42.512 -174.905,-100.755 -232.613,-133.998c-28.039,-16.152 -45.381,-45.988 -45.538,-78.346c-0.348,-71.771 -0.982,-202.463 -1.432,-295.04Z"
                                    style={{ stroke: '#ebebeb', strokeWidth: '1px' }}
                                />
                                <path
                                    d="M69.431,143.628c-0.105,-21.726 11.411,-41.852 30.195,-52.77c18.784,-10.918 41.971,-10.963 60.797,-0.118c79.644,45.879 190.564,109.775 244.69,140.955c19.995,11.518 32.362,32.795 32.474,55.87c0.327,67.355 1.027,211.588 1.509,311.087c0.105,21.509 -11.296,41.434 -29.892,52.243c-18.597,10.809 -41.552,10.853 -60.19,0.117c-73.798,-42.512 -174.905,-100.755 -232.613,-133.998c-28.039,-16.152 -45.381,-45.988 -45.538,-78.346c-0.348,-71.771 -0.982,-202.463 -1.432,-295.04Z"
                                    style={{
                                        stroke: '#ebebeb',
                                        strokeWidth: '1px',
                                        fill: 'url(#gradient-fill)',
                                        filter: 'drop-shadow(5px 5px 17.5px #000000)',
                                    }}
                                />
                                <path
                                    d="M360.429,297.229l-0,147.231c-0,58.594 -47.571,106.165 -106.165,106.165c-58.595,0 -106.166,-47.571 -106.166,-106.165l0,-147.231c0,-58.595 47.571,-106.166 106.166,-106.166c58.594,0 106.165,47.571 106.165,106.166Z"
                                    style={{ fill: '#d2d2d2' }}
                                />
                                <path
                                    d="M360.429,297.229l-0,147.231c-0,58.594 -47.571,106.165 -106.165,106.165c-58.595,0 -106.166,-47.571 -106.166,-106.165l0,-147.231c0,-58.595 47.571,-106.166 106.166,-106.166c58.594,0 106.165,47.571 106.165,106.166Z"
                                    style={{ fill: '#e6e6e6', filter: 'drop-shadow(5px 5px 27.5px #a6a6a6)' }}
                                    className="cursor-pointer"
                                    onClick={toggleAppearance}
                                />
                            </g>
                            <circle
                                cx="254.122"
                                cy="296.504"
                                r="93.081"
                                className="fill-[#646464] stroke-[#ebebeb] stroke-width-[1px] dark:fill-white dark:translate-y-36 cursor-pointer"
                                onClick={toggleAppearance}
                            />
                        </svg>
                    </div>
                </div>
                <div className="flex flex-row gap-2">
                    <Link href={route('register')}>
                        <Button type="button" className="relative" size="xl">
                            Get Started
                        </Button>
                    </Link>
                </div>
            </header>
            <div className="overflow-hidden w-full">
                <ThreeDMarquee images={images} />
            </div>
            <section className="dark:bg-neutral-900 w-full">
                <div className="mx-auto flex flex-col gap-12">
                    <div className="items-center md:max-w-4xl max-w-full mx-auto flex md:flex-row flex-col">
                        <div className="prose dark:prose-invert px-4 py-12">
                            <h2 className="">Centralized Control</h2>
                            <p className="text-gray-600 dark:text-gray-300">
                                Manage all your feature flags from a single dashboard. Control rollouts across multiple
                                applications and environments with ease.
                            </p>
                        </div>
                        <div className="w-full">
                            <CentralizedControlAnimation />
                        </div>
                    </div>
                    <div className="w-full mx-auto group bg-secondary py-12">
                        <div className="max-w-3xl mx-auto gap-12 flex md:flex-row flex-col items-center justify-between">
                            <div>
                                <svg
                                    width="60%"
                                    height="100%"
                                    viewBox="0 0 64 64"
                                    version="1.1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    xmlSpace="preserve"
                                    style={{
                                        fillRule: 'evenodd',
                                        clipRule: 'evenodd',
                                        strokeLinejoin: 'round',
                                        strokeMiterlimit: 2,
                                    }}
                                    className="mx-auto motion-safe:grayscale-100 group-hover:grayscale-0 motion-reduce:grayscale-0"
                                >
                                    <rect x="0" y="0" width="64" height="64" style={{ fill: '#328c6b' }} />
                                    <path
                                        id="flag"
                                        d="M32,30.563L32,26.625L12,21L12,43.5L32,37.875L32,41.813L52,36.188L32,30.563Z"
                                        style={{ fill: '#fdfdfc' }}
                                        className="group-hover:motion-safe:animate-wiggle origin-center"
                                    />
                                </svg>
                            </div>
                            <div className="prose dark:prose-invert px-4">
                                <h2 className="text-2xl font-bold mb-4 mt-4">Built for Laravel Pennant</h2>
                                <p>
                                    Seamlessly integrate Beacon with{' '}
                                    <a href="http://laravel.com/docs/pennant">Laravel Pennant</a> to manage feature
                                    flags across all environments with ease. Gain centralized control, simplify
                                    rollouts, and improve your team&apos;s productivity.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="items-center justify-between max-w-3xl mx-auto flex md:flex-row flex-col gap-0 md:gap-8">
                        <div className="prose dark:prose-invert px-4 py-12">
                            <h2 className="text-2xl font-bold mb-4 mt-4">Zero-Downtime Configuration Changes</h2>
                            <p>
                                Change Feature Flag configurations without deploying code. Beacon allows you to manage
                                your feature flags in real-time, ensuring a smooth experience for your users.
                            </p>
                        </div>
                        <div className="flex flex-row gap-8 items-center">
                            <Switch
                                className="scale-300 rotate-90"
                                checked={deploySwitchState}
                                onClick={toggleDeploySwitch}
                            />
                            <AppWindowMac
                                className={cn('h-48 w-48', {
                                    'stroke-gray-300': !deploySwitchState,
                                    'stroke-[#06df73]': deploySwitchState,
                                })}
                            />
                        </div>
                    </div>
                    <div className="w-full bg-secondary py-12">
                        <div className="items-center mx-auto  justify-between max-w-3xl flex md:flex-row flex-col gap-20 ">
                            <div>
                                <svg
                                    width="100%"
                                    height="100%"
                                    viewBox="0 0 1054 1054"
                                    version="1.1"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <g transform="matrix(1,0,0,1,-13.0779,-13.0779)">
                                        <g transform="matrix(1.45509,0,0,1.45509,-245.747,-245.747)">
                                            <circle
                                                cx="540"
                                                cy="540"
                                                r="357.657"
                                                style={{ strokeWidth: '8.93px' }}
                                                className="fill-secondary motion-preset-blink motion-safe:motion-duration-[5s] motion-reduce:motion-duration-[10s] motion-delay-700 stroke-primary"
                                            />
                                        </g>
                                        <g transform="matrix(1.45509,0,0,1.45509,-245.747,-245.747)">
                                            <circle
                                                cx="540"
                                                cy="540"
                                                r="281.534"
                                                style={{ strokeWidth: '8.93px' }}
                                                className="fill-secondary motion-preset-blink motion-safe:motion-duration-[5s] motion-reduce:motion-duration-[10s] motion-delay-500 stroke-primary"
                                            />
                                        </g>
                                        <g transform="matrix(2.73406,0,0,2.73406,-936.391,-936.391)">
                                            <circle
                                                cx="540"
                                                cy="540"
                                                r="105.928"
                                                style={{ strokeWidth: '8.93px' }}
                                                className="fill-secondary motion-preset-blink motion-safe:motion-duration-[5s] motion-reduce:motion-duration-[10s] motion-delay-300 stroke-primary"
                                            />
                                        </g>
                                        <g transform="matrix(2.49718,0,0,2.49718,-808.475,-808.475)">
                                            <circle
                                                cx="540"
                                                cy="540"
                                                r="64.975"
                                                style={{ strokeWidth: '8.93px' }}
                                                className="fill-secondary motion-preset-blink motion-safe:motion-duration-[5s] motion-reduce:motion-duration-[10s] motion-delay-100 stroke-primary"
                                            />
                                        </g>
                                        <g transform="matrix(1.45509,0,0,1.45509,-245.747,-245.747)">
                                            <circle
                                                cx="540"
                                                cy="540"
                                                r="14.592"
                                                style={{ strokeWidth: '8.93px' }}
                                                className="fill-secondary motion-preset-blink motion-safe:motion-duration-[5s] motion-reduce:motion-duration-[10s] stroke-primary"
                                            />
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div className="prose dark:prose-invert px-4">
                                <h2 className="text-2xl font-bold mt-4">Gradual Rollouts</h2>
                                <p>
                                    Gradually roll out new features to a percentage of your users. Monitor performance
                                    and user feedback before a full rollout, ensuring a smooth transition and minimizing
                                    risk.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="items-center justify-between w-full md:max-w-3xl mx-auto flex md:flex-row flex-col gap-20 py-12">
                        <div className="prose dark:prose-invert px-4">
                            <h2 className="text-2xl font-bold mb-4">A/B Testing</h2>
                            <p>
                                Use Feature Flags to run experiments with advanced audience segmentation and multiple
                                variants. Measure user engagement and performance to make data-driven decisions on which
                                features to keep or discard.
                            </p>
                        </div>
                        <div className="rotate-12 max-h-[250px] md:max-h-[218px] flex flex-row justify-center overflow-hidden">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                xmlSpace="preserve"
                                fillRule="evenodd"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                clipRule="evenodd"
                                viewBox="0 0 18 21"
                                className="w-[250px] max-h-[218px] overflow-hidden"
                                id="experiments"
                            >
                                <g transform="translate(-3.5 -1.5)">
                                    <path
                                        id="Liquid"
                                        fill="#06df73"
                                        fillRule="nonzero"
                                        d="M17.584 15.068C17.745 15.362 20 19.664 20 20c0 1.097-.902 2-2 2H6c-1.098 0-2-.903-2-2 0-.336 2.292-4.705 2.453-4.999m0-.001h11.094"
                                        className="motion-safe:motion-preset-blink motion-duration-[10s]"
                                    ></path>
                                    <path
                                        id="Liquid"
                                        fill="#fb64b6"
                                        fillRule="nonzero"
                                        d="M17.584 15.068C17.745 15.362 20 19.664 20 20c0 1.097-.902 2-2 2H6c-1.098 0-2-.903-2-2 0-.336 2.292-4.705 2.453-4.999m0-.001h11.094"
                                        className="motion-safe:motion-preset-blink motion-duration-[10s] motion-delay-[5s]"
                                    ></path>
                                    <path
                                        id="Flask"
                                        fill="none"
                                        fillRule="nonzero"
                                        className="stroke-[0.5] stroke-primary"
                                        d="M14 2v6a2 2 0 0 0 .245.96l5.51 10.08c.161.294.245.624.245.96 0 1.097-.902 2-2 2H6c-1.098 0-2-.903-2-2 0-.336.084-.666.245-.96l5.51-10.08A2 2 0 0 0 10 8V2M6.453 15h11.094M8.5 2h7"
                                    ></path>
                                    <g id="Bubbles" className="opacity-75">
                                        <g
                                            id="bubble-1"
                                            className="motion-safe:motion-preset-float-sm motion-duration-3000 motion-delay-100"
                                        >
                                            <path
                                                fill="none"
                                                fillRule="nonzero"
                                                strokeWidth="0.33934"
                                                className="stroke-primary"
                                                d="M7.335 19.45c.186 0 .34.153.34.34"
                                            ></path>
                                            <circle
                                                cx="7.5"
                                                cy="16.5"
                                                r="5.5"
                                                fill="none"
                                                className="stroke-primary"
                                                strokeWidth="2"
                                                transform="matrix(.16967 0 0 .16967 6.114 16.939)"
                                            ></circle>
                                        </g>
                                        <circle
                                            cx="18.5"
                                            cy="8.5"
                                            r="3.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 6.114 16.939)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-2500 motion-delay-300"
                                        ></circle>
                                        <circle
                                            cx="7.5"
                                            cy="4.5"
                                            r="2.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 6.153 16.6)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-4000 motion-delay-500"
                                        ></circle>
                                        <circle
                                            cx="18.5"
                                            cy="8.5"
                                            r="3.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 8.522 15.327)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-3500 motion-delay-200"
                                        ></circle>
                                        <circle
                                            cx="7.5"
                                            cy="16.5"
                                            r="5.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 10.727 16.43)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-2800 motion-delay-700"
                                        ></circle>
                                        <circle
                                            cx="7.5"
                                            cy="4.5"
                                            r="2.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 12.713 16.939)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-3200 motion-delay-400"
                                        ></circle>
                                        <circle
                                            cx="18.5"
                                            cy="8.5"
                                            r="3.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 12.154 18.38)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-2700 motion-delay-600"
                                        ></circle>
                                        <circle
                                            cx="7.5"
                                            cy="16.5"
                                            r="5.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 14.954 14.139)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-4200 motion-delay-150"
                                        ></circle>
                                        <circle
                                            cx="7.5"
                                            cy="4.5"
                                            r="2.5"
                                            fill="none"
                                            strokeWidth="2"
                                            transform="matrix(.16967 0 0 .16967 15.887 18.466)"
                                            className="stroke-primary motion-safe:motion-preset-float-sm motion-duration-3800 motion-delay-800"
                                        ></circle>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            {pricingEnabled && (
                <>
                    <section id="pricing" className="scroll-mt-8" ref={pricingRef}>
                        <Pricing products={products as ProductCollection} />
                    </section>
                    <section className="pt-20 pb-10 md:px-10 dark:bg-background w-full" id="faq">
                        <div className="max-w-full mx-auto px-6">
                            <h2 className="text-3xl font-bold text-primary text-center mb-12">
                                Frequently Asked Questions
                            </h2>
                            <Accordion type="single" collapsible className="max-w-full">
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
                </>
            )}
        </HomeLayout>
    );
}
