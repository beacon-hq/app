import { Role, User, UserCollection, UserStatus } from '@/Application';
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
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { Trash } from 'lucide-react';
import React from 'react';

export default function Table({
    members,
    memberCount,
    onDelete,
    tableOptions,
}: {
    members: UserCollection;
    memberCount?: number;
    onDelete: (user: { user_id?: number; email?: string }) => void;
    tableOptions: TableOptions;
}) {
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
        }) as ColumnDef<User>,
        columnHelper.accessor('email', {
            id: 'email',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Email" />,
            enableSorting: true,
            enableHiding: false,
        }) as ColumnDef<User>,
        columnHelper.accessor('roles', {
            id: 'roles',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Roles" />,
            enableSorting: false,
            enableHiding: false,
            cell: ({ cell }) => (
                <div className="flex flex-wrap gap-1">
                    {(cell?.getValue() ?? []).map((role: string) => (
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
                if (value === UserStatus.ACTIVE && row.original.email_verified_at !== null) {
                    return <Badge>Active</Badge>;
                }
                if (value === UserStatus.INACTIVE && row.original.email_verified_at !== null) {
                    return <Badge variant="outline">Inactive</Badge>;
                }
                if (row.original.email_verified_at === null) {
                    return (
                        <Badge className="bg-primary/30 border-primary/50 hover:bg-primary/40 text-black">
                            Pending
                        </Badge>
                    );
                }
            },
        }) as ColumnDef<User>,
        columnHelper.display({
            id: 'actions',
            cell: ({ row }) => {
                return (
                    <div className="flex flex-row gap-2 justify-end">
                        <div className="flex">
                            <AlertDialog>
                                <AlertDialogTrigger>
                                    <Trash className="h-6 w-6 cursor-pointer" />
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Remove User</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Are you sure you want to remove <strong>{row.original.name}</strong> from
                                            the team?
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction
                                            onClick={() => onDelete({ user_id: row.original.id as number })}
                                        >
                                            Delete
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>
                );
            },
        }),
    ];

    return (
        <DataTable
            columns={columns}
            data={members}
            filter={{ placeholder: 'Filter members…', column: 'name' }}
            facets={{
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
            totalRows={memberCount ?? undefined}
        />
    );
}
