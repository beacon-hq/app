import BeaconIcon from '@/Components/BeaconIcon';
import EarlyAccessNotice from '@/Components/EarlyAccessNotice';
import GetStarted from '@/Components/illustrations/get-started';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/Components/ui/navigation-menu';
import { Sheet, SheetContent, SheetTrigger } from '@/Components/ui/sheet';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import useScrollToLocation from '@/hooks/use-scroll-to-location';
import { cn } from '@/lib/utils';
import { AuthProp } from '@/types';
import { Method } from '@inertiajs/core';
import { Link, usePage } from '@inertiajs/react';
import { ArrowUp, Circle, Menu } from 'lucide-react';
import React, { useEffect, useState } from 'react';

interface MenuItem {
    title: string;
    url?: string;
    method?: Method;
    onClick?: () => void;
    description?: string;
    icon?: React.ReactNode;
    graphic?: React.ReactNode;
    items?: MenuItem[];
}

interface NavbarData {
    menu: MenuItem[];
    auth: {
        login: {
            title: string;
            url: string;
        };
        signup: {
            title: string;
            url: string;
        };
    };
    docs: {
        url: string;
    };
}

export default function NavMenu({
    auth,
    showLogo = false,
    docsUrl = '/docs',
}: AuthProp & {
    showLogo?: boolean;
    docsUrl?: string;
}) {
    const pricingEnabled = usePage().props.features['pricing.enabled'];
    const status = usePage().props.status;

    const navbarData: NavbarData = {
        menu: [
            {
                title: 'Get Started',
                items: [
                    {
                        title: 'Features',
                        url: docsUrl + '/introduction/overview.html#key-features',
                        description: 'Learn all about the features of Beacon.',
                    },
                    {
                        title: 'Installation',
                        url: docsUrl + '/introduction/install.html',
                        description: 'Integrate with your Laravel app via Laravel Pennant.',
                    },
                    {
                        title: 'Create Your First Feature Flag',
                        url: docsUrl + '/introduction/get-started.html',
                        description: 'Learn how to create your first feature flag in Beacon.',
                    },
                ],
                graphic: <GetStarted />,
            },
            {
                title: 'Documentation',
                url: docsUrl + '/index.html',
            },
            ...(pricingEnabled
                ? [
                      {
                          title: 'Pricing',
                          url: route().current('checkout.index') ? '#pricing' : '/#pricing',
                          onClick: () => {
                              setNavBarOpen(false);
                          },
                      },
                  ]
                : []),
            ...(status !== undefined && status !== null
                ? [
                      {
                          title: 'Status',
                          url: status.url,
                          icon: (
                              <Tooltip>
                                  <TooltipTrigger>
                                      <Circle
                                          className={cn('w-4 h-4 mr-2', {
                                              'text-green-500 fill-green-500': status.data.status === 'operational',
                                              'text-yellow-500 fill-yellow-500':
                                                  status.data.status === 'partial_outage',
                                              'text-red-500 fill-red-500': status.data.status === 'major_outage',
                                          })}
                                      />
                                  </TooltipTrigger>
                                  <TooltipContent className="z-100">{status.data.message}</TooltipContent>
                              </Tooltip>
                          ),
                      },
                  ]
                : []),
        ],
        auth: {
            login: {
                title: 'Sign In',
                url: '/login',
            },
            signup: {
                title: pricingEnabled ? 'Start Your Free Trial' : 'Sign Up',
                url: '/register',
            },
        },
        docs: {
            url: docsUrl ?? '/docs',
        },
    };
    const [navBarOpen, setNavBarOpen] = useState(false);

    useScrollToLocation(function () {
        setNavBarOpen(false);
    });

    const [showScrollToTop, setShowScrollToTop] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setShowScrollToTop(window.scrollY > 200);
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const scrollToTop = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        history.pushState('', document.title, window.location.pathname);
    };

    return (
        <>
            <section className="py-4 z-100">
                <div className="container">
                    <nav className="hidden bg-background z-100 px-12 py-2 items-center md:flex flex-row fixed w-full top-0 text-primary">
                        <div className="flex grow justify-between items-center">
                            <div className="w-48">
                                {showLogo && (
                                    <a href={route('welcome')}>
                                        <BeaconIcon className="h-12" />
                                    </a>
                                )}
                                {!showLogo && (
                                    <button
                                        onClick={scrollToTop}
                                        className={cn('cursor-pointer', { invisible: !showScrollToTop })}
                                    >
                                        <BeaconIcon className="h-12" />
                                    </button>
                                )}
                            </div>
                            <div
                                className={cn('flex items-center gap-6 grow', {
                                    '-ml-18': auth?.user !== null && route().current('checkout.*'),
                                    'ml-24': auth?.user === null,
                                })}
                            >
                                <div className="flex items-center justify-end mx-auto">
                                    <NavigationMenu>
                                        <NavigationMenuList>
                                            {navbarData.menu.map((item) => renderMenuItem(item))}
                                        </NavigationMenuList>
                                    </NavigationMenu>
                                </div>
                            </div>
                            {!auth?.user && (
                                <div className="flex gap-2 w-48">
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={navbarData.auth.login.url}>{navbarData.auth.login.title}</Link>
                                    </Button>
                                    <Button asChild size="sm">
                                        <Link href={navbarData.auth.signup.url}>{navbarData.auth.signup.title}</Link>
                                    </Button>
                                </div>
                            )}
                            {auth?.user && !route().current('checkout.*') && (
                                <div className="flex gap-2 w-48 justify-end">
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={route('dashboard')}>Dashboard</Link>
                                    </Button>
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={route('logout')} method="post">
                                            Logout
                                        </Link>
                                    </Button>
                                </div>
                            )}
                            {auth?.user && route().current('checkout.*') && (
                                <div className="flex gap-2 w-48 justify-end">
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={route('logout')} method="post">
                                            Logout
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </div>
                    </nav>
                    <div className="lg:hidden flex justify-end">
                        <div className="flex items-center justify-between">
                            <Sheet open={navBarOpen} onOpenChange={setNavBarOpen}>
                                <nav className="bg-background z-50 px-4 py-2 w-screen text-primary h-12 fixed top-0 right-0 flex justify-between">
                                    {showLogo && (
                                        <a href={route('welcome')} className="flex items-center gap-2">
                                            <BeaconIcon />
                                        </a>
                                    )}
                                    {!showLogo && showScrollToTop && (
                                        <button onClick={scrollToTop} className="flex items-center gap-2">
                                            <BeaconIcon />
                                        </button>
                                    )}
                                    <div className="flex justify-end grow">
                                        <SheetTrigger asChild>
                                            <Button variant="outline" size="icon">
                                                <Menu className="size-4" />
                                            </Button>
                                        </SheetTrigger>
                                    </div>
                                </nav>
                                <SheetContent className="overflow-y-auto text-primary">
                                    <div className="flex flex-col gap-6 p-4">
                                        <Accordion type="single" collapsible className="flex w-full flex-col gap-4">
                                            {navbarData.menu.map((item) => renderMobileMenuItem(item))}
                                        </Accordion>

                                        <div className="flex flex-col gap-3">
                                            <Button asChild variant="outline">
                                                <a href={navbarData.auth.login.url}>{navbarData.auth.login.title}</a>
                                            </Button>
                                            <Button asChild>
                                                <a href={navbarData.auth.signup.url}>{navbarData.auth.signup.title}</a>
                                            </Button>
                                        </div>
                                    </div>
                                </SheetContent>
                            </Sheet>
                        </div>
                    </div>
                </div>
                <button
                    id="scroll-to-top"
                    onClick={scrollToTop}
                    className={`fixed bottom-5 right-5 z-100 bg-primary w-16 h-16 rounded-full flex items-center text-secondary group/scroll-to-top cursor-pointer transition-opacity duration-300 ${
                        showScrollToTop ? 'opacity-100' : 'opacity-0 pointer-events-none'
                    }`}
                >
                    <ArrowUp className="mx-auto group-hover/scroll-to-top:motion-safe:animate-bounce" />
                </button>
            </section>
            <EarlyAccessNotice className="fixed top-17 z-50" />
        </>
    );
}

const renderMenuItem = (item: MenuItem) => {
    if (item.items) {
        return (
            <NavigationMenuItem key={item.title}>
                <NavigationMenuTrigger>{item.title}</NavigationMenuTrigger>
                <NavigationMenuContent className={cn('bg-popover text-popover-foreground')}>
                    <ul className="grid gap-3 p-4 md:w-[400px] lg:w-[500px] lg:grid-cols-[.75fr_1fr]">
                        {item.graphic && (
                            <NavigationMenuLink asChild>
                                <li className="row-span-3">
                                    <div className="bg-secondary dark:bg-primary w-full h-full">{item.graphic}</div>
                                </li>
                            </NavigationMenuLink>
                        )}
                        {item.items.map((subItem) => (
                            <li key={subItem.title}>
                                <NavigationMenuLink asChild className="w-80">
                                    <SubMenuLink item={subItem} />
                                </NavigationMenuLink>
                            </li>
                        ))}
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        );
    }

    return (
        <NavigationMenuItem key={item.title}>
            <NavigationMenuLink
                href={item.url}
                onClick={item.onClick}
                className="group flex flex-row h-10 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-accent-foreground"
            >
                {item.icon}
                {item.title}
            </NavigationMenuLink>
        </NavigationMenuItem>
    );
};

const renderMobileMenuItem = (item: MenuItem) => {
    if (item.items) {
        return (
            <AccordionItem key={item.title} value={item.title} className="border-b-0">
                <AccordionTrigger className="text-md py-0 font-semibold hover:no-underline">
                    {item.title}
                </AccordionTrigger>
                <AccordionContent className="mt-2">
                    {item.items.map((subItem) => (
                        <SubMenuLink key={subItem.title} item={subItem} />
                    ))}
                </AccordionContent>
            </AccordionItem>
        );
    }

    return item.method ? (
        <Link
            key={item.title}
            href={item.url as string}
            method={item.method as Method}
            className="text-md font-semibold"
        >
            {item.title}
        </Link>
    ) : (
        <a key={item.title} href={item.url as string} className="text-md font-semibold" onClick={item.onClick}>
            {item.title}
        </a>
    );
};

const SubMenuLink = ({ item }: { item: MenuItem }) => {
    console.log(item);
    return (
        <a
            className="flex flex-row gap-4 rounded-md p-3 leading-none no-underline transition-colors outline-none select-none hover:bg-muted hover:text-accent-foreground"
            href={item.url}
        >
            <div className="text-foreground">{item.icon}</div>
            <div>
                <div className="text-sm font-semibold">{item.title}</div>
                {item.description && <p className="text-sm leading-snug text-muted-foreground">{item.description}</p>}
            </div>
        </a>
    );
};
