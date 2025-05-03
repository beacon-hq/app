import { Invite, TeamCollection } from '@/Application';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { DataTable, TableOptions } from '@/Components/ui/data-table';
import AddUser from '@/Pages/Users/Components/AddUser';
import { router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { formatDistanceToNow } from 'date-fns';
import { Send, Trash2 } from 'lucide-react';
import { useMemo } from 'react';

interface InvitesTableProps {
    invites: Invite[];
    teams: TeamCollection;
    tableOptions?: TableOptions;
}

const InvitesTable = ({ invites, tableOptions, teams }: InvitesTableProps) => {
    const handleDelete = (invite: Invite) => {
        if (!invite.id) return;
        router.delete(route('invites.destroy', { invite: invite.id }));
    };

    const handleResend = (invite: Invite) => {
        if (!invite.id) return;
        router.post(route('invites.resend', { invite: invite.id }));
    };

    const columns = useMemo<ColumnDef<Invite>[]>(
        () => [
            {
                id: 'avatar',
                header: '',
                cell: ({ row }) => {
                    const invite = row.original as any;
                    return (
                        <Avatar className="h-8 w-8">
                            <AvatarImage src={invite.avatar} alt={invite.email} />
                            <AvatarFallback>{invite.email.charAt(0).toUpperCase()}</AvatarFallback>
                        </Avatar>
                    );
                },
                enableSorting: false,
            },
            {
                accessorKey: 'email',
                header: 'Email',
            },
            {
                id: 'team',
                header: 'Team',
            },
            {
                accessorKey: 'role',
                header: 'Role',
            },
            {
                id: 'invited_by',
                header: 'Invited By',
                cell: ({ row }) => {
                    const invite = row.original as any;
                    return (
                        <div className="flex items-center gap-2">
                            <Avatar className="h-6 w-6">
                                <AvatarImage
                                    src={invite.user.gravatar}
                                    alt={`${invite.user.first_name} ${invite.user.last_name}`}
                                />
                                <AvatarFallback>{invite.user.first_name?.charAt(0) || ''}</AvatarFallback>
                            </Avatar>
                            <span>
                                {invite.user.first_name} {invite.user.last_name}
                            </span>
                        </div>
                    );
                },
            },
            {
                id: 'expires_at',
                header: 'Expires',
                cell: ({ row }) => formatDistanceToNow(new Date((row as any).original.expires_at), { addSuffix: true }),
            },
            {
                id: 'actions',
                header: 'Actions',
                cell: ({ row }) => {
                    const invite = row.original;
                    return (
                        <div className="flex flex-row gap-2 justify-end">
                            <Send
                                className="h-6 w-6 cursor-pointer text-primary"
                                onClick={() => handleResend(invite)}
                            />
                            <AlertDialog>
                                <AlertDialogTrigger>
                                    <Trash2 className="h-6 w-6 cursor-pointer text-destructive" />
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Delete Invitation</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Are you sure you want to delete the invitation for{' '}
                                            <strong>{invite.email}</strong>? This action cannot be undone.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction onClick={() => handleDelete(invite)}>
                                            Delete
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    );
                },
            },
        ],
        [],
    );

    if (invites.length === 0) {
        return (
            <div className="text-center text-muted-foreground py-6 flex flex-col w-fit mx-auto gap-4">
                No pending invites found.
                <AddUser teams={teams} />
            </div>
        );
    }

    return (
        <DataTable
            columns={columns}
            data={invites}
            filter={{
                column: 'email',
                placeholder: 'Filter by email...',
            }}
            tableOptions={tableOptions}
        />
    );
};

export default InvitesTable;
