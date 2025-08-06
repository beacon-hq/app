import { Team, TeamCollection } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { chooseTeam } from '@/lib/utils';
import { Auth } from '@/types';
import { usePage } from '@inertiajs/react';
import { Check, ChevronRight, ChevronsUpDown } from 'lucide-react';
import React, { useState } from 'react';

const TeamSelect = ({ teams }: { teams: TeamCollection }) => {
    const auth = usePage().props.auth as Auth;
    const [selectedTeam] = useState<Team>(auth.currentTeam);

    const uniqueOrganizationCount = teams.reduce((acc, team) => {
        if (team.organization?.id && !acc.includes(team.organization.id)) {
            acc.push(team.organization.id);
        }
        return acc;
    }, [] as string[]).length;

    if (teams.length === 1) {
        return (
            <Button type="button" variant="secondary" className="flex flex-row items-center gap-1">
                <div className="flex aspect-square size-8 items-center justify-center rounded-lg">
                    <IconColor color={auth.currentTeam.color} icon={auth.currentTeam.icon} />
                </div>
                <div className="flex flex-col gap-0.5 leading-none">
                    <span className="">{auth.currentTeam.name}</span>
                </div>
            </Button>
        );
    }

    if (teams.length > 1) {
        return (
            <div data-dusk="team-select">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            type="button"
                            variant="secondary"
                            className="flex flex-row items-center gap-0.5"
                            data-dusk="select-team"
                        >
                            {uniqueOrganizationCount > 1 && (
                                <>
                                    {auth.currentTeam.organization?.name}
                                    <ChevronRight />
                                </>
                            )}
                            <div className="flex aspect-square size-8 items-center justify-center rounded-lg">
                                <IconColor color={auth.currentTeam.color} icon={auth.currentTeam.icon} />
                            </div>
                            <div className="flex flex-col gap-0.5 leading-none">{auth.currentTeam.name}</div>
                            <ChevronsUpDown className="ml-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-[--radix-dropdown-menu-trigger-width]" align="start">
                        {teams
                            .filter((team) => team.id != selectedTeam.id)
                            .map((team, idx) => (
                                <DropdownMenuItem
                                    key={team.id}
                                    onSelect={() => chooseTeam(team)}
                                    data-dusk={`select-option-team-${idx}`}
                                >
                                    {uniqueOrganizationCount > 1 && (
                                        <>
                                            {team.organization?.name}
                                            <ChevronRight />
                                        </>
                                    )}
                                    <div className="flex aspect-square size-8 items-center justify-center rounded-lg">
                                        <IconColor color={team.color} icon={team.icon} />
                                    </div>
                                    <div className="flex flex-col gap-0.5 leading-none">{team.name}</div>
                                    {team.id === selectedTeam.id && <Check className="ml-auto" />}
                                </DropdownMenuItem>
                            ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        );
    }
};

export default TeamSelect;
