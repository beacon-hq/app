import {
    ApplicationCollection,
    EnvironmentCollection,
    FeatureFlag,
    FeatureFlagCollection,
    FeatureTypeCollection,
    TagCollection,
} from '@/Application';
import { IconColor } from '@/Components/IconColor';
import ItemList from '@/Components/ItemList';
import { DataTable, TableOptions } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { localDateTime } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';
import { Pencil } from 'lucide-react';
import React, { useState } from 'react';

export type TableFilters = { [key: string]: Set<string> };

export default function Table({
    featureFlags,
    featureFlagCount,
    featureTypes,
    tags,
    applications,
    environments,
    tableOptions,
}: {
    featureFlags: FeatureFlagCollection;
    featureFlagCount: number;
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
    tableOptions: TableOptions;
}) {
    const columnHelper = createColumnHelper<FeatureFlag>();
    const [currentTableOptions] = useState<TableOptions>(tableOptions);

    const columns: ColumnDef<FeatureFlag, any>[] = [
        columnHelper.accessor('feature_type', {
            id: 'featureType',
            header: ({ column }) => <DataTableColumnHeader column={column} title="" />,
            cell: ({ row }) => {
                return (
                    <IconColor
                        icon={row.original?.feature_type?.icon ?? null}
                        color={row.original?.feature_type?.color ?? null}
                    />
                );
            },
            enableHiding: false,
            enableSorting: false,
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row, cell }) => (
                <div className="ml-2 w-64">
                    <p className="font-semibold">{cell.getValue()}</p>
                    <p className="truncate text-xs text-gray-500">{row.original.description}</p>
                </div>
            ),
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('statuses', {
            id: 'application',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Applications" />,
            cell: ({ cell }) => {
                let statusApplications = new Set();
                cell.getValue()?.map((status) => {
                    status.application?.name && statusApplications.add(status.application.name);
                });
                return (
                    <ItemList
                        items={applications.filter((application) => statusApplications.has(application.name)) as any}
                        className="absolute -mt-4"
                        asTooltip={true}
                    />
                );
            },
            enableSorting: false,
            enableHiding: false,
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('statuses', {
            id: 'environment',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Environments" />,
            cell: ({ cell }) => {
                let statusEnvironments = new Set();
                cell.getValue()?.map((status) => {
                    status.environment?.name && statusEnvironments.add(status.environment.name);
                });
                return (
                    <ItemList
                        items={environments.filter((environment) => statusEnvironments.has(environment.name)) as any}
                        className="absolute -mt-4"
                        asTooltip={true}
                    />
                );
            },
            enableSorting: false,
            enableHiding: false,
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('tags', {
            id: 'tag',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Tags" />,
            cell: ({ cell }) => {
                return <ItemList items={cell.getValue() as any} asTooltip={true} />;
            },
            enableSorting: false,
            enableHiding: false,
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('last_seen_at', {
            id: 'Last Seen',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Last Seen" />,
            enableSorting: true,
            cell: ({ cell }) => (cell.getValue() === null ? 'never' : localDateTime(cell.getValue())),
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('created_at', {
            id: 'Created',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Created" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<FeatureFlag>,
        columnHelper.accessor('updated_at', {
            id: 'Updated',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Updated" />,
            enableSorting: true,
            cell: ({ cell }) => localDateTime(cell.getValue()),
        }) as ColumnDef<FeatureFlag>,
        columnHelper.display({
            id: 'tools',
            cell: function ({ row }) {
                return (
                    <div className="flex">
                        <Link href={route('feature-flags.edit.overview', { feature_flag: row.original.id as string })}>
                            <Pencil className="h-6 w-6" />
                        </Link>
                    </div>
                );
            },
        }) as ColumnDef<FeatureFlag>,
    ];

    return (
        <DataTable
            columns={columns}
            data={featureFlags}
            totalRows={featureFlagCount}
            filter={{
                placeholder: 'Filter feature flags…',
                column: 'name',
            }}
            facets={{
                application: {
                    label: 'Application',
                    values: applications.map((application) => {
                        return {
                            value: application.id,
                            label: application.name,
                            icon: (<IconColor color={application.color} className="aspect-square" />) as unknown,
                        };
                    }) as { value: string; label: string; icon: React.ComponentType<{ className?: string }> }[],
                },
                environment: {
                    label: 'Environments',
                    values: environments.map((environment) => {
                        return {
                            value: environment.id,
                            label: environment.name,
                            icon: (<IconColor color={environment.color} className="aspect-square" />) as unknown,
                        };
                    }) as { value: string; label: string; icon: React.ComponentType<{ className?: string }> }[],
                },
                tag: {
                    label: 'Tags',
                    values: tags.map((tag) => {
                        return {
                            value: tag.id,
                            label: tag.name,
                            icon: (<IconColor color={tag.color} className="aspect-square" />) as unknown,
                        };
                    }) as { value: string; label: string; icon: React.ComponentType<{ className?: string }> }[],
                },
                featureType: {
                    label: 'Feature Type',
                    values: featureTypes.map((featureType) => {
                        return {
                            value: featureType.id,
                            label: featureType.name,
                            icon: featureType.icon ? (
                                <IconColor icon={featureType.icon} color={featureType.color} />
                            ) : undefined,
                        };
                    }) as { value: string; label: string; icon?: React.ComponentType<{ className?: string }> }[],
                },
            }}
            pageSize={currentTableOptions.perPage}
            page={currentTableOptions.page}
            columnVisibility={{ Updated: false }}
            sortBy={[{ id: 'name', desc: false }]}
            currentFilters={currentTableOptions.filters}
            tableOptions={tableOptions}
        />
    );
}
