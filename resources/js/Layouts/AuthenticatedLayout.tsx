import { ColorPicker } from '@/Components/ColorPicker';
import Footer from '@/Components/Footer';
import Icon from '@/Components/Icon';
import IconPicker from '@/Components/IconPicker';
import Sidebar from '@/Components/Sidebar';
import TeamSelect from '@/Components/TeamSelect';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { SidebarTrigger, SidebarWrapper, useSidebar } from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/sonner';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import React, { Fragment, PropsWithChildren, useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Authenticated({
    breadcrumbs,
    header,
    icon,
    headerAction,
    children,
}: PropsWithChildren<{
    breadcrumbs?: { name: string; href?: string; icon?: string }[];
    header?: string;
    icon?: string;
    headerAction?: React.ReactNode;
}>) {
    const auth = usePage().props.auth;
    const alert = usePage<any>().props.alert;
    usePage<any>().props.alert = null; // clear the alert after it's been handled
    // const notifications = usePage().props.notifications;
    const teams = usePage().props.teams;

    const { open } = useSidebar();

    const [createTeamOpen, setCreateTeamOpen] = useState<boolean>(false);

    useEffect(() => {
        // @ts-ignore
        alert?.message ? toast(alert.message, { type: alert.status ?? 'info' }) : null;
    }, [alert]);

    if (!breadcrumbs && header) {
        breadcrumbs = [{ name: header }];
        if (icon !== undefined) {
            breadcrumbs[0].icon = icon;
        }
    }

    return (
        <SidebarWrapper>
            <Sidebar expanded={open} auth={auth} />
            <div className="flex flex-col w-full">
                {(header || breadcrumbs) && (
                    <div className="bg-background w-full sticky top-0 z-50 flex flex-row justify-between items-center pt-6 px-12">
                        <div className="">
                            <SidebarTrigger />
                        </div>
                        {!route().current('teams.*') && !route().current('profile.*') && <TeamSelect teams={teams} />}
                    </div>
                )}

                <header className="bg-background w-full px-12 pt-6 flex justify-between">
                    {breadcrumbs && (
                        <Breadcrumb className="inline-block">
                            <BreadcrumbList>
                                {breadcrumbs.map((breadcrumb: any, index: number) => (
                                    <Fragment key={breadcrumb.name}>
                                        <BreadcrumbItem className="text-3xl leading-tight text-gray-800 dark:text-gray-200">
                                            {breadcrumb.href && (
                                                <BreadcrumbLink asChild>
                                                    <>
                                                        {breadcrumb.icon && (
                                                            <Icon
                                                                name={breadcrumb.icon}
                                                                className={cn('h-8 w-8 inline-block', {})}
                                                            />
                                                        )}
                                                        <Link href={breadcrumb.href}>{breadcrumb.name}</Link>
                                                    </>
                                                </BreadcrumbLink>
                                            )}
                                            {!breadcrumb.href && (
                                                <BreadcrumbPage
                                                    className={cn('flex flex-row items-center gap-2', {
                                                        'font-semibold': breadcrumbs.length - 1 === index,
                                                    })}
                                                >
                                                    {breadcrumb.icon && (
                                                        <Icon
                                                            name={breadcrumb.icon}
                                                            className={cn('h-8 w-8 inline-block', {})}
                                                        />
                                                    )}
                                                    {breadcrumb.name}
                                                </BreadcrumbPage>
                                            )}
                                        </BreadcrumbItem>
                                        {breadcrumbs.length - 1 !== index && <BreadcrumbSeparator />}
                                    </Fragment>
                                ))}
                            </BreadcrumbList>
                        </Breadcrumb>
                    )}
                    {!breadcrumbs && (
                        <h1 className="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            {header}
                        </h1>
                    )}
                    {headerAction}
                </header>
                <main className="w-full min-h-screen block bg-background px-12 pb-6">{children}</main>
                <Footer />
            </div>
            <Dialog open={createTeamOpen} onOpenChange={setCreateTeamOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create a new team</DialogTitle>
                    </DialogHeader>
                    <Label htmlFor="name" aria-hidden aria-required>
                        Name
                    </Label>
                    <Input id="name" type="text" placeholder="Name…" />
                    <Label aria-required>Color</Label>
                    <ColorPicker color={null} onColorChange={() => {}} />
                    <IconPicker icon="" onIconSelect={() => {}} errors={{}} />
                    <DialogFooter>
                        <Button type="submit" className="w-full">
                            Create team
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {alert && <Toaster richColors closeButton />}
        </SidebarWrapper>
    );
}
