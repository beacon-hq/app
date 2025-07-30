import { IconColor } from '@/Components/IconColor';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import Guest from '@/Layouts/GuestLayout';
import { chooseTeam } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { ChevronRight, ChevronsRight } from 'lucide-react';
import React from 'react';

export default function Select({ teams }: PageProps) {
    const uniqueOrganizationCount = teams.reduce((acc, team) => {
        if (team.organization?.id && !acc.includes(team.organization.id)) {
            acc.push(team.organization.id);
        }
        return acc;
    }, [] as string[]).length;
    return (
        <Guest className="w-fit min-w-1/2 mx-auto">
            <Head title="Choose a Team" />

            <div className="py-6 w-full">
                <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                    <div className="px-6 lg:px-0">
                        <h1 className="text-center text-2xl font-bold mb-4">Choose a Team</h1>
                        <Card className="w-full p-4">
                            <CardContent className="pb-0">
                                {teams.map((team, index) => (
                                    <div className="cursor-pointer" onClick={() => chooseTeam(team)} key={team.id}>
                                        <div className="flex flex-row gap-4 items-center h-20 justify-start">
                                            <div className="flex flex-row w-full gap-4 items-center ">
                                                {uniqueOrganizationCount > 1 && (
                                                    <>
                                                        {team.organization?.name}
                                                        <ChevronsRight />
                                                    </>
                                                )}
                                                <div className="flex-1 flex flex-row items-center gap-2">
                                                    <IconColor
                                                        color={team.color}
                                                        icon={team.icon}
                                                        className="inline-block"
                                                    />{' '}
                                                    {team.name}
                                                </div>
                                                <div className="grow-0">
                                                    {uniqueOrganizationCount <= 1 && <ChevronRight />}
                                                </div>
                                            </div>
                                        </div>
                                        {teams.length - 1 !== index && <Separator />}
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Guest>
    );
}
