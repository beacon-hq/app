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
    return (
        <Guest>
            <Head title="Choose a Team" />

            <div className="py-6">
                <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                    <div className="prose px-6 lg:px-0">
                        <h1 className="text-center">Choose a Team</h1>
                        <Card className="w-96    p-4">
                            <CardContent className="pb-0">
                                {teams.map((team, index) => (
                                    <div className="cursor-pointer" onClick={() => chooseTeam(team)} key={team.id}>
                                        <div className="flex flex-row gap-4 items-center h-20 justify-between">
                                            <div className="flex flex-row gap-4 items-center">
                                                <IconColor color={team.color} icon={team.icon} />
                                                {team.name}
                                            </div>
                                            <ChevronRight />
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
