import { Team, UserCollection } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import { IconColor } from '@/Components/IconColor';
import IconPicker from '@/Components/IconPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { TableOptions } from '@/Components/ui/data-table';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddMembers from '@/Pages/Teams/Components/AddMembers';
import Table from '@/Pages/Teams/Components/Table';
import { Head, router, useForm } from '@inertiajs/react';
import React, { useState } from 'react';

export default function Edit({
    team,
    users,
    members,
    filters,
    page,
    perPage,
}: {
    team: Team;
    users: UserCollection;
    members: UserCollection;
    filters: { [key: string]: string[] };
    page: number;
    perPage: number;
}) {
    const { data, setData, errors, patch } = useForm<Team>({
        color: team.color,
        icon: team.icon,
        id: team.id,
        members: null, // members are managed elsewhere
        name: team.name,
        organization: null,
        slug: team.slug,
    });

    const [tableOptions] = useState<TableOptions>({
        page,
        perPage,
        filters: Object.fromEntries(Object.entries(filters).map(([key, value]) => [key, new Set(value)])),
    });

    const handleSubmit = () => {
        patch(route('teams.update', { slug: team.slug }));
    };

    const handleDelete = (user: { id?: number; email?: string }) => {
        router.delete(route('team-members.delete', { slug: team.slug }), {
            data: user,
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Teams', href: route('teams.index') },
                {
                    name: data.name as string,
                    icon: data.icon ? <IconColor color={data.color} icon={data.icon} className="w-8 h-8" /> : undefined,
                },
            ]}
            headerAction={<AddMembers team={team} users={users} />}
        >
            <Head title="Edit" />
            <div className="mt-12">
                <section className="flex flew-row gap-6 mb-12 justify-items-start">
                    <header className="w-1/4">
                        <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Team Detail</h2>

                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Change the basic details for your team.
                        </p>
                    </header>
                    <div className="w-3/4 grow">
                        <div className="flex flex-row justify-between items-center gap-4 mb-6">
                            <div className="grow">
                                <Label htmlFor="team_name">Team Name</Label>
                                <Input
                                    id="team_name"
                                    value={data.name as string}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                <InputError message={errors?.name} />
                            </div>
                            <div>
                                <IconPicker
                                    icon={data.icon as string}
                                    onIconSelect={(icon) => setData('icon', icon)}
                                    errors={errors}
                                />
                            </div>
                            <div>
                                <Label>Icon Color</Label>
                                <ColorPicker onColorChange={(color) => setData('color', color)} color={data.color} />
                                <InputError message={errors?.color} />
                            </div>
                        </div>
                        <Button type="button" onClick={handleSubmit}>
                            Save
                        </Button>
                    </div>
                </section>
                <section className="flex flex-row gap-8 mt-8">
                    <header className="w-1/4">
                        <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Team Members</h2>
                    </header>

                    <div className="w-3/4 grow">
                        <Table
                            members={members ?? []}
                            memberCount={members?.length ?? 0}
                            onDelete={handleDelete}
                            tableOptions={tableOptions}
                        />
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
