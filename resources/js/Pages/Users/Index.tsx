import AddUser from './Components/AddUser';
import ManageUser from './Components/ManageUser';
import Table from './Components/Table';
import { Team, User, UserCollection } from '@/Application';
import { TableOptions } from '@/Components/ui/data-table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';
import { Head, router } from '@inertiajs/react';
import { UserCircle } from 'lucide-react';
import { useState } from 'react';

export default function Index({
    users,
    teams,
    filters,
    page,
    perPage,
}: {
    users: UserCollection;
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

        router.delete(route('users.destroy', { id: user.id }));
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
                <Table
                    users={users}
                    teams={teams}
                    tableOptions={tableOptions}
                    onDelete={handleDelete}
                    onManage={handleManage}
                />

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
