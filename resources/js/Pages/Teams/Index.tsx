import { TeamCollection, User } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import UserAvatar from '@/Components/UserAvatar';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

export default function Index({ teams }: { teams: TeamCollection }) {
    const [showSheet, setShowSheet] = useState(false);

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ name: 'Settings', icon: 'Settings' }, { name: 'Teams' }]}
            headerAction={
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Team
                </Button>
            }
        >
            <Head title="Index" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
                        <Card>
                            <CardContent className="px-4 pb-0">
                                {teams.map((team, index) => (
                                    <div className="cursor-pointer" key={team.id}>
                                        <div className="relative flex flex-row gap-4 items-center h-20 justify-between">
                                            <div className="flex flex-row gap-4 items-center">
                                                <IconColor color={team.color} icon={team.icon} />
                                                <Link href={route('teams.edit', { slug: team.slug })}>
                                                    <span className="absolute inset-0"></span>
                                                    {team.name}
                                                </Link>
                                            </div>
                                            <div className="flex flex-row grow justify-end overflow-hidden">
                                                {team.members?.map((user: User) => (
                                                    <UserAvatar user={user} key={user.id} className="w-6 h-6" />
                                                ))}
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
        </AuthenticatedLayout>
    );
}
