import ApplicationLogo from '@/Components/ApplicationLogo';
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
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
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
    SidebarMenuSub,
    SidebarProvider,
} from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/sonner';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import {
    AppWindowMac,
    BadgeCheck,
    Bell,
    ChevronsUpDown,
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
import { Fragment, PropsWithChildren, ReactNode, useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Authenticated({
    breadcrumbs,
    header,
    children,
}: PropsWithChildren<{ breadcrumbs?: { name: string; href?: string }[]; header?: string }>) {
    const user = usePage().props.auth.user;
    const alert = usePage<any>().props.alert;
    const notifications = usePage().props.notifications;

    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    useEffect(() => {
        // @ts-ignore
        alert?.message ? toast(alert.message, { type: alert.status ?? 'info' }) : null;
    }, [alert]);

    const isMobile = useIsMobile();

    return (
        <>
            <SidebarProvider defaultOpen={true}>
                <Sidebar collapsible="none" className="h-screen sticky top-0">
                    <SidebarHeader>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <Link href={route('dashboard')}>
                                    <ApplicationLogo className="fill-current w-11/12 mx-auto text-gray-800 dark:text-gray-200" />
                                </Link>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarHeader>
                    <SidebarContent>
                        <SidebarGroup>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link
                                            href={route('dashboard')}
                                            className={cn({
                                                'font-bold': route().current('dashboard'),
                                            })}
                                        >
                                            <Gauge className="h-6 w-6" />
                                            Dashboard
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link
                                            href={route('applications.index')}
                                            className={cn({
                                                'font-bold': route().current('applications.*'),
                                            })}
                                        >
                                            <AppWindowMac className="h-6 w-6" />
                                            Applications
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link
                                            href={route('environments.index')}
                                            className={cn({
                                                'font-bold': route().current('environments.*'),
                                            })}
                                        >
                                            <Globe className="h-6 w-6" />
                                            Environments
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton>
                                        <Link
                                            href={route('feature-flags.index')}
                                            className={cn({
                                                'font-bold': route().current('feature-flags.*'),
                                            })}
                                        >
                                            <Flag className="h-4 w-4 inline-block mr-2" />
                                            <span>Feature Flags</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link
                                            href={route('feature-types.index')}
                                            className={cn({
                                                'font-bold': route().current('feature-types.*'),
                                            })}
                                        >
                                            <Component className="h-6 w-6" />
                                            Feature Types
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link href={route('applications.index')}>
                                            <SlidersHorizontal className="h-6 w-6" />
                                            Policies
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link
                                            href={route('tags.index')}
                                            className={cn({
                                                'font-bold': route().current('tags.*'),
                                            })}
                                        >
                                            <Tag className="h-6 w-6" />
                                            Tags
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                                <SidebarMenuItem>
                                    <SidebarMenuButton asChild>
                                        <Link href={route('applications.index')}>
                                            <Settings className="h-6 w-6" />
                                            Settings
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroup>
                    </SidebarContent>
                    <SidebarFooter>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <SidebarMenuButton
                                            size="lg"
                                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                        >
                                            <Avatar className="h-8 w-8 rounded-lg">
                                                <AvatarImage
                                                    src={user.avatar}
                                                    alt={`${user.first_name} ${user.last_name}`}
                                                />
                                                <AvatarFallback className="rounded-lg">CN</AvatarFallback>
                                            </Avatar>
                                            <div className="grid flex-1 text-left text-sm leading-tight">
                                                <span className="truncate font-semibold">{`${user.first_name} ${user.last_name}`}</span>
                                                <span className="truncate text-xs">{user.email}</span>
                                            </div>
                                            <ChevronsUpDown className="ml-auto size-4" />
                                        </SidebarMenuButton>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        className="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                        side="bottom"
                                        align="start"
                                        sideOffset={4}
                                    >
                                        <DropdownMenuGroup>
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
                        <header className="bg-background shadow w-full block sticky top-0 z-50">
                            <div className="mx-auto px-4 py-6 sm:px-6 lg:px-8">
                                {breadcrumbs && (
                                    <Breadcrumb className="inline-block">
                                        <BreadcrumbList>
                                            {breadcrumbs.map((breadcrumb: any, index: number) => (
                                                <Fragment key={breadcrumb.name}>
                                                    <BreadcrumbItem className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                                                        {breadcrumb.href && (
                                                            <BreadcrumbLink asChild>
                                                                <Link href={breadcrumb.href}>{breadcrumb.name}</Link>
                                                            </BreadcrumbLink>
                                                        )}
                                                        {!breadcrumb.href && (
                                                            <BreadcrumbPage>{breadcrumb.name}</BreadcrumbPage>
                                                        )}
                                                    </BreadcrumbItem>
                                                    {breadcrumbs.length - 1 !== index && <BreadcrumbSeparator />}
                                                </Fragment>
                                            ))}
                                        </BreadcrumbList>
                                    </Breadcrumb>
                                )}
                                {!breadcrumbs && (
                                    <h1 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                                        {header}
                                    </h1>
                                )}
                            </div>
                        </header>
                    )}

                    <main className="w-full block bg-background">{children}</main>
                </div>
            </SidebarProvider>

            {alert && <Toaster richColors closeButton />}
        </>
    );
}
