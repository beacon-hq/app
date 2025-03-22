import { IconColor } from '@/Components/IconColor';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import Guest from '@/Layouts/GuestLayout';
import { chooseTeam } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import React from 'react';

export default function Select({ teams }: PageProps) {
    const uniqueOrganizationCount = teams.reduce((acc, team) => {
        if (team.organization?.id && !acc.includes(team.organization.id)) {
            acc.push(team.organization.id);
        }
        return acc;
    }, [] as string[]).length;
    return (
        <Guest>
            <Head title="Choose a Team" />

            <div className="py-6">
                <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                    <div className="prose px-6 lg:px-0">
                        <h1 className="text-center">Choose a Team</h1>
                        <Card className="w-96 p-4">
                            <CardContent className="pb-0">
                                {teams.map((team, index) => (
                                    <div className="cursor-pointer" onClick={() => chooseTeam(team)} key={team.id}>
                                        <div className="flex flex-row gap-4 items-center h-20 justify-start">
                                            <div className="flex flex-row gap-4 items-center">
                                                {uniqueOrganizationCount > 1 && (
                                                    <>
                                                        {team.organization?.name}
                                                        <ChevronRight />
                                                    </>
                                                )}
                                                <IconColor color={team.color} icon={team.icon} /> {team.name}
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
