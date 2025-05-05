import BeaconIcon from '@/Components/BeaconIcon';
import GetStarted from '@/Components/illustrations/get-started';
import { Button } from '@/Components/ui/button';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/Components/ui/navigation-menu';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowUp, ChevronRight } from 'lucide-react';
import React, { useEffect, useState } from 'react';

export default function NavMenu({ showLogo = false }: { showLogo?: boolean }) {
    const [showScrollToTop, setShowScrollToTop] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setShowScrollToTop(window.scrollY > 200);
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        history.pushState('', document.title, window.location.pathname);
    };

    const ListItem = React.forwardRef<React.ElementRef<'a'>, React.ComponentPropsWithoutRef<'a'>>(
        ({ className, title, children, ...props }, ref) => {
            return (
                <li>
                    <NavigationMenuLink asChild>
                        <a
                            ref={ref}
                            className={cn(
                                'block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
                                className,
                            )}
                            {...props}
                        >
                            <div className="text-sm font-medium leading-none">{title}</div>
                            <p className="line-clamp-2 text-sm leading-snug text-muted-foreground">{children}</p>
                        </a>
                    </NavigationMenuLink>
                </li>
            );
        },
    );
    ListItem.displayName = 'ListItem';

    return (
        <div>
            <div className="fixed w-full top-0 bg-secondary z-100 h-12">
                <NavigationMenu className="mx-auto h-12">
                    <NavigationMenuList className="flex flex-row h-12 items-center">
                        {(showLogo || showScrollToTop) && (
                            <div className="fixed left-0 top-0 flex flex-row h-12 items-center">
                                <NavigationMenuItem>
                                    <NavigationMenuLink
                                        className={cn(navigationMenuTriggerStyle(), 'bg-secondary text-primary')}
                                    >
                                        {showLogo && (
                                            <a href={route('welcome')}>
                                                <BeaconIcon className="h-9" />
                                            </a>
                                        )}
                                        {!showLogo && (
                                            <div onClick={scrollToTop}>
                                                <BeaconIcon className="h-9" />
                                            </div>
                                        )}
                                    </NavigationMenuLink>
                                </NavigationMenuItem>
                            </div>
                        )}
                        <NavigationMenuItem>
                            <NavigationMenuTrigger className="bg-secondary text-primary">
                                Getting started
                            </NavigationMenuTrigger>
                            <NavigationMenuContent>
                                <ul className="grid gap-3 p-4 md:w-[400px] lg:w-[500px] lg:grid-cols-[.75fr_1fr]">
                                    <li className="row-span-3">
                                        <NavigationMenuLink asChild>
                                            <div className="bg-secondary dark:bg-primary h-full">
                                                <GetStarted />
                                            </div>
                                        </NavigationMenuLink>
                                    </li>
                                    <ListItem href="/docs/install" title="Features">
                                        Learn all about the features of Beacon.
                                    </ListItem>
                                    <ListItem href="/docs/install" title="Installation">
                                        Integrate with your Laravel app via Laravel Pennant.
                                    </ListItem>
                                    <ListItem href="/docs/get-started" title="Create Your First Feature Flag">
                                        Learn how to create your first feature flag in Beacon and use it in your app.
                                    </ListItem>
                                </ul>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                        <NavigationMenuItem>
                            <a href="/docs">
                                <NavigationMenuLink
                                    className={cn(navigationMenuTriggerStyle(), 'bg-secondary text-primary')}
                                >
                                    Documentation
                                </NavigationMenuLink>
                            </a>
                        </NavigationMenuItem>
                        <NavigationMenuItem>
                            <Link href={`${route('welcome')}#pricing`}>
                                <NavigationMenuLink
                                    className={cn(navigationMenuTriggerStyle(), 'bg-secondary text-primary')}
                                >
                                    Pricing
                                </NavigationMenuLink>
                            </Link>
                        </NavigationMenuItem>
                        <div className="fixed right-0 top-0 flex flex-row h-12 items-center">
                            {!route().current('login') && (
                                <NavigationMenuItem>
                                    <NavigationMenuLink
                                        className={cn(
                                            navigationMenuTriggerStyle(),
                                            'bg-secondary text-primary group/login',
                                        )}
                                    >
                                        <Link href={route('login')} className="flex flex-row items-center">
                                            <ChevronRight
                                                size={12}
                                                className="group-hover/login:motion-safe:translate-x-13 transition-all inline-block"
                                            />
                                            Sign in
                                        </Link>
                                    </NavigationMenuLink>
                                </NavigationMenuItem>
                            )}
                            <NavigationMenuItem>
                                <NavigationMenuLink
                                    className={cn(navigationMenuTriggerStyle(), 'bg-secondary text-primary')}
                                >
                                    {!route().current('register') && (
                                        <Link href={route('register')}>
                                            <Button type="button" size="sm">
                                                Start Your Free Trial
                                            </Button>
                                        </Link>
                                    )}
                                    {route().current('register') && (
                                        <Button type="button" size="sm" disabled>
                                            Start Your Free Trial
                                        </Button>
                                    )}
                                </NavigationMenuLink>
                            </NavigationMenuItem>
                        </div>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>
            <div
                id="scroll-to-top"
                onClick={scrollToTop}
                className={`fixed bottom-5 right-5 z-100 bg-primary w-16 h-16 rounded-full flex items-center text-secondary group/scroll-to-top cursor-pointer transition-opacity duration-300 ${
                    showScrollToTop ? 'opacity-100' : 'opacity-0 pointer-events-none'
                }`}
            >
                <ArrowUp className="mx-auto group-hover/scroll-to-top:motion-safe:animate-bounce" />
            </div>
        </div>
    );
}
