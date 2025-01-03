import CopyToClipboard from '@/Components/CopyToClipboard';
import { Alert, AlertDescription } from '@/Components/ui/alert';
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
import { DataTable } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { localDateTime } from '@/lib/utils';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { AlertCircle, Trash } from 'lucide-react';
import React from 'react';

export default function Table({ tokens, onDelete }: { tokens: any; onDelete: (id: string) => void }) {
    const columnHelper = createColumnHelper();

    const columns: ColumnDef<any, any>[] = [
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
        }) as ColumnDef<any, any>,
        columnHelper.accessor('token', {
            id: 'token',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Token" />,
            enableSorting: false,
            enableHiding: false,
            cell: function ({ cell }) {
                const token = cell.getValue() as string;
                return (
                    <div className="flex flex-col">
                        <div className="font-semibold flex flex-row items-center">
                            <p>{token}</p>
                            {token.charAt(1) !== '*' && <CopyToClipboard text={token} className="ml-2" />}
                        </div>
                        {token.charAt(1) !== '*' && (
                            <Alert variant="destructive" className="mt-2 flex w-fit">
                                <AlertDescription className="text-center">
                                    <AlertCircle className="h-6 w-6 inline-block mr-4" />
                                    <strong>Keep this token secure. It won't be shown again.</strong>
                                </AlertDescription>
                            </Alert>
                        )}
                    </div>
                );
            },
        }) as ColumnDef<any, any>,
        columnHelper.accessor('last_used_at', {
            id: 'last_used_at',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Last Used" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ cell }) => (cell.getValue() !== null ? localDateTime(cell.getValue()) : 'never'),
        }) as ColumnDef<any, any>,
        columnHelper.accessor('created_at', {
            id: 'created',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Created" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<any, any>,
        columnHelper.display({
            id: 'tools',
            cell: function ({ row }) {
                return (
                    <div className="flex">
                        <AlertDialog>
                            <AlertDialogTrigger>
                                <Trash className="h-6 w-6" />
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Delete Token</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Are you sure you want to delete the token "<strong>{row.original.name}</strong>
                                        "? This action cannot be undone.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                    <AlertDialogAction onClick={() => onDelete(row.original.id)}>
                                        Delete
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </div>
                );
            },
        }) as ColumnDef<any, any>,
    ];

    return (
        <DataTable
            columns={columns}
            data={tokens}
            filter={{ placeholder: 'Filter tokens…', column: 'name' }}
            pageSize={20}
            columnVisibility={{ Updated: false }}
            sortBy={[{ id: 'last_used_at', desc: false }]}
        />
    );
}
