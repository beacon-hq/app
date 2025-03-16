import ApplicationLogo from './ApplicationLogo.js';
import UserAvatar from './UserAvatar.js';
import { Permission } from '@/Application';
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
    Sidebar as SidebarContainer,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/Components/ui/sidebar';
import { Gate } from '@/lib/permissions';
import { cn } from '@/lib/utils';
import { AuthProp } from '@/types';
import { Link } from '@inertiajs/react';
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
import React from 'react';

const Sidebar = function (props: { expanded: boolean } & AuthProp) {
    return (
        <SidebarContainer collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem className="data-[state=open]:flex data-[state=open]:flex-row data-[state=open]:justify-between items-center">
                        <Link href={route('dashboard')}>
                            <ApplicationLogo
                                className="fill-current h-12 w-auto text-gray-800 dark:text-gray-200"
                                expanded={props.expanded}
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
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.APPLICATIONS_VIEW) })}>
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
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.ENVIRONMENTS_VIEW) })}>
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
                        <SidebarMenuItem
                            className={cn({
                                hidden:
                                    !Gate.can(Permission.FEATURE_FLAGS_VIEW) &&
                                    !Gate.can(Permission.FEATURE_FLAG_STATUS_VIEW),
                            })}
                        >
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
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.FEATURE_TYPES_VIEW) })}>
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
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.POLICIES_VIEW) })}>
                            <SidebarMenuButton asChild tooltip="Policies" isActive={route().current('policies.*')}>
                                <Link href={route('policies.index')}>
                                    <SlidersHorizontal className="h-6 w-6" />
                                    <span>Policies</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.TAGS_VIEW) })}>
                            <SidebarMenuButton asChild tooltip="Tags" isActive={route().current('tags.*')}>
                                <Link href={route('tags.index')}>
                                    <Tag className="h-6 w-6" />
                                    <span>Tags</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem className={cn({ hidden: !Gate.can(Permission.API_TOKENS_VIEW) })}>
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
                                    <UserAvatar user={props.auth.user} />
                                    <div className="grid grow text-left text-sm leading-tight">
                                        <span className="truncate font-semibold">{`${props.auth.user.first_name} ${props.auth.user.last_name}`}</span>
                                        <span className="truncate text-xs">{props.auth.user.email}</span>
                                    </div>
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                                side="bottom"
                                align="start"
                                sideOffset={4}
                            >
                                <DropdownMenuGroup>
                                    <DropdownMenuLabel asChild>
                                        <div className="grid flex-1 text-left text-sm leading-tight">
                                            <span className="truncate font-semibold">{`${props.auth.user.first_name} ${props.auth.user.last_name}`}</span>
                                            <span className="truncate text-xs">{props.auth.user.email}</span>
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
                                        <Link href={route('teams.index')} className="w-full">
                                            <Users />
                                            {Gate.can(Permission.TEAMS_UPDATE) ? 'Manage Teams' : 'View Teams'}
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
        </SidebarContainer>
    );
};

export default Sidebar;
