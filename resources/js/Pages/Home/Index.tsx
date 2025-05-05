import NavMenu from '@/Components/NavMenu';
import { CentralizedControlAnimation } from '@/Pages/Home/Components/CentralizedControlAnimation';
import Pricing from '@/Pages/Pricing';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { PageProps } from '@/types';
import { SiGithub, SiYoutube } from '@icons-pack/react-simple-icons';
import { Head } from '@inertiajs/react';
import { Radio } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

export default function Index({ auth }: PageProps) {
    useScrollToLocation();

    const [isDark, setIsDark] = useState(false);

    const pricingRef = useRef(null);

    useEffect(() => {
        // Initialize dark mode based on localStorage or system preference
        const isDarkMode =
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        setIsDark(isDarkMode);
        document.documentElement.classList.toggle('dark', isDarkMode);
    }, []);

    const toggleAppearance = () => {
        const newIsDark = !isDark;
        setIsDark(newIsDark);
        localStorage.theme = newIsDark ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', newIsDark);
    };

    return (
        <>
            <Head title="Welcome" />
            <NavMenu />

            <header className="flex flex-col items-center">
                <div className="mx-auto px-4 py-8 text-center w-11/12 md:w-3/4">
                    <svg
                        viewBox="0 0 2173 742"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlSpace="preserve"
                        style={{
                            fillRule: 'evenodd',
                            clipRule: 'evenodd',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            strokeMiterlimit: '1.5',
                        }}
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
                        <g id="beacon" className="translate-x-16 fill-black dark:fill-white">
                            <g transform="matrix(454.932,0,0,454.932,552.092,534.404)">
                                <path
                                    d="M0.384,0.008c-0.028,0 -0.052,-0.005 -0.072,-0.014c-0.02,-0.009 -0.037,-0.021 -0.051,-0.036c-0.013,-0.014 -0.023,-0.03 -0.03,-0.047l-0.007,0l0,0.089l-0.168,-0l0,-0.728l0.171,0l-0,0.276l0.004,-0c0.006,-0.017 0.016,-0.033 0.029,-0.048c0.014,-0.015 0.03,-0.028 0.051,-0.038c0.02,-0.01 0.045,-0.015 0.074,-0.015c0.038,0 0.074,0.01 0.107,0.03c0.033,0.02 0.06,0.051 0.08,0.093c0.02,0.041 0.03,0.094 0.03,0.158c0,0.061 -0.009,0.113 -0.029,0.155c-0.019,0.041 -0.046,0.072 -0.079,0.093c-0.033,0.022 -0.07,0.032 -0.11,0.032Zm-0.058,-0.133c0.022,-0 0.04,-0.006 0.055,-0.018c0.015,-0.013 0.027,-0.03 0.034,-0.052c0.008,-0.022 0.012,-0.048 0.012,-0.077c0,-0.03 -0.004,-0.056 -0.011,-0.078c-0.008,-0.022 -0.02,-0.039 -0.035,-0.051c-0.015,-0.012 -0.033,-0.019 -0.055,-0.019c-0.022,0 -0.041,0.006 -0.056,0.018c-0.015,0.012 -0.027,0.029 -0.035,0.051c-0.008,0.022 -0.012,0.048 -0.012,0.079c-0,0.029 0.004,0.055 0.012,0.077c0.008,0.022 0.02,0.039 0.035,0.052c0.016,0.012 0.034,0.018 0.056,0.018Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(454.932,0,0,454.932,815.925,534.404)">
                                <path
                                    d="M0.31,0.01c-0.057,0 -0.106,-0.011 -0.147,-0.034c-0.041,-0.022 -0.073,-0.054 -0.095,-0.096c-0.022,-0.042 -0.033,-0.093 -0.033,-0.151c0,-0.056 0.011,-0.105 0.033,-0.148c0.022,-0.042 0.053,-0.075 0.094,-0.098c0.04,-0.024 0.088,-0.036 0.142,-0.036c0.039,0 0.074,0.006 0.106,0.018c0.032,0.012 0.06,0.03 0.083,0.054c0.024,0.023 0.042,0.052 0.055,0.087c0.013,0.035 0.02,0.075 0.02,0.12l-0,0.043l-0.472,-0l-0,-0.1l0.39,0l-0.079,0.024c-0,-0.026 -0.004,-0.047 -0.012,-0.066c-0.007,-0.018 -0.019,-0.032 -0.034,-0.042c-0.014,-0.009 -0.033,-0.014 -0.055,-0.014c-0.023,-0 -0.041,0.005 -0.057,0.015c-0.015,0.009 -0.026,0.023 -0.034,0.041c-0.008,0.018 -0.012,0.039 -0.012,0.062l-0,0.072c-0,0.028 0.004,0.051 0.013,0.07c0.01,0.019 0.023,0.033 0.039,0.042c0.017,0.009 0.036,0.014 0.058,0.014c0.016,-0 0.03,-0.002 0.042,-0.007c0.013,-0.004 0.023,-0.01 0.032,-0.019c0.009,-0.008 0.016,-0.018 0.021,-0.03l0.152,0.025c-0.009,0.031 -0.025,0.058 -0.048,0.081c-0.022,0.024 -0.05,0.041 -0.084,0.054c-0.034,0.013 -0.073,0.019 -0.118,0.019Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(454.932,0,0,454.932,1067.75,534.404)">
                                <path
                                    d="M0.209,0.01c-0.035,-0 -0.066,-0.006 -0.093,-0.018c-0.027,-0.012 -0.048,-0.03 -0.064,-0.054c-0.015,-0.024 -0.023,-0.054 -0.023,-0.09c0,-0.03 0.005,-0.055 0.016,-0.076c0.011,-0.021 0.025,-0.038 0.044,-0.051c0.019,-0.013 0.041,-0.023 0.065,-0.029c0.025,-0.007 0.051,-0.012 0.078,-0.014c0.031,-0.003 0.056,-0.006 0.075,-0.009c0.019,-0.003 0.033,-0.007 0.042,-0.014c0.008,-0.006 0.013,-0.014 0.013,-0.026l-0,-0.002c-0,-0.012 -0.003,-0.023 -0.009,-0.032c-0.006,-0.009 -0.014,-0.015 -0.025,-0.02c-0.011,-0.004 -0.025,-0.007 -0.041,-0.007c-0.015,0 -0.029,0.003 -0.041,0.007c-0.012,0.005 -0.022,0.011 -0.03,0.02c-0.008,0.008 -0.014,0.018 -0.017,0.03l-0.154,-0.021c0.007,-0.031 0.021,-0.058 0.042,-0.082c0.021,-0.023 0.049,-0.042 0.083,-0.055c0.034,-0.013 0.074,-0.02 0.119,-0.02c0.034,0 0.066,0.004 0.095,0.012c0.029,0.008 0.055,0.02 0.077,0.035c0.023,0.016 0.04,0.035 0.052,0.058c0.013,0.022 0.019,0.048 0.019,0.077l-0,0.371l-0.16,-0l-0,-0.076l-0.005,-0c-0.01,0.018 -0.022,0.034 -0.037,0.047c-0.015,0.012 -0.033,0.022 -0.053,0.029c-0.02,0.006 -0.043,0.01 -0.068,0.01Zm0.052,-0.113c0.019,-0 0.036,-0.004 0.052,-0.012c0.015,-0.008 0.027,-0.018 0.036,-0.032c0.01,-0.014 0.014,-0.029 0.014,-0.047l0,-0.053c-0.005,0.003 -0.011,0.005 -0.018,0.008c-0.008,0.002 -0.016,0.004 -0.024,0.006c-0.009,0.002 -0.018,0.003 -0.027,0.005c-0.009,0.001 -0.017,0.003 -0.025,0.004c-0.017,0.002 -0.031,0.006 -0.042,0.012c-0.012,0.005 -0.021,0.012 -0.027,0.021c-0.006,0.009 -0.009,0.019 -0.009,0.032c-0,0.012 0.003,0.022 0.009,0.03c0.006,0.009 0.014,0.015 0.024,0.019c0.011,0.005 0.023,0.007 0.037,0.007Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(454.932,0,0,454.932,1309.16,534.404)">
                                <path
                                    d="M0.309,0.01c-0.057,0 -0.106,-0.012 -0.146,-0.035c-0.041,-0.024 -0.073,-0.057 -0.095,-0.099c-0.022,-0.042 -0.033,-0.091 -0.033,-0.147c0,-0.056 0.011,-0.105 0.033,-0.148c0.022,-0.042 0.054,-0.075 0.095,-0.098c0.04,-0.024 0.089,-0.036 0.146,-0.036c0.035,0 0.067,0.005 0.096,0.014c0.029,0.009 0.054,0.022 0.076,0.039c0.022,0.016 0.04,0.037 0.053,0.061c0.014,0.024 0.023,0.05 0.027,0.08l-0.157,0.026c-0.003,-0.014 -0.007,-0.027 -0.012,-0.038c-0.005,-0.011 -0.012,-0.02 -0.02,-0.028c-0.007,-0.008 -0.016,-0.014 -0.027,-0.018c-0.01,-0.004 -0.021,-0.006 -0.033,-0.006c-0.022,0 -0.041,0.006 -0.056,0.018c-0.015,0.012 -0.027,0.03 -0.035,0.052c-0.008,0.023 -0.012,0.05 -0.012,0.081c-0,0.031 0.004,0.058 0.012,0.081c0.008,0.023 0.02,0.04 0.035,0.053c0.015,0.012 0.034,0.018 0.056,0.018c0.012,0 0.023,-0.002 0.034,-0.006c0.01,-0.004 0.019,-0.01 0.027,-0.018c0.008,-0.008 0.014,-0.018 0.02,-0.03c0.005,-0.011 0.009,-0.024 0.011,-0.039l0.157,0.026c-0.004,0.03 -0.013,0.058 -0.026,0.082c-0.014,0.024 -0.031,0.045 -0.053,0.062c-0.022,0.017 -0.047,0.03 -0.076,0.04c-0.029,0.009 -0.062,0.013 -0.097,0.013Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(454.932,0,0,454.932,1553.83,534.404)">
                                <path
                                    d="M0.31,0.01c-0.057,0 -0.106,-0.012 -0.147,-0.035c-0.041,-0.024 -0.073,-0.057 -0.095,-0.099c-0.022,-0.042 -0.033,-0.091 -0.033,-0.147c0,-0.056 0.011,-0.105 0.033,-0.148c0.022,-0.042 0.054,-0.075 0.095,-0.098c0.041,-0.024 0.09,-0.036 0.147,-0.036c0.057,0 0.106,0.012 0.147,0.036c0.04,0.023 0.072,0.056 0.094,0.098c0.022,0.043 0.033,0.092 0.033,0.148c-0,0.056 -0.011,0.105 -0.033,0.147c-0.022,0.042 -0.054,0.075 -0.094,0.099c-0.041,0.023 -0.09,0.035 -0.147,0.035Zm-0,-0.13c0.021,0 0.04,-0.006 0.055,-0.019c0.015,-0.013 0.026,-0.03 0.034,-0.053c0.007,-0.023 0.011,-0.05 0.011,-0.08c0,-0.03 -0.004,-0.057 -0.011,-0.079c-0.008,-0.023 -0.019,-0.04 -0.034,-0.053c-0.015,-0.012 -0.034,-0.019 -0.055,-0.019c-0.022,0 -0.041,0.007 -0.056,0.019c-0.015,0.013 -0.026,0.03 -0.034,0.053c-0.007,0.022 -0.011,0.049 -0.011,0.079c-0,0.03 0.004,0.057 0.011,0.08c0.008,0.023 0.019,0.04 0.034,0.053c0.015,0.013 0.034,0.019 0.056,0.019Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(454.932,0,0,454.932,1809.18,534.404)">
                                <path
                                    d="M0.227,-0.312l-0,0.312l-0.171,-0l0,-0.546l0.161,0l0.003,0.138l-0.008,-0c0.014,-0.044 0.035,-0.079 0.063,-0.105c0.029,-0.026 0.067,-0.04 0.115,-0.04c0.038,0 0.072,0.009 0.1,0.026c0.028,0.017 0.05,0.041 0.066,0.071c0.015,0.031 0.023,0.067 0.023,0.109l-0,0.347l-0.17,-0l-0,-0.314c-0,-0.031 -0.008,-0.055 -0.024,-0.073c-0.015,-0.017 -0.037,-0.026 -0.065,-0.026c-0.019,0 -0.035,0.004 -0.049,0.012c-0.014,0.008 -0.025,0.02 -0.033,0.035c-0.007,0.015 -0.011,0.033 -0.011,0.054Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                        </g>
                    </svg>
                </div>

                <h1 className="text-4xl text-center w-full font-bold mb-8 bg-linear-to-br text-transparent bg-clip-text inline-block from-green-400 via-blue-500 to-blue-600 dark:from-green-800 dark:via-blue-800 dark:to-blue-900">
                    Feature Flag Management For Laravel
                </h1>
            </header>

            <section className="dark:bg-neutral-900 w-full">
                <div className="mx-auto px-4 py-12 flex flex-col gap-12">
                    <div className="items-center md:max-w-4xl max-w-full mx-auto flex md:flex-row flex-col">
                        <div className="prose dark:prose-invert">
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
                    <div className="items-center justify-between max-w-3xl mx-auto flex md:flex-row flex-col gap-12 group">
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
                                className="mx-auto"
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
                        <div className="prose dark:prose-invert">
                            <h2 className="text-2xl font-bold mb-4 mt-4">Built for Laravel Pennant</h2>
                            <p className="mb-6">
                                Seamlessly integrate Beacon with{' '}
                                <a href="http://laravel.com/docs/pennant">Laravel Pennant</a> to manage feature flags
                                across all environments with ease. Gain centralized control, simplify rollouts, and
                                improve your team&apos;s productivity.
                            </p>
                        </div>
                    </div>
                    <div className="items-center justify-between max-w-3xl mx-auto flex md:flex-row flex-col gap-20">
                        <div className="prose dark:prose-invert">
                            <h2 className="text-2xl font-bold mb-4 mt-4">Zero-Downtime Rollouts</h2>
                            <p className="mb-6">
                                Change Feature Flag configurations without deploying code. Beacon allows you to manage
                                your feature flags in real-time, ensuring a smooth experience for your users.
                            </p>
                        </div>
                        <div>
                            <Radio
                                size={120}
                                className="motion-safe:animate-zoomInOut text-primary motion-safe:animate-infinite motion-safe:[animation-duration:_3s]"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section id="pricing" ref={pricingRef}>
                <Pricing />
            </section>

            <footer className="bg-gray-800 text-white py-6">
                <div className="container mx-auto px-4 text-center">
                    <p className="mb-4">
                        Beacon is made possible by the Laravel community and open-source contributors like you.
                    </p>
                    <div className="flex justify-center space-x-4">
                        <a href="https://github.com/beacon-hq" className="hover:underline">
                            <div className="flex flex-row items-center">
                                <SiGithub className="h-4 w-4 inline-block mr-1" /> Github
                            </div>
                        </a>
                        <a href="https://youtube.com/@beacon-hq" className="hover:underline">
                            <div className="flex flex-row items-center">
                                <SiYoutube className="h-4 w-4 inline-block mr-1" /> YouTube
                            </div>
                        </a>
                    </div>
                    <p className="text-sm mt-4">Made with 🦁💖🏳️‍🌈 by Davey Shafik.</p>
                    <p className="text-sm">
                        Released under the{' '}
                        <a href="https://github.com/beacon-hq/app/blob/main/LICENSE.md">FCL-1.0-MIT License</a>.
                        Copyright © 2024-{new Date().getFullYear()} Davey Shafik.
                    </p>
                </div>
            </footer>
        </>
    );
}
