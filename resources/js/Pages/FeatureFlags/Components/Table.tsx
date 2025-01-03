import { IconColor } from '@/Components/IconColor';
import { DataTable } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { localDateTime } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { Eye } from 'lucide-react';

export default function Table({ featureFlags }: { featureFlags: any }) {
    const columnHelper = createColumnHelper();

    const columns: ColumnDef<any, any>[] = [
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row, cell }) => (
                <div className="flex flex-row items-center">
                    <div>
                        <IconColor
                            icon={(row.original as any).feature_type.icon}
                            color={(row.original as any).feature_type.color}
                        />
                    </div>
                    <div className="ml-2 w-64">
                        <p className="font-semibold">{cell.getValue()}</p>
                        <p className="truncate text-xs text-gray-500">{(row.original as any).description}</p>
                    </div>
                </div>
            ),
        }) as ColumnDef<any, any>,
        columnHelper.accessor('last_seen_at', {
            id: 'Last Seen',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Last Seen" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<any, any>,
        columnHelper.accessor('created_at', {
            id: 'Created',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Created" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<any, any>,
        columnHelper.accessor('updated_at', {
            id: 'Updated',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Updated" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<any, any>,
        columnHelper.display({
            id: 'tools',
            cell: function ({ row }) {
                return (
                    <div className="flex">
                        <Link href={route('feature-flags.edit', (row.original as any).slug)}>
                            <Eye className="h-6 w-6" />
                        </Link>
                    </div>
                );
            },
        }) as ColumnDef<any, any>,
    ];

    return (
        <DataTable
            columns={columns}
            data={featureFlags}
            filter={{ placeholder: 'Filter feature flags…', column: 'name' }}
            pageSize={20}
            columnVisibility={{ Updated: false }}
            sortBy={[{ id: 'name', desc: false }]}
        />
    );
}
