import { Permission } from '@/Application';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { ColorPicker } from '@/Components/ColorPicker';
import Footer from '@/Components/Footer';
import IconPicker from '@/Components/IconPicker';
import OrganizationSelect from '@/Components/OrganizationSelect';
import Sidebar from '@/Components/Sidebar';
import TeamSelect from '@/Components/TeamSelect';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { SidebarTrigger, SidebarWrapper, useSidebar } from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/sonner';
import { Gate } from '@/lib/permissions';
import { usePage } from '@inertiajs/react';
import React, { PropsWithChildren, useState } from 'react';
import { useAlertHandler } from '@/hooks/use-alert-handler';

export default function Authenticated({
    breadcrumbs,
    header,
    icon,
    headerAction,
    children,
}: PropsWithChildren<{
    breadcrumbs?: { name: string; href?: string; icon?: string | React.ReactNode }[];
    header?: string;
    icon?: string;
    headerAction?: React.ReactNode;
}>) {
    const auth = usePage().props.auth;
    // const notifications = usePage().props.notifications;
    const teams = usePage().props.teams;
    const organizations = usePage().props.organizations;

    useAlertHandler();

    const { open } = useSidebar();

    const [createTeamOpen, setCreateTeamOpen] = useState<boolean>(false);

    if (!breadcrumbs && header) {
        breadcrumbs = [{ name: header }];
        if (icon !== undefined) {
            breadcrumbs[0].icon = icon as string;
        }
    }

    return (
        <SidebarWrapper>
            <Sidebar expanded={open} auth={auth} />
            <div className="flex flex-col w-full">
                {(header || breadcrumbs) && (
                    <div className="bg-background w-full sticky top-0 z-50 flex flex-row justify-between items-center py-4 px-12">
                        <div className="">
                            <SidebarTrigger />
                        </div>
                        {!route().current('teams.*') &&
                            !route().current('profile.*') &&
                            !route().current('organizations.*') &&
                            !route().current('settings.*') &&
                            !route().current('users.*') &&
                            !Gate.canOnly([`${Permission.BILLING}.*`]) && <TeamSelect teams={teams} />}
                        {(route().current('teams.*') ||
                            route().current('users.*') ||
                            Gate.canOnly([`${Permission.BILLING}.*`])) && (
                            <OrganizationSelect organizations={organizations} />
                        )}
                    </div>
                )}

                <header className="bg-background w-full px-12 pt-4 flex justify-between">
                    {breadcrumbs && <Breadcrumbs breadcrumbs={breadcrumbs} />}
                    {!breadcrumbs && (
                        <h1 className="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            {header}
                        </h1>
                    )}
                    {headerAction}
                </header>
                <main className="w-full min-h-(--body-height) block bg-background px-12 pb-6">{children}</main>
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

            <Toaster richColors closeButton />
        </SidebarWrapper>
    );
}
