import { Organization, OrganizationCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { chooseOrganization } from '@/lib/utils';
import { Auth } from '@/types';
import { usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import React, { useState } from 'react';

const OrganizationSelect = ({ organizations }: { organizations: OrganizationCollection }) => {
    const auth = usePage().props.auth as Auth;
    const [selectedOrganization] = useState<Organization>(auth.currentTeam?.organization as Organization);

    if (organizations.length === 1) {
        return null;
    }

    const selectableOrganizations = organizations.filter(
        (organization: Organization) =>
            organization.id != selectedOrganization.id && organization.id != auth.currentTeam.organization?.id,
    );

    if (selectableOrganizations.length === 0) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button type="button" variant="secondary" className="flex flex-row items-center gap-0.5">
                    <div className="flex flex-col gap-0.5 leading-none">{auth.currentTeam.organization?.name}</div>
                    <ChevronsUpDown className="ml-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-[--radix-dropdown-menu-trigger-width]" align="start">
                {selectableOrganizations.map((organization: Organization) => (
                    <DropdownMenuItem key={organization.id} onSelect={() => chooseOrganization(organization)}>
                        <div className="flex flex-col gap-0.5 leading-none">{organization.name}</div>
                        {organization.id === selectedOrganization.id && <Check className="ml-auto" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

export default OrganizationSelect;
