import { Role, Team, User, UserCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Table from '@/Pages/Teams/Components/Table';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import { MinusCircle, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

const AddMembers = ({ team }: { team: Team }) => {
    const [open, setOpen] = useState(false);
    const [currentEmail, setCurrentEmail] = useState<string>('');
    const [currentRole, setCurrentRole] = useState<string>('');
    const { data, setData, post, transform } = useForm<{ users: { email: string; role: Role }[] }>({ users: [] });

    transform((data) => {
        console.log('transform');
        console.log(data);
        console.log(currentEmail);
        console.log(currentRole);

        if (currentEmail.includes('@') && currentRole !== '') {
            console.log('modified data');
            return { users: [...data.users, { email: currentEmail, role: currentRole as Role }] };
        }

        console.log('unmodified data');
        return data;
    });

    const handleSubmit = () => {
        post(route('teams.invite', { slug: team.slug }), {
            onFinish: () => {
                setOpen(false);
                setData('users', []);
            },
        });

        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button type="submit">
                    <PlusCircle /> Add Members
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add Members to {team.name}</DialogTitle>
                </DialogHeader>
                {data.users.map((user, index) => (
                    <div key={user.email} className="flex flex-row gap-4 items-center">
                        <Label htmlFor={`add_member_${user.email}`} hidden>
                            Email
                        </Label>
                        <Input
                            id="add_members"
                            value={user.email}
                            onChange={(e) =>
                                setData(
                                    'users',
                                    data.users.map(function (user, idx) {
                                        if (idx !== index) {
                                            return user;
                                        }

                                        return {
                                            ...user,
                                            email: e.target.value,
                                        };
                                    }),
                                )
                            }
                        />
                        <Select
                            value={user.role}
                            onValueChange={(value: string) =>
                                setData(
                                    'users',
                                    data.users.map(function (user, idx) {
                                        if (idx !== index) {
                                            return user;
                                        }

                                        return {
                                            ...user,
                                            role: value as Role,
                                        };
                                    }),
                                )
                            }
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Role</SelectLabel>
                                    {Object.values(Role).map((role) => (
                                        <SelectItem key={role} value={role}>
                                            {role}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <MinusCircle
                            size="40"
                            onClick={() =>
                                setData(
                                    'users',
                                    data.users.filter((currentUser) => user.email !== currentUser.email),
                                )
                            }
                        />
                    </div>
                ))}
                <div className="flex flew-row gap-2 items-center">
                    <Label htmlFor="add_members" hidden>
                        Email
                    </Label>
                    <Input id="add_members" value={currentEmail} onChange={(e) => setCurrentEmail(e.target.value)} />
                    <Select value={currentRole} onValueChange={setCurrentRole}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Role</SelectLabel>
                                {Object.values(Role).map((role) => (
                                    <SelectItem key={role} value={role}>
                                        {role}
                                    </SelectItem>
                                ))}
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <PlusCircle
                        className={cn('cursor-pointer', {
                            'cursor-not-allowed text-primary/30': !currentEmail.includes('@') || currentRole === '',
                        })}
                        size="40"
                        onClick={function () {
                            if (!currentEmail.includes('@') || currentRole === '') {
                                return;
                            }

                            setData('users', [...data.users, { email: currentEmail, role: currentRole as Role }]);

                            setCurrentEmail('');
                            setCurrentRole('');
                        }}
                    />
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={handleSubmit}>
                        Add
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

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
            headerAction={<AddMembers team={team} />}
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
