import AddUser from './Components/AddUser';
import InvitesTable from './Components/InvitesTable';
import ManageUser from './Components/ManageUser';
import Table from './Components/Table';
import { InviteCollection, Team, User, UserCollection } from '@/Application';
import { TableOptions } from '@/Components/ui/data-table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';
import { Head, router } from '@inertiajs/react';
import { UserCircle } from 'lucide-react';
import { useState } from 'react';

export default function Index({
    users,
    invites,
    teams,
    filters,
    page,
    perPage,
}: {
    users: UserCollection;
    invites: InviteCollection;
    teams: Team[];
    filters: { [key: string]: string[] };
    page: number;
    perPage: number;
}) {
    const [tableOptions] = useState<TableOptions>({
        page,
        perPage,
        filters: Object.fromEntries(Object.entries(filters).map(([key, value]) => [key, new Set(value)])),
    });

    const [manageUserDialogOpen, setManageUserDialogOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState<User | null>(null);

    const handleDelete = (user: { id?: number; email?: string }) => {
        if (!user.id) return;

        router.delete(route('users.destroy', { user: user.id as number }));
    };

    const handleManage = (user: User) => {
        setSelectedUser(user);
        setManageUserDialogOpen(true);
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    icon: 'Settings',
                    href: route('settings.index'),
                    name: 'Settings',
                },
                {
                    icon: <UserCircle className={cn('h-8 w-8 inline-block')} />,
                    name: 'Users',
                },
            ]}
            headerAction={<AddUser teams={teams} />}
        >
            <Head title="Users" />

            <div className="space-y-6 mt-8">
                <div>
                    <h2 className="text-xl font-semibold mb-4">Users</h2>
                    <Table
                        users={users}
                        teams={teams}
                        tableOptions={tableOptions}
                        onDelete={handleDelete}
                        onManage={handleManage}
                    />
                </div>

                <div>
                    <h2 className="text-xl font-semibold mb-4">Pending Invites</h2>
                    <InvitesTable invites={invites} teams={teams} />
                </div>

                {selectedUser && (
                    <ManageUser
                        user={selectedUser}
                        teams={teams}
                        open={manageUserDialogOpen}
                        onOpenChange={setManageUserDialogOpen}
                    />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
