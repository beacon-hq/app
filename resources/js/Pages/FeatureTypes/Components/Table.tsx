import { FeatureType, FeatureTypeCollection } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { DataTable } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { localDateTime } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { Pencil } from 'lucide-react';

export default function Table({ featureTypes }: { featureTypes: FeatureTypeCollection }) {
    const columnHelper = createColumnHelper<FeatureType>();

    const columns: ColumnDef<FeatureType, unknown>[] = [
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
            cell: function ({ row, cell }) {
                return (
                    <div className="flex flex-row items-center">
                        <IconColor icon={row.original?.icon} color={row.original?.color ?? null} />
                        <div className="ml-2 w-96">
                            <p className="font-semibold">{cell.getValue()}</p>
                            <p className="truncate text-xs text-gray-500">{row.original.description}</p>
                        </div>
                    </div>
                );
            },
        }) as ColumnDef<FeatureType>,
        columnHelper.accessor('is_default', {
            id: 'default',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Default" />,
            enableSorting: false,
            cell: ({ cell }) => (
                <div className="flex items-center">
                    {cell.getValue() ? (
                        <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            Default
                        </span>
                    ) : null}
                </div>
            ),
        }) as ColumnDef<FeatureType>,
        columnHelper.accessor('temporary', {
            id: 'temporary',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Temporary" />,
            enableSorting: false,
            cell: ({ cell }) => (
                <div className="flex items-center">
                    {cell.getValue() ? (
                        <span className="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                            Temporary
                        </span>
                    ) : null}
                </div>
            ),
        }) as ColumnDef<FeatureType>,
        columnHelper.accessor('created_at', {
            id: 'Created',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Created" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<FeatureType>,
        columnHelper.accessor('updated_at', {
            id: 'Updated',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Updated" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<FeatureType>,
        columnHelper.display({
            id: 'tools',
            cell: function ({ row }) {
                return (
                    <div className="flex space-x-2">
                        <Link
                            href={route('feature-types.edit', { feature_type: row.original.id as string })}
                            data-dusk="button-feature-type-edit"
                        >
                            <Pencil className="h-4 w-4" />
                        </Link>
                        {!row.original.is_default && (
                            <Link
                                href={route('feature-types.set-default', { feature_type: row.original.id as string })}
                                method="patch"
                                as="button"
                                className="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                Set Default
                            </Link>
                        )}
                    </div>
                );
            },
        }) as ColumnDef<FeatureType>,
    ];

    return (
        <DataTable
            columns={columns}
            data={featureTypes}
            filter={{ placeholder: 'Filter feature types…', column: 'name' }}
            pageSize={20}
            columnVisibility={{ Updated: false }}
        />
    );
}
