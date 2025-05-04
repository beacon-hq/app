import BeaconIcon from '@/Components/BeaconIcon';
import { AnimatedBeam } from '@/Components/magicui/animated-beam';
import { cn } from '@/lib/utils';
import { AppWindow, Code2, ShieldUser } from 'lucide-react';
import { useReducedMotion } from 'motion/react';
import React, { forwardRef, useRef } from 'react';

const Circle = forwardRef<HTMLDivElement, { className?: string; children?: React.ReactNode }>(
    ({ className, children }, ref) => {
        return (
            <div
                ref={ref}
                className={cn(
                    'z-10 flex size-12 items-center justify-center rounded-full border-2 bg-white p-3 shadow-[0_0_20px_-12px_rgba(0,0,0,0.8)]',
                    className,
                )}
            >
                {children}
            </div>
        );
    },
);

Circle.displayName = 'Circle';

export function CentralizedControlAnimation({ className }: { className?: string }) {
    const reducedMotion = useReducedMotion() ?? false;

    const containerRef = useRef<HTMLDivElement>(null);

    const beacon = useRef<HTMLDivElement>(null);

    const userApp = useRef<HTMLDivElement>(null);
    const userAppLocalEnv = useRef<HTMLDivElement>(null);
    const userAppStagingEnv = useRef<HTMLDivElement>(null);
    const userAppProdEnv = useRef<HTMLDivElement>(null);

    const adminApp = useRef<HTMLDivElement>(null);
    const adminAppLocalEnv = useRef<HTMLDivElement>(null);
    const adminAppStagingEnv = useRef<HTMLDivElement>(null);

    const apiApp = useRef<HTMLDivElement>(null);
    const apiAppStagingEnv = useRef<HTMLDivElement>(null);
    const apiAppProdEnv = useRef<HTMLDivElement>(null);

    return (
        <div
            className={cn('relative flex h-[425px] w-full items-center justify-center overflow-hidden p-10', className)}
            ref={containerRef}
        >
            <div className="flex size-full max-w-lg flex-row items-stretch justify-between gap-10">
                <div className="flex flex-col justify-center">
                    <Circle ref={beacon} className="size-20">
                        <BeaconIcon />
                    </Circle>
                </div>
                <div className="flex flex-col justify-center gap-10">
                    <p>Application</p>
                    <Circle ref={userApp} className="size-16">
                        <AppWindow />
                    </Circle>

                    <Circle ref={adminApp} className="size-16">
                        <ShieldUser />
                    </Circle>

                    <Circle ref={apiApp} className="size-16">
                        <Code2 />
                    </Circle>
                </div>
                <div className="flex flex-col justify-center gap-2">
                    <div className="flex flex-col items-center justify-between gap-2">
                        <p>Environment</p>
                        <Circle ref={userAppLocalEnv} className="size-4 bg-green-400"></Circle>
                        <Circle ref={userAppStagingEnv} className="size-4 bg-orange-400"></Circle>
                        <Circle ref={userAppProdEnv} className="size-4 bg-red-400"></Circle>
                        <Circle ref={adminAppLocalEnv} className="size-4 bg-pink-400"></Circle>
                        <Circle ref={adminAppStagingEnv} className="size-4 bg-orange-400"></Circle>
                        <Circle ref={apiAppStagingEnv} className="size-4 bg-purple-400"></Circle>
                        <Circle ref={apiAppProdEnv} className="size-4 bg-red-400"></Circle>
                    </div>
                </div>
            </div>

            {/* AnimatedBeams */}
            <AnimatedBeam containerRef={containerRef} fromRef={beacon} toRef={userApp} disabled={reducedMotion} />
            <AnimatedBeam containerRef={containerRef} fromRef={beacon} toRef={adminApp} disabled={reducedMotion} />
            <AnimatedBeam containerRef={containerRef} fromRef={beacon} toRef={apiApp} disabled={reducedMotion} />

            <AnimatedBeam
                containerRef={containerRef}
                fromRef={userAppLocalEnv}
                toRef={userApp}
                disabled={reducedMotion}
            />
            <AnimatedBeam
                containerRef={containerRef}
                fromRef={userAppStagingEnv}
                toRef={userApp}
                disabled={reducedMotion}
            />
            <AnimatedBeam
                containerRef={containerRef}
                fromRef={userAppProdEnv}
                toRef={userApp}
                disabled={reducedMotion}
            />

            <AnimatedBeam
                containerRef={containerRef}
                fromRef={adminAppLocalEnv}
                toRef={adminApp}
                disabled={reducedMotion}
            />
            <AnimatedBeam
                containerRef={containerRef}
                fromRef={adminAppStagingEnv}
                toRef={adminApp}
                disabled={reducedMotion}
            />

            <AnimatedBeam
                containerRef={containerRef}
                fromRef={apiAppStagingEnv}
                toRef={apiApp}
                disabled={reducedMotion}
            />
            <AnimatedBeam containerRef={containerRef} fromRef={apiAppProdEnv} toRef={apiApp} disabled={reducedMotion} />
        </div>
    );
}
