import { ProductCollection } from '@/Application';
import Footer from '@/Components/Footer';
import NavMenu from '@/Components/NavMenu';
import { AuroraText } from '@/Components/magicui/aurora-text';
import { Button } from '@/Components/ui/button';
import { Switch } from '@/Components/ui/switch';
import { CentralizedControlAnimation } from '@/Pages/Home/Components/CentralizedControlAnimation';
import Pricing from '@/Pages/Home/Components/Pricing';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { SiGithub, SiYoutube } from '@icons-pack/react-simple-icons';
import { Head, Link, usePage } from '@inertiajs/react';
import { AppWindowMac } from 'lucide-react';
import { useReducedMotion } from 'motion/react';
import React, { useEffect, useRef, useState } from 'react';

export default function Index() {
    useScrollToLocation();
    const pricingEnabled = usePage().props.features['pricing.enabled'];
    const { products, docsUrl } = usePage().props;

    const [isDark, setIsDark] = useState(false);

    const pricingRef = useRef(null);

    const [deploySwitchState, setDeploySwitchState] = useState(false);

    const motionReduced = useReducedMotion();

    const deploySwitchTimeout = useRef<any>(null);

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

    const toggleDeploySwitch = () => {
        clearTimeout(deploySwitchTimeout.current);
        setDeploySwitchState(!deploySwitchState);
    };

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
                <div className="mx-auto px-4 py-8 text-center w-full md:w-3/4">
                    <svg
                        viewBox="0 0 1955 754"
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
                        <g id="beacon" className="fill-black dark:fill-white">
                            <g transform="matrix(451.672,0,0,451.672,604.871,530.788)">
                                <path
                                    d="M0.336,0.012C0.314,0.012 0.292,0.007 0.271,-0.005C0.25,-0.016 0.229,-0.032 0.21,-0.054L0.206,-0.054L0.192,-0L0.058,-0L0.058,-0.696L0.23,-0.696L0.23,-0.534L0.226,-0.462C0.243,-0.478 0.263,-0.49 0.284,-0.499C0.305,-0.508 0.327,-0.512 0.349,-0.512C0.388,-0.512 0.423,-0.501 0.452,-0.481C0.481,-0.46 0.504,-0.43 0.52,-0.393C0.536,-0.355 0.544,-0.31 0.544,-0.259C0.544,-0.202 0.534,-0.153 0.514,-0.112C0.494,-0.071 0.468,-0.041 0.437,-0.02C0.405,0.002 0.371,0.012 0.336,0.012ZM0.294,-0.128C0.307,-0.128 0.32,-0.132 0.331,-0.14C0.342,-0.148 0.351,-0.161 0.358,-0.18C0.365,-0.199 0.368,-0.224 0.368,-0.256C0.368,-0.283 0.365,-0.306 0.36,-0.323C0.355,-0.339 0.347,-0.352 0.337,-0.36C0.326,-0.368 0.313,-0.372 0.298,-0.372C0.285,-0.372 0.274,-0.369 0.263,-0.364C0.252,-0.359 0.241,-0.349 0.23,-0.336L0.23,-0.152C0.24,-0.143 0.251,-0.137 0.262,-0.134C0.273,-0.13 0.283,-0.128 0.294,-0.128Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(451.672,0,0,451.672,850.128,530.788)">
                                <path
                                    d="M0.29,0.012C0.241,0.012 0.198,0.002 0.159,-0.019C0.12,-0.04 0.089,-0.07 0.066,-0.109C0.043,-0.148 0.032,-0.195 0.032,-0.25C0.032,-0.304 0.044,-0.351 0.067,-0.39C0.09,-0.429 0.119,-0.459 0.156,-0.48C0.193,-0.501 0.231,-0.512 0.272,-0.512C0.321,-0.512 0.362,-0.501 0.395,-0.48C0.427,-0.458 0.451,-0.429 0.467,-0.392C0.482,-0.355 0.49,-0.315 0.49,-0.27C0.49,-0.255 0.489,-0.241 0.488,-0.228C0.486,-0.214 0.484,-0.204 0.483,-0.198L0.168,-0.198L0.166,-0.31L0.344,-0.31C0.344,-0.329 0.339,-0.346 0.33,-0.361C0.32,-0.375 0.302,-0.382 0.276,-0.382C0.262,-0.382 0.248,-0.378 0.235,-0.371C0.221,-0.363 0.21,-0.349 0.201,-0.33C0.192,-0.311 0.189,-0.284 0.19,-0.25C0.191,-0.213 0.198,-0.186 0.211,-0.167C0.224,-0.148 0.239,-0.135 0.258,-0.128C0.276,-0.121 0.295,-0.118 0.314,-0.118C0.331,-0.118 0.349,-0.121 0.366,-0.126C0.383,-0.131 0.4,-0.138 0.418,-0.148L0.472,-0.046C0.445,-0.027 0.415,-0.013 0.382,-0.003C0.349,0.007 0.318,0.012 0.29,0.012Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(451.672,0,0,451.672,1066.48,530.788)">
                                <path
                                    d="M0.188,0.012C0.157,0.012 0.13,0.005 0.108,-0.01C0.085,-0.024 0.068,-0.043 0.056,-0.066C0.044,-0.089 0.038,-0.115 0.038,-0.142C0.038,-0.194 0.059,-0.235 0.102,-0.265C0.145,-0.294 0.214,-0.314 0.31,-0.324C0.309,-0.336 0.305,-0.346 0.3,-0.354C0.295,-0.361 0.287,-0.367 0.277,-0.371C0.267,-0.374 0.255,-0.376 0.24,-0.376C0.222,-0.376 0.203,-0.373 0.183,-0.367C0.163,-0.36 0.14,-0.35 0.114,-0.336L0.054,-0.446C0.077,-0.46 0.101,-0.472 0.125,-0.482C0.149,-0.492 0.173,-0.5 0.198,-0.505C0.223,-0.51 0.248,-0.512 0.274,-0.512C0.317,-0.512 0.355,-0.504 0.386,-0.488C0.417,-0.471 0.44,-0.446 0.457,-0.412C0.474,-0.377 0.482,-0.333 0.482,-0.278L0.482,-0L0.342,-0L0.33,-0.048L0.326,-0.048C0.306,-0.03 0.285,-0.016 0.263,-0.005C0.24,0.007 0.215,0.012 0.188,0.012ZM0.248,-0.12C0.262,-0.12 0.274,-0.123 0.283,-0.13C0.292,-0.136 0.301,-0.144 0.31,-0.154L0.31,-0.222C0.283,-0.218 0.261,-0.213 0.246,-0.206C0.23,-0.199 0.219,-0.192 0.212,-0.183C0.205,-0.174 0.202,-0.165 0.202,-0.156C0.202,-0.145 0.206,-0.136 0.214,-0.13C0.222,-0.123 0.233,-0.12 0.248,-0.12Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(451.672,0,0,451.672,1291.86,530.788)">
                                <path
                                    d="M0.288,0.012C0.239,0.012 0.196,0.002 0.157,-0.019C0.118,-0.04 0.088,-0.07 0.066,-0.109C0.043,-0.148 0.032,-0.195 0.032,-0.25C0.032,-0.305 0.045,-0.353 0.07,-0.392C0.094,-0.431 0.127,-0.46 0.168,-0.481C0.209,-0.502 0.253,-0.512 0.3,-0.512C0.33,-0.512 0.357,-0.507 0.382,-0.498C0.407,-0.489 0.428,-0.476 0.446,-0.46L0.366,-0.354C0.358,-0.36 0.35,-0.365 0.341,-0.369C0.332,-0.372 0.322,-0.374 0.31,-0.374C0.289,-0.374 0.271,-0.369 0.256,-0.359C0.241,-0.349 0.229,-0.335 0.221,-0.316C0.212,-0.297 0.208,-0.275 0.208,-0.25C0.208,-0.225 0.212,-0.203 0.221,-0.184C0.23,-0.165 0.241,-0.151 0.256,-0.141C0.271,-0.131 0.287,-0.126 0.306,-0.126C0.32,-0.126 0.334,-0.129 0.347,-0.134C0.36,-0.139 0.372,-0.145 0.384,-0.154L0.45,-0.048C0.429,-0.029 0.405,-0.015 0.376,-0.004C0.347,0.007 0.318,0.012 0.288,0.012Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(451.672,0,0,451.672,1474.79,530.788)">
                                <path
                                    d="M0.28,0.012C0.237,0.012 0.197,0.002 0.16,-0.019C0.122,-0.04 0.091,-0.07 0.068,-0.109C0.044,-0.148 0.032,-0.195 0.032,-0.25C0.032,-0.305 0.044,-0.353 0.068,-0.392C0.091,-0.431 0.122,-0.46 0.16,-0.481C0.197,-0.502 0.237,-0.512 0.28,-0.512C0.323,-0.512 0.363,-0.502 0.401,-0.481C0.438,-0.46 0.469,-0.431 0.493,-0.392C0.516,-0.353 0.528,-0.305 0.528,-0.25C0.528,-0.195 0.516,-0.148 0.493,-0.109C0.469,-0.07 0.438,-0.04 0.401,-0.019C0.363,0.002 0.323,0.012 0.28,0.012ZM0.28,-0.126C0.297,-0.126 0.311,-0.131 0.322,-0.141C0.333,-0.151 0.34,-0.165 0.345,-0.184C0.35,-0.203 0.352,-0.225 0.352,-0.25C0.352,-0.275 0.35,-0.297 0.345,-0.316C0.34,-0.335 0.333,-0.349 0.322,-0.359C0.311,-0.369 0.297,-0.374 0.28,-0.374C0.263,-0.374 0.249,-0.369 0.238,-0.359C0.227,-0.349 0.22,-0.335 0.215,-0.316C0.21,-0.297 0.208,-0.275 0.208,-0.25C0.208,-0.225 0.21,-0.203 0.215,-0.184C0.22,-0.165 0.227,-0.151 0.238,-0.141C0.249,-0.131 0.263,-0.126 0.28,-0.126Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                            <g transform="matrix(451.672,0,0,451.672,1711.01,530.788)">
                                <path
                                    d="M0.058,-0L0.058,-0.5L0.198,-0.5L0.21,-0.44L0.214,-0.44C0.234,-0.459 0.257,-0.476 0.283,-0.491C0.308,-0.505 0.338,-0.512 0.372,-0.512C0.427,-0.512 0.467,-0.494 0.492,-0.457C0.516,-0.42 0.528,-0.37 0.528,-0.308L0.528,-0L0.356,-0L0.356,-0.286C0.356,-0.318 0.352,-0.339 0.344,-0.35C0.336,-0.361 0.323,-0.366 0.306,-0.366C0.29,-0.366 0.277,-0.363 0.266,-0.356C0.255,-0.349 0.243,-0.34 0.23,-0.328L0.23,-0L0.058,-0Z"
                                    style={{ fillRule: 'nonzero' }}
                                />
                            </g>
                        </g>
                    </svg>
                </div>
                <h1 className="text-4xl text-center w-full font-bold text-transparent bg-clip-text inline-block px-4">
                    <AuroraText colors={['#00e281', '#00e7a4', '#00f1ed', '#00b9fa', '#009aff']} speed={4}>
                        Feature Flag Management For Laravel
                    </AuroraText>
                </h1>
                <div className="perspective-[25vh] aspect-square relative top-20 xl:top-30 transform-3d transform-gpu sm:w-3/4 xl:left-[7.5%] md:left-5 -left-5 w-full">
                    <div
                        className={cn(
                            'sm:w-full w-10/12 absolute translate-x-[15%] sm:translate-x-[0%] translate-y-[0%] rotate-y-4 rotate-x-0 rounded-md z-30',
                            '[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent motion-safe:animate-border',
                        )}
                        style={{
                            maskImage: 'linear-gradient(8deg,transparent 0%, black 67%, black 100%)',
                        }}
                    >
                        <img
                            src="/images/hero/hero-applications-light.png"
                            alt="Beacon Screenshot"
                            className="dark:hidden rounded-md"
                        />
                        <img
                            src="/images/hero/hero-applications-dark.png"
                            alt="Beacon Screenshot"
                            className="hidden dark:block rounded-md"
                        />
                    </div>
                    <div
                        className={cn(
                            'sm:w-full w-10/12 absolute translate-x-[17%] sm:translate-x-[2%] translate-y-[20%] sm:translate-y-[15%] rotate-y-4 rotate-x-0 rounded-md z-30',
                            '[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent motion-safe:animate-border',
                        )}
                        style={{
                            maskImage: 'linear-gradient(8deg,transparent 0%, black 67%, black 100%)',
                        }}
                    >
                        <img
                            src="/images/hero/hero-feature-flags-light.png"
                            alt="Beacon Screenshot"
                            className="dark:hidden rounded-md"
                        />
                        <img
                            src="/images/hero/hero-feature-flags-dark.png"
                            alt="Beacon Screenshot"
                            className="hidden dark:block rounded-md"
                        />
                    </div>
                    <div
                        className={cn(
                            'sm:w-full w-10/12 absolute translate-x-[19%] sm:translate-x-[4%] translate-y-[40%] sm:translate-y-[30%] rotate-y-4 rotate-x-0 rounded-md z-30',
                            '[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent motion-safe:animate-border',
                        )}
                        style={{
                            maskImage: 'linear-gradient(8deg,transparent 0%, black 67%, black 100%)',
                        }}
                    >
                        <img
                            src="/images/hero/hero-dashboard-light.png"
                            alt="Beacon Screenshot"
                            className="dark:hidden rounded-md"
                        />
                        <img
                            src="/images/hero/hero-dashboard-dark.png"
                            alt="Beacon Screenshot"
                            className="hidden dark:block rounded-md"
                        />
                    </div>
                </div>
            </header>

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
                <section id="pricing" className="scroll-mt-8" ref={pricingRef}>
                    <Pricing products={products as ProductCollection} />
                </section>
            )}

            <Footer />
        </>
    );
}
