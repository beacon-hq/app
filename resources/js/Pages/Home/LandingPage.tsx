import Landing from '@/Layouts/LandingLayout';
import { AuroraText } from '@/components/ui/aurora-text';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';
import useSound from 'use-sound';
import { useTimeout } from 'usehooks-ts';

export default function LandingPage({ waitlistEnabled = true }: { waitlistEnabled: boolean }) {
    const [toggle, setToggle] = React.useState(false);
    const [toggled, setToggled] = React.useState(false);
    const [showSendIcon, setShowSendIcon] = useState(true);
    const [showCheckIcon, setShowCheckIcon] = useState(false);
    const [checkDrawProgress, setCheckDrawProgress] = useState(0);

    const inputRef = useRef<HTMLInputElement | null>(null);

    const { data, setData, post, processing, reset, wasSuccessful } = useForm({
        email: '',
    });

    const sendPath = 'M22 2L11 13M22 2L15 22L11 13L2 9L22 2Z';
    const checkPath = 'M4 12L9 17L20 6';

    const sendIconRef = useRef<SVGGElement>(null);
    const checkIconRef = useRef<SVGPathElement>(null);

    const checkPathLength = useRef<number>(0);
    useEffect(() => {
        if (checkIconRef.current) {
            checkPathLength.current = checkIconRef.current.getTotalLength();

            checkIconRef.current.style.strokeDasharray = `${checkPathLength.current}`;
            checkIconRef.current.style.strokeDashoffset = `${checkPathLength.current}`;
        }
    }, []);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.email || processing || wasSuccessful) return;

        post('/subscribe', {
            onStart: () => {
                // Form submission is starting
            },
            onSuccess: () => {
                startAnimationSequence();

                if (waitlistEnabled) {
                    setToggle(true);
                }

                reset();
            },
            onError: () => {
                setShowSendIcon(true);
                setShowCheckIcon(false);
                setCheckDrawProgress(0);
            },
        });
    };

    const startAnimationSequence = () => {
        animateSendIconFlyOff();

        setTimeout(() => {
            setShowSendIcon(false);
            setShowCheckIcon(true);
            animateCheckIconDraw();
        }, 650);
    };

    const animateSendIconFlyOff = () => {
        if (sendIconRef.current) {
            const element = sendIconRef.current as Element;
            element.animate(
                [
                    { opacity: 1, transform: 'scale(1) translate(0, 0) rotate(0deg)' },
                    { opacity: 0.8, transform: 'scale(1.2) translate(20px, -20px) rotate(5deg)' },
                    { opacity: 0, transform: 'scale(0.3) translate(150px, -100px) rotate(15deg)' },
                ],
                {
                    duration: 600,
                    easing: 'cubic-bezier(0.25, 0.1, 0.25, 1)',
                    fill: 'forwards',
                },
            );
        }
    };

    const animateCheckIconDraw = () => {
        const duration = 1000;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;

            let progress = Math.min(elapsed / duration, 1);

            if (progress < 0.5) {
                progress = 2 * progress * progress;
            } else {
                progress = -1 + (4 - 2 * progress) * progress;
            }

            setCheckDrawProgress(progress);

            if (elapsed < duration) {
                requestAnimationFrame(animate);
            } else {
                setCheckDrawProgress(1); // Ensure we finish at exactly 1
            }
        };

        animate();
    };

    useEffect(() => {
        if (checkIconRef.current && showCheckIcon) {
            const dashOffset = checkPathLength.current * (1 - checkDrawProgress);

            checkIconRef.current.style.strokeDashoffset = `${dashOffset}`;
        }
    }, [checkDrawProgress, showCheckIcon]);

    const toggleAppearence = () => {
        if (waitlistEnabled) {
            setTimeout(() => {
                if (!toggle) {
                    inputRef.current !== null && inputRef.current.focus();
                }
            }, 1000);
            setToggle(!toggle);
            setToggled(true);
            play();
        }
    };

    const click = '/sounds/click.mp3';

    const [play] = useSound(click, { volume: 1 });

    // useTimeout(() => {
    //     if (waitlistEnabled && !toggled) {
    //         toggleAppearence();
    //     }
    // }, 4000);

    return (
        <Landing>
            <Head title="Beacon" />

            <section
                className={cn(
                    'bg-black w-full h-screen transition-[background] duration-1000 flex flex-col justify-center overflow-hidden',
                    {
                        'bg-aurora': toggle && waitlistEnabled,
                        'overflow-auto': toggle && waitlistEnabled,
                    },
                )}
            >
                <div
                    className={cn(
                        'transition-[translate] translate-y-20  duration-1000 motion-opacity-in-0 motion-duration-[8s]',
                        {
                            'translate-y-8': toggle,
                        },
                    )}
                >
                    <h1
                        className={cn(
                            'text-center font-bold opacity-0 text-white text-8xl mb-4 transition-[opacity] duration-500',
                            {
                                'opacity-100': toggle,
                            },
                        )}
                    >
                        Beacon
                    </h1>
                    <svg
                        className={cn('inset-0 -mt-8 mx-auto xl:w-3/13 lg:w-1/3 md:w-3/8 w-1/2')}
                        viewBox="0 0 510 680"
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        style={{
                            fillRule: 'evenodd',
                            clipRule: 'evenodd',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            strokeMiterlimit: 1.5,
                        }}
                    >
                        <g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M508.527,180.555L508.527,561.134C508.527,596.742 479.618,625.651 444.01,625.651L64.518,625.651C28.909,625.651 -0,596.742 -0,561.134L-0,180.555C-0,144.947 28.909,116.037 64.518,116.037L444.01,116.037C479.618,116.037 508.527,144.947 508.527,180.555Z"
                                    style={{ fill: 'rgb(235,235,235)' }}
                                />
                            </g>
                            <g transform="matrix(0.981804,0,0,0.981804,5.36317,-24.0877)">
                                <path
                                    d="M508.527,180.555L508.527,561.134C508.527,596.742 479.618,625.651 444.01,625.651L64.518,625.651C28.909,625.651 -0,596.742 -0,561.134L-0,180.555C-0,144.947 28.909,116.037 64.518,116.037L444.01,116.037C479.618,116.037 508.527,144.947 508.527,180.555Z"
                                    style={{ fill: !toggle ? 'url(#border-gradient)' : 'none' }}
                                    className="animate-pulse blur-xl"
                                />
                            </g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M508.527,180.555L508.527,561.134C508.527,596.742 479.618,625.651 444.01,625.651L64.518,625.651C28.909,625.651 -0,596.742 -0,561.134L-0,180.555C-0,144.947 28.909,116.037 64.518,116.037L444.01,116.037C479.618,116.037 508.527,144.947 508.527,180.555Z"
                                    style={{
                                        fill: toggle ? 'url(#gradient-background-light)' : '#000',
                                    }}
                                />
                            </g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M69.431,143.628C69.326,121.902 80.842,101.776 99.626,90.858C118.41,79.94 141.597,79.895 160.423,90.74C240.067,136.619 350.987,200.515 405.113,231.695C425.108,243.213 437.475,264.49 437.587,287.565C437.914,354.92 438.614,499.153 439.096,598.652C439.201,620.161 427.8,640.086 409.204,650.895C390.607,661.704 367.652,661.748 349.014,651.012C275.216,608.5 174.109,550.257 116.401,517.014C88.362,500.862 71.02,471.026 70.863,438.668C70.515,366.897 69.881,236.205 69.431,143.628Z"
                                    style={{ stroke: 'rgb(235,235,235)', strokeWidth: '1px' }}
                                />
                            </g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M69.431,143.628C69.326,121.902 80.842,101.776 99.626,90.858C118.41,79.94 141.597,79.895 160.423,90.74C240.067,136.619 350.987,200.515 405.113,231.695C425.108,243.213 437.475,264.49 437.587,287.565C437.914,354.92 438.614,499.153 439.096,598.652C439.201,620.161 427.8,640.086 409.204,650.895C390.607,661.704 367.652,661.748 349.014,651.012C275.216,608.5 174.109,550.257 116.401,517.014C88.362,500.862 71.02,471.026 70.863,438.668C70.515,366.897 69.881,236.205 69.431,143.628Z"
                                    style={{
                                        stroke: '#ebebeb',
                                        strokeWidth: '1px',
                                        fill: toggle ? 'url(#gradient-fill)' : '#fff',
                                        filter: 'drop-shadow(5px 5px 17.5px #000000)',
                                    }}
                                    className="transition-[fill]"
                                />
                            </g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M360.429,297.229L360.429,444.46C360.429,503.054 312.858,550.625 254.264,550.625C195.669,550.625 148.098,503.054 148.098,444.46L148.098,297.229C148.098,238.634 195.669,191.063 254.264,191.063C312.858,191.063 360.429,238.634 360.429,297.229Z"
                                    style={{ fill: 'rgb(210,210,210)' }}
                                />
                            </g>
                            <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                                <path
                                    d="M360.429,297.229L360.429,444.46C360.429,503.054 312.858,550.625 254.264,550.625C195.669,550.625 148.098,503.054 148.098,444.46L148.098,297.229C148.098,238.634 195.669,191.063 254.264,191.063C312.858,191.063 360.429,238.634 360.429,297.229Z"
                                    style={{ fill: 'rgb(230,230,230)' }}
                                    onClick={toggleAppearence}
                                />
                            </g>
                        </g>
                        <g transform="matrix(0.96187,0,0,0.96187,10.4316,-16.6954)">
                            <circle
                                cx="254.122"
                                cy="296.504"
                                r="93.081"
                                className={cn(
                                    'stroke-[#ebebeb] stroke-width-[1px] fill-black translate-y-36 transition-[translate] duration-1000',
                                    {
                                        'fill-[#646464]': toggle,
                                        'translate-y-0': toggle,
                                        'cursor-pointer': waitlistEnabled,
                                        'cursor-not-allowed': !waitlistEnabled,
                                    },
                                )}
                                onClick={toggleAppearence}
                            />
                        </g>
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
                            <linearGradient
                                id="border-gradient"
                                x1="0"
                                y1="0"
                                x2="1"
                                y2="0"
                                gradientUnits="userSpaceOnUse"
                                gradientTransform="matrix(451.919,487.359,-487.359,451.919,28.3046,127.164)"
                            >
                                <stop offset="0" style={{ stopColor: 'rgb(0,226,129)', stopOpacity: 1 }} />
                                <stop offset="0.16" style={{ stopColor: 'rgb(0,231,164)', stopOpacity: 1 }} />
                                <stop offset="0.34" style={{ stopColor: 'rgb(8,235,198)', stopOpacity: 1 }} />
                                <stop offset="0.55" style={{ stopColor: 'rgb(0,216,245)', stopOpacity: 1 }} />
                                <stop offset="0.74" style={{ stopColor: 'rgb(3,185,251)', stopOpacity: 1 }} />
                                <stop offset="1" style={{ stopColor: 'rgb(0,154,254)', stopOpacity: 1 }} />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div
                    className={cn(
                        'opacity-0 transition-[opacity] duration-200 w-5/6 md:w-1/2 xl:w-1/3 mx-auto mt-8 min-h-fit',
                        'rounded-lg bg-white border-neutral-200 p-8 shadow-lg',
                        {
                            'motion-translate-y-in-150 motion-duration-1000 opacity-100': toggle,
                        },
                    )}
                >
                    <h1 className="text-xl text-center w-full font-normal text-black bg-clip-text inline-block px-4">
                        Supercharge your deployments with Beacon.
                        <AuroraText colors={['#00e281', '#00e7a4', '#00f1ed', '#00b9fa', '#009aff']} speed={4}>
                            Feature flag management for Laravel.
                        </AuroraText>
                    </h1>
                    <div className="mt-6">
                        <form
                            onSubmit={handleSubmit}
                            className="flex flex-col md:flex-row items-center mx-auto gap-2 sm:gap-0"
                        >
                            <Input
                                ref={inputRef}
                                type="email"
                                name="email"
                                className={cn('text-black w-full h-12 !text-lg transition-[translate]', {
                                    'absolute translate-y-100': processing || wasSuccessful,
                                })}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing || wasSuccessful}
                                placeholder="Your email address"
                                required
                            />
                            <div
                                className={cn('md:w-1/4 w-full transition-[width,height]', {
                                    'md:ml-2': !processing && !wasSuccessful,
                                    'md:w-full h-24 md:h-12': processing || wasSuccessful,
                                })}
                            >
                                <Button
                                    type="submit"
                                    size="xl"
                                    className="bg-[rgb(0,231,164)] hover:bg-[rgb(0,216,245)] disabled:bg-[rgb(0,216,245)] transition-[width,height] text-black group overflow-hidden relative w-full p-4 min-h-full"
                                    disabled={processing || wasSuccessful}
                                >
                                    <span
                                        className={cn(
                                            'transition-all duration-300 group-hover:opacity-0',
                                            processing || wasSuccessful ? 'opacity-0' : 'opacity-100',
                                        )}
                                    >
                                        Join Waitlist
                                    </span>
                                    <span
                                        className={cn(
                                            'absolute inset-0 flex items-center justify-center',
                                            processing || wasSuccessful
                                                ? 'opacity-100'
                                                : 'opacity-0 group-hover:opacity-100 group-hover:animate-tada',
                                        )}
                                    >
                                        <svg
                                            className="!h-11/12 !w-full"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        >
                                            <g
                                                ref={sendIconRef}
                                                style={{
                                                    opacity: showSendIcon ? 1 : 0,
                                                    visibility: showSendIcon ? 'visible' : 'hidden',
                                                }}
                                            >
                                                <path d={sendPath} />
                                            </g>

                                            <path
                                                ref={checkIconRef}
                                                d={checkPath}
                                                style={{
                                                    opacity: showCheckIcon ? 1 : 0,
                                                    visibility: showCheckIcon ? 'visible' : 'hidden',
                                                }}
                                            />
                                        </svg>
                                    </span>
                                </Button>
                            </div>
                        </form>
                        <p className="text-xs text-neutral-400 text-center mt-2">
                            We will only use your email to send you emails related to the Beacon launch.
                        </p>
                    </div>
                </div>
            </section>
        </Landing>
    );
}
