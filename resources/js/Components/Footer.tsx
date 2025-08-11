import { ProductCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { SiGithub, SiYoutube } from '@icons-pack/react-simple-icons';
import { Link, usePage } from '@inertiajs/react';
import React from 'react';

const Footer = ({ expanded = true }: { expanded?: boolean }) => {
    const pricingEnabled = usePage().props.features['pricing.enabled'];
    const { products, docsUrl, auth } = usePage().props;
    if (expanded) {
        return (
            <footer className="bg-secondary mt-8">
                <div className=" w-3/4 mx-auto">
                    <div className="flex flex-col md:flex-row gap-12 py-12 text-primary">
                        {!auth && (
                            <div className="prose dark:prose-invert w-full md:w-1/3">
                                <h3>Get Started Today</h3>
                                {pricingEnabled && (
                                    <>
                                        <p className="text-lg mb-8">
                                            Unlimited flags, apps, environments, and users — for the price of a coffee.
                                        </p>
                                        <a href={`/register?plan=${(products as ProductCollection)[0].id}`}>
                                            <Button size="lg">Start Free Trial</Button>
                                        </a>
                                    </>
                                )}
                                {!pricingEnabled && (
                                    <>
                                        <p className="text-lg mb-8">
                                            Sign up today to start managing your feature flags with Beacon.
                                        </p>
                                        <a href="/register">
                                            <Button size="lg">Sign up</Button>
                                        </a>
                                    </>
                                )}
                            </div>
                        )}
                        <div
                            className={cn('w-full md:w-2/3 flex flex-row flex-wrap gap-4 sm:gap-0 justify-between', {
                                'mx-auto': auth,
                            })}
                        >
                            <div className="basis-1/3 sm:basis-1/2 md:basis-1/4">
                                <h4 className="font-bold mb-2">Product</h4>
                                <ul>
                                    <li>
                                        <Link href={docsUrl + '/introduction/overview.html#key-features'}>
                                            Features
                                        </Link>
                                    </li>
                                    {pricingEnabled && (
                                        <li>
                                            <Link href={route().current('checkout.index') ? '#pricing' : '/#pricing'}>
                                                Pricing
                                            </Link>
                                        </li>
                                    )}
                                    <li>
                                        <Link href="/#faq">FAQ</Link>
                                    </li>
                                    {pricingEnabled && (
                                        <li>
                                            <a href="mailto:support@beacon-hq.dev">Support</a>
                                        </li>
                                    )}
                                </ul>
                            </div>
                            <div className="basis-1/3 sm:basis-1/2 md:basis-1/4">
                                {pricingEnabled && (
                                    <>
                                        <h4 className="font-bold mb-2">Company</h4>
                                        <ul>
                                            <li>
                                                <Link href={route('company.our-team')}>Our Team</Link>
                                            </li>
                                            <li>
                                                <a href="mailto:hello@beacon-hq.dev">Contact</a>
                                            </li>
                                        </ul>
                                    </>
                                )}
                            </div>
                            <div className="basis-1/3 sm:basis-1/2 md:basis-1/4">
                                <h4 className="font-bold mb-2">Resources</h4>
                                <ul>
                                    <li>
                                        <Link href={docsUrl + '/introduction/get-started.html'}>Get Started</Link>
                                    </li>
                                    <li>
                                        <Link href={docsUrl + '/index.html'}>Documentation</Link>
                                    </li>
                                    <li>
                                        <SiGithub className="h-4 w-4 inline-block mr-1" /> Github
                                    </li>
                                    <li>
                                        <SiYoutube className="h-4 w-4 inline-block mr-1" /> YouTube
                                    </li>
                                </ul>
                            </div>
                            <div className="basis-1/3 sm:basis-1/2 md:basis-1/4">
                                {pricingEnabled && (
                                    <>
                                        <h4 className="font-bold mb-2">Legal</h4>
                                        <ul>
                                            <li>
                                                <Link href={route('company.privacy-policy')}>Privacy Policy</Link>
                                            </li>
                                            <li>
                                                <Link href={route('company.terms-of-service')}>Terms of Service</Link>
                                            </li>
                                        </ul>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="container mx-auto px-4 text-center">
                    <p className="text-sm mt-4 mb-2">Made with 🦁💖🏳️‍🌈 by Davey Shafik.</p>
                    <p className="text-sm">
                        Released under the{' '}
                        <a href="https://github.com/beacon-hq/app/blob/main/LICENSE.md">FCL-1.0-MIT License</a>.
                        Copyright © 2024-{new Date().getFullYear()} Davey Shafik.
                    </p>
                </div>
            </footer>
        );
    }

    return (
        <footer className="text-center text-neutral-400 bg-background w-full text-xs py-4">
            <p>Made with 🦁💖🏳️‍🌈 by Davey Shafik.</p>
            <p>
                Released under the <a href="https://github.com/beacon-hq/app/blob/main/LICENSE.md">FCL-1.0-MIT</a>{' '}
                License. Copyright © 2024-{new Date().getFullYear()} Davey Shafik.
            </p>
        </footer>
    );
};

export default Footer;
