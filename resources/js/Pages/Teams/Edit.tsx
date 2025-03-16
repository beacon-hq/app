import { Role, Team, User, UserCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Table from '@/Pages/Teams/Components/Table';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';

export default function Edit({ team }: { team: Team }) {
    const owners = team.members?.filter((member: User) => member.roles.includes(Role.OWNER) ?? []) as UserCollection;
    const admins = team.members?.filter((member: User) => member.roles.includes(Role.ADMIN) ?? []) as UserCollection;
    const developers = team.members?.filter(
        (member: User) => member.roles.includes(Role.DEVELOPER) ?? [],
    ) as UserCollection;
    const billers = team.members?.filter((member: User) => member.roles.includes(Role.BILLER) ?? []) as UserCollection;

    const { data, setData } = useForm<Team>({
        color: team.color,
        icon: team.icon,
        id: team.id,
        members: team.members,
        name: team.name,
        slug: team.slug,
    });

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Teams', href: route('teams.index') },
                { name: data.name as string, icon: data.icon ?? undefined },
            ]}
        >
            <Head title="Edit" />
            <div className="mt-12">
                <section className="flex flew-row gap-6 mb-12 justify-items-start">
                    <Label htmlFor="team_name" className="text-xl text-nowrap w-1/4">
                        Team Name
                    </Label>
                    <Input
                        id="team_name"
                        className="w-1/3"
                        value={data.name as string}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <Button type="button">Save</Button>
                </section>
                {owners.length > 0 && (
                    <section className={'flex flex-row gap-8'}>
                        <header className="w-1/4">
                            <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Owners</h2>

                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Owners have full access to manage billing, feature flag configuration, and team
                                settings.
                            </p>
                        </header>

                        <div className="w-3/4 grow">
                            <Table users={owners} />
                        </div>
                    </section>
                )}
                {admins.length > 0 && (
                    <section className="flex flex-row gap-8">
                        <header className="w-1/4">
                            <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Admins</h2>

                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Admins have full access to feature flag configuration, and team settings.
                            </p>
                        </header>

                        <div className="w-3/4 grow">
                            <Table users={admins} />
                        </div>
                    </section>
                )}
                {developers.length > 0 && (
                    <section className="flex flex-row gap-8 mt-6">
                        <header className="w-1/4">
                            <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Developers</h2>

                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Developers only have access to feature flag configuration.
                            </p>
                        </header>

                        <div className="w-3/4 grow">
                            <Table users={developers} />
                        </div>
                    </section>
                )}
                {billers.length > 0 && (
                    <section className="flex flex-row gap-8 mt-6">
                        <header className="w-1/4">
                            <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Billing Admins</h2>

                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Billing Admins only have access to billing.
                            </p>
                        </header>

                        <div className="w-3/4 grow">
                            <Table users={billers} />
                        </div>
                    </section>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
