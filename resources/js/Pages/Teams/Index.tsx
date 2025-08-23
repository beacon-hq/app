import { Team, TeamCollection, User } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import UserAvatar from '@/Components/UserAvatar';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Teams/Components/Form';
import { Head, Link, useForm } from '@inertiajs/react';
import { ChevronRight, ChevronsRight, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

export default function Index({ teams }: { teams: TeamCollection }) {
    const [showSheet, setShowSheet] = useState(false);

    const uniqueOrganizationCount = teams.reduce((acc, team) => {
        if (team.organization?.id && !acc.includes(team.organization.id)) {
            acc.push(team.organization.id);
        }
        return acc;
    }, [] as string[]).length;

    // @ts-ignore
    const { data, setData, errors, post, processing, reset } = useForm<Team>({
        id: undefined,
        organization: undefined,
        name: '',
        color: '',
        icon: '',
        members: undefined,
    });

    const handleSubmit = () => {
        post(route('teams.store'));
    };

    const handleCancel = () => {
        setShowSheet(false);
        reset();
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Settings', icon: 'Settings', href: route('settings.index') },
                { name: 'Teams', icon: 'Users' },
            ]}
            headerAction={
                <Button onClick={() => setShowSheet(true)} data-dusk="button-teams-create">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Team
                </Button>
            }
        >
            <Head title="Index" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
                        <Card data-dusk="card-teams">
                            <CardContent className="px-4 pb-0">
                                {teams.map((team, index) => (
                                    <div
                                        className="cursor-pointer"
                                        key={team.id}
                                        data-dusk={`card-teams-team-${index}`}
                                    >
                                        <div className="relative flex flex-col gap-4 items-start h-fit p-4">
                                            <div className="w-full flex flex-row gap-4 items-center">
                                                {uniqueOrganizationCount > 1 && (
                                                    <>
                                                        {team.organization?.name}
                                                        <ChevronsRight />
                                                    </>
                                                )}
                                                <div className="flex flex-row justify-between grow">
                                                    <Link
                                                        href={route('teams.edit', { team: team.id as string })}
                                                        className="flex flex-row gap-2"
                                                    >
                                                        <IconColor color={team.color} icon={team.icon} />
                                                        <span className="absolute inset-0"></span>
                                                        {team.name}
                                                    </Link>
                                                    <ChevronRight className="relative top-5" />
                                                </div>
                                            </div>
                                            <div className="flex flex-row grow justify-end overflow-hidden">
                                                {team.members?.map((user: User) => (
                                                    <Tooltip>
                                                        <TooltipTrigger>
                                                            <UserAvatar user={user} key={user.id} className="w-6 h-6" />
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            {user.first_name} {user.last_name}
                                                        </TooltipContent>
                                                    </Tooltip>
                                                ))}
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
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()}>
                    <SheetTitle className="mb-4">New Team</SheetTitle>
                    <Form
                        submit={handleSubmit}
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onCancel={handleCancel}
                    />
                </SheetContent>
            </Sheet>
        </AuthenticatedLayout>
    );
}
