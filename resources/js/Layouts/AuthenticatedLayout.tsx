import ApplicationLogo from '@/Components/ApplicationLogo';
import Footer from '@/Components/Footer';
import Icon from '@/Components/Icon';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
    SidebarWrapper,
    useSidebar,
} from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/sonner';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import {
    AppWindowMac,
    BadgeCheck,
    Bell,
    Component,
    Flag,
    Gauge,
    Globe,
    LogOut,
    PlusCircle,
    Settings,
    SlidersHorizontal,
    Sparkles,
    Tag,
    Users,
} from 'lucide-react';
import React, { Fragment, PropsWithChildren, useEffect } from 'react';
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
    const user = usePage().props.auth.user;
    const alert = usePage<any>().props.alert;
    usePage<any>().props.alert = null; // clear the alert after it's been handled
    // const notifications = usePage().props.notifications;

    const { state, open, setOpen, openMobile, setOpenMobile, isMobile, toggleSidebar } = useSidebar();

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
            <Sidebar collapsible="icon" variant="inset">
                <SidebarHeader>
                    <SidebarMenu>
                        <SidebarMenuItem className="data-[state=open]:flex data-[state=open]:flex-row data-[state=open]:justify-between items-center">
                            <Link href={route('dashboard')}>
                                <ApplicationLogo
                                    className="fill-current h-12 w-auto text-gray-800 dark:text-gray-200"
                                    expanded={open}
                                />
                            </Link>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarHeader>
                <SidebarContent>
                    <SidebarGroup>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton asChild tooltip="Dashboard" isActive={route().current('dashboard')}>
                                    <Link href={route('dashboard')}>
                                        <Gauge className="h-6 w-6" />
                                        <span>Dashboard</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip="Applications"
                                    isActive={route().current('applications.*')}
                                >
                                    <Link href={route('applications.index')}>
                                        <AppWindowMac className="h-6 w-6" />
                                        <span>Applications</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip="Environments"
                                    isActive={route().current('environments.*')}
                                >
                                    <Link href={route('environments.index')}>
                                        <Globe className="h-6 w-6" />
                                        <span>Environments</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip="Feature Flags"
                                    isActive={route().current('feature-flags.*')}
                                >
                                    <Link href={route('feature-flags.index')}>
                                        <Flag className="h-6 w-6" />
                                        <span>Feature Flags</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip="Feature Types"
                                    isActive={route().current('feature-types.*')}
                                >
                                    <Link href={route('feature-types.index')}>
                                        <Component className="h-6 w-6" />
                                        <span>Feature Types</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton asChild tooltip="Policies" isActive={route().current('policies.*')}>
                                    <Link href={route('policies.index')}>
                                        <SlidersHorizontal className="h-6 w-6" />
                                        <span>Policies</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton asChild tooltip="Tags" isActive={route().current('tags.*')}>
                                    <Link href={route('tags.index')}>
                                        <Tag className="h-6 w-6" />
                                        <span>Tags</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton asChild tooltip="Settings" isActive={route().current('settings.*')}>
                                    <Link href={route('settings.index')}>
                                        <Settings className="h-6 w-6" />
                                        <span>Settings</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroup>
                </SidebarContent>
                <SidebarFooter className="p-0">
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <SidebarMenuButton
                                        size="lg"
                                        className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                    >
                                        <Avatar className="h-10 w-10 rounded-full shadow-md">
                                            <AvatarImage
                                                src={user.avatar}
                                                alt={`${user.first_name} ${user.last_name}`}
                                            />
                                            <AvatarFallback>
                                                ${user.first_name.charAt(1)} ${user.last_name.charAt(1)}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="grid grow text-left text-sm leading-tight">
                                            <span className="truncate font-semibold">{`${user.first_name} ${user.last_name}`}</span>
                                            <span className="truncate text-xs">{user.email}</span>
                                        </div>
                                    </SidebarMenuButton>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    className="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                    side="bottom"
                                    align="start"
                                    sideOffset={4}
                                >
                                    <DropdownMenuGroup>
                                        <DropdownMenuLabel asChild>
                                            <div className="grid flex-1 text-left text-sm leading-tight">
                                                <span className="truncate font-semibold">{`${user.first_name} ${user.last_name}`}</span>
                                                <span className="truncate text-xs">{user.email}</span>
                                            </div>
                                        </DropdownMenuLabel>
                                        <DropdownMenuItem asChild>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        type="button"
                                                        className="w-full bg-blue-300 text-foreground hover:bg-blue-200 text-left"
                                                    >
                                                        <Sparkles />
                                                        Invite a team member…
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Invite team members</DialogTitle>
                                                    </DialogHeader>
                                                    <Label className="email hidden">E-mail</Label>
                                                    <div className="flex flex-row items-center gap-2">
                                                        <Input
                                                            type="email"
                                                            placeholder="E-mail address…"
                                                            className="input"
                                                        />
                                                        <PlusCircle className="w-6 h-6" />
                                                    </div>
                                                    <DialogFooter>
                                                        <Button type="submit" className="w-full">
                                                            Send invitation
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link href={route('profile.edit')} className="w-full">
                                                <BadgeCheck />
                                                Account
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link href="#" className="w-full">
                                                <Users />
                                                Team
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <Bell />
                                            Notifications
                                        </DropdownMenuItem>
                                    </DropdownMenuGroup>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link method="post" href={route('logout')} className="w-full">
                                            <LogOut />
                                            Log out
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarFooter>
            </Sidebar>
            <div className="flex flex-col w-full">
                {(header || breadcrumbs) && (
                    <div className="bg-background w-full sticky top-0 z-50 flex flex-row justify-between items-center">
                        <div className="pt-7 ml-12">
                            <SidebarTrigger />
                        </div>
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

            {alert && <Toaster richColors closeButton />}
        </SidebarWrapper>
    );
}
