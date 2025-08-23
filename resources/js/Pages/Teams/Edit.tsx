import { Team, UserCollection } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
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
import React, { FormEvent, useState } from 'react';

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
    // @ts-ignore
    const { data, setData, errors, patch, processing } = useForm<Team>({
        id: team.id,
        color: team.color,
        icon: team.icon,
        members: undefined,
        name: team.name,
        organization: undefined,
    });

    const [tableOptions] = useState<TableOptions>({
        page,
        perPage,
        filters: Object.fromEntries(Object.entries(filters).map(([key, value]) => [key, new Set(value)])),
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch(route('teams.update', { team: team.id as string }));
    };

    const handleDelete = (user: { id?: number; email?: string }) => {
        router.delete(route('team-members.delete', { team: team.id as string }), {
            data: user,
        });
    };

    const handleCancel = () => {
        router.get(route('teams.index'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Settings', href: route('settings.index'), icon: 'Settings' },
                { name: 'Teams', href: route('teams.index'), icon: 'Users' },
                { name: team.name as string },
            ]}
        >
            <Head title="Edit Team" />

            <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div className="overflow-hidden p-4">
                    <div className="flex flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
                        <div className="w-full md:w-1/3">
                            <div className="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
                                <div className="flex flex-col p-4">
                                    <h3 className="mb-4 text-xl font-semibold">Team Details</h3>
                                    <form onSubmit={submit} className="flex flex-col space-y-4">
                                        <div>
                                            <Label htmlFor="name">Team Name</Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                value={data.name as string}
                                                onChange={(e) => {
                                                    // @ts-ignore
                                                    setData('name', e.target.value)
                                                }}
                                                data-dusk="input-teams-name"
                                            />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div>
                                            <Label htmlFor="color">Team Color</Label>
                                            <ColorPicker
                                                onColorChange={(color: string | null) => {
                                                    // @ts-ignore
                                                    setData('color', color)
                                                }}
                                                color={data.color as string}
                                            />
                                            <InputError message={errors.color} />
                                        </div>
                                        <div>
                                            <Label htmlFor="icon">Team Icon</Label>
                                            <IconPicker
                                                icon={data.icon as string}
                                                onIconSelect={(icon) => {
                                                    // @ts-ignore
                                                    setData('icon', icon)
                                                }}
                                                errors={errors}
                                            />
                                            <InputError message={errors.icon} />
                                        </div>
                                        <div className="flex justify-end">
                                            <Button
                                                variant="link"
                                                className="mr-2"
                                                type="button"
                                                onClick={handleCancel}
                                            >
                                                Cancel
                                            </Button>
                                            <Button
                                                type="submit"
                                                className="w-24"
                                                disabled={processing}
                                                data-dusk="button-teams-submit"
                                            >
                                                Update
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div className="w-full md:w-2/3">
                            <div className="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
                                <div className="flex flex-col p-4">
                                    <h3 className="mb-4 text-xl font-semibold">Team Members</h3>
                                    <div className="mb-4">
                                        <AddMembers team={team} users={users} />
                                    </div>
                                    <Table members={members} onDelete={handleDelete} tableOptions={tableOptions} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
