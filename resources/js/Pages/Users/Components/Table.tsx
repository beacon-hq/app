import { Role, Team, User, UserCollection, UserStatus } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import UserAvatar from '@/Components/UserAvatar';
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
import { Badge } from '@/Components/ui/badge';
import { DataTable, TableOptions } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { cn } from '@/lib/utils';
import { Auth } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { Send, Trash, UserCog } from 'lucide-react';
import React from 'react';

export default function Table({
    users,
    userCount,
    teams,
    onDelete,
    onManage,
    tableOptions,
}: {
    users: UserCollection;
    userCount?: number;
    teams: Team[];
    onDelete: (user: { id?: number; email?: string }) => void;
    onManage: (user: User) => void;
    tableOptions: TableOptions;
}) {
    const currentUser = (usePage().props.auth as Auth).user;

    const roleColors = {
        [Role.OWNER]: 'border-red-400 bg-red-200 hover:bg-red-300',
        [Role.ADMIN]: 'border-purple-400 bg-purple-200 hover:bg-purple-300',
        [Role.DEVELOPER]: 'border-green-400 bg-green-200 hover:bg-green-300',
        [Role.BILLER]: 'border-blue-400 bg-blue-200 hover:bg-blue-300',
    };

    const columnHelper = createColumnHelper<User>();

    const columns: ColumnDef<User, unknown>[] = [
        columnHelper.display({
            id: 'avatar',
            header: ({ column }) => <DataTableColumnHeader column={column} title="" />,
            enableSorting: false,
            enableHiding: false,
            cell: function ({ row }) {
                return <UserAvatar user={row.original} />;
            },
        }) as ColumnDef<User>,
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ cell }) => {
                const value = cell.getValue();
                return (
                    <div className="flex items-center gap-2">
                        <span>{value}</span>
                        {cell.row.original.id === currentUser.id && (
                            <Badge variant="outline" className="text-primary/50 rounded-full bg-yellow-100">
                                You
                            </Badge>
                        )}
                    </div>
                );
            },
        }) as ColumnDef<User>,
        columnHelper.accessor('email', {
            id: 'email',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Email" />,
            enableSorting: true,
            enableHiding: false,
        }) as ColumnDef<User>,
        columnHelper.accessor('teams', {
            id: 'teams',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Teams" />,
            enableSorting: false,
            enableHiding: false,
            cell: ({ cell }) => (
                <div className="flex flex-wrap gap-1">
                    {(cell.getValue() || []).map((team: Team) => (
                        <div key={team.id ?? ''} className="flex items-center gap-1 rounded-md bg-secondary px-2 py-1">
                            <IconColor icon={team.icon} color={team.color} className="h-4 w-4" />
                            <span className="text-sm">{team.name}</span>
                        </div>
                    ))}
                </div>
            ),
        }) as ColumnDef<User>,
        columnHelper.accessor('roles', {
            id: 'roles',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Roles" />,
            enableSorting: false,
            enableHiding: false,
            cell: ({ cell }) => (
                <div className="flex flex-wrap gap-1">
                    {(cell.getValue() || []).map((role: string) => (
                        <Badge className={cn(roleColors[role as Role], 'text-black')} key={role}>
                            {role}
                        </Badge>
                    ))}
                </div>
            ),
        }) as ColumnDef<User>,
        columnHelper.accessor('status', {
            id: 'status',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Status" />,
            enableSorting: false,
            enableHiding: false,
            cell: ({ cell, row }) => {
                const value = cell.getValue();
                if (value === UserStatus.ACTIVE) {
                    return <Badge>Active</Badge>;
                }
                if (value === UserStatus.INACTIVE) {
                    return <Badge variant="outline">Inactive</Badge>;
                }
                return null;
            },
        }) as ColumnDef<User>,
        columnHelper.display({
            id: 'actions',
            cell: ({ row }) => {
                return (
                    <div className="flex flex-row gap-2 justify-end">
                        {row.original.email_verified_at === null && (
                            <Send
                                className="h-6 w-6 cursor-pointer"
                                onClick={() => router.post(route('verification.resend'), { id: row.original.id })}
                            />
                        )}
                        <UserCog className="h-6 w-6 cursor-pointer" onClick={() => onManage(row.original)} />
                        {row.original.id !== currentUser.id && (
                            <AlertDialog>
                                <AlertDialogTrigger>
                                    <Trash className="h-6 w-6 cursor-pointer" />
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Delete User</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Are you sure you want to delete <strong>{row.original.name}</strong>? This
                                            action cannot be undone.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction
                                            onClick={() => onDelete({ id: row.original.id ?? undefined })}
                                        >
                                            Delete
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        )}
                    </div>
                );
            },
        }),
    ];

    return (
        <DataTable
            columns={columns}
            data={users}
            filter={{ placeholder: 'Filter users…', column: 'name' }}
            facets={{
                teams: {
                    label: 'Teams',
                    values: teams.map((team) => ({
                        value: team.id?.toString() ?? '',
                        label: team.name ?? '',
                    })),
                },
                roles: {
                    label: 'Roles',
                    values: [
                        {
                            value: Role.OWNER,
                            label: Role.OWNER,
                        },
                        {
                            value: Role.ADMIN,
                            label: Role.ADMIN,
                        },
                        {
                            value: Role.DEVELOPER,
                            label: Role.DEVELOPER,
                        },
                        {
                            value: Role.BILLER,
                            label: Role.BILLER,
                        },
                    ],
                },
                status: {
                    label: 'Status',
                    values: [
                        {
                            value: 'active',
                            label: 'Active',
                        },
                        {
                            value: 'inactive',
                            label: 'Inactive',
                        },
                        {
                            value: 'pending',
                            label: 'Pending',
                        },
                    ],
                },
            }}
            pageSize={tableOptions.perPage}
            columnVisibility={{ Updated: false }}
            tableOptions={tableOptions}
            currentFilters={tableOptions.filters}
            totalRows={userCount}
        />
    );
}
