'use client';

import { DataTablePagination } from './data-table-pagination';
import { DataTableFacets, DataTableToolbar, FilterProps } from './data-table-toolbar';
import { Skeleton } from '@/Components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { TableFilters } from '@/Pages/FeatureFlags/Components/Table';
import { router } from '@inertiajs/react';
import {
    ColumnDef,
    ColumnFiltersState,
    flexRender,
    getCoreRowModel,
    getFacetedRowModel,
    getFacetedUniqueValues,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    OnChangeFn,
    PaginationState,
    Row,
    RowSelectionState,
    SortingState,
    useReactTable,
    VisibilityState,
} from '@tanstack/react-table';
import * as _ from 'lodash';
import * as React from 'react';
import { TableHTMLAttributes, useEffect, useState } from 'react';
import { useDebounceCallback } from 'usehooks-ts';

export type TableOptions = {
    page: number;
    perPage: number;
    filters: TableFilters;
    sort?: Record<string, string>;
};

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    filter?: FilterProps;
    facets?: DataTableFacets;
    enableRowSelection?: boolean | ((row: Row<TData>) => boolean);
    onRowSelectionChange?: OnChangeFn<RowSelectionState>;
    page?: number;
    pageSize?: number;
    totalRows?: number;
    onSortingChange?: (sortingState: SortingState) => void;
    sortBy?: SortingState;
    columnVisibility?: VisibilityState;
    currentFilters?: TableFilters;
    tableOptions?: TableOptions;
}

export function DataTable<TData, TValue>({
    columns,
    data,
    filter,
    facets,
    enableRowSelection = false,
    onRowSelectionChange,
    page = 1,
    pageSize = 10,
    onSortingChange,
    sortBy,
    columnVisibility,
    totalRows,
    currentFilters,
    tableOptions,
    ...tableProps
}: TableHTMLAttributes<HTMLTableElement> & DataTableProps<TData, TValue>) {
    const [rowSelection, setRowSelection] = useState({});
    const [columnVisibilityState, setColumnVisibilityState] = useState<VisibilityState>(columnVisibility ?? {});
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [sorting, setSorting] = useState<SortingState>(sortBy ?? []);
    const [pagination, setPagination] = useState<PaginationState>({ pageIndex: page - 1, pageSize });
    const [currentTableOptions, setCurrentTableOptions] = useState(tableOptions);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        if (!_.isEqual(tableOptions, currentTableOptions)) {
            setCurrentTableOptions(tableOptions);
        }
    }, [tableOptions]);
    const [newFilters, setNewFilters] = useState<TableFilters>(currentFilters ?? {});

    const debounceFilterChange = useDebounceCallback((filters: TableFilters) => {
        if (!_.isEqual(filters, newFilters)) {
            setNewFilters(filters);
        }
    }, 800);

    const handleSortingChange = (updater: any) => {
        const newSortingValue: SortingState = updater instanceof Function ? updater(sorting) : updater;

        if (onSortingChange) {
            onSortingChange(newSortingValue);
        }

        setSorting(updater);

        if (newSortingValue.length > 0) {
            let filterQuery = {};

            if (currentTableOptions?.filters && Object.keys(currentTableOptions.filters).length > 0) {
                filterQuery = Object.fromEntries(
                    Object.entries(currentTableOptions.filters).map(([key, value]) => {
                        return [key, Array.from(value as Set<string>)];
                    }),
                );
            }

            const sortParams: Record<string, string> = {};
            newSortingValue.forEach((sort) => {
                sortParams[sort.id] = sort.desc ? 'desc' : 'asc';
            });

            router.get(
                route(route().current() as string, route().routeParams),
                {
                    page: currentTableOptions?.page || 1,
                    perPage: currentTableOptions?.perPage || 10,
                    filters: filterQuery,
                    sort: sortParams,
                },
                {
                    onStart: () => setIsLoading(true),
                    onFinish: () => setIsLoading(false),
                },
            );
        }
    };

    const handleFilterChange = (filters?: TableFilters) => {
        if (!tableOptions?.filters) {
            return;
        }

        if (!filters) {
            router.get(route(route().current() as string, route().routeParams), undefined, {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
            });
            return;
        }

        const nonEmptyFilters = Object.fromEntries(
            Object.entries(filters).filter(([key, value]: [string, Set<string>]) => {
                return value.size > 0 && (key !== filter?.column || Array.from(value)[0] !== '');
            }),
        );

        if (_.isEqual(currentFilters, nonEmptyFilters)) {
            return;
        }

        if (Object.keys(nonEmptyFilters).length === 0) {
            router.get(route(route().current() as string, route().routeParams), undefined, {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
            });
            return;
        }

        const filterQuery = Object.fromEntries(
            Object.entries(nonEmptyFilters).map(function ([key, value]) {
                return [key, Array.from(value)];
            }),
        );

        const sortParams: Record<string, string> = {};
        if (sorting.length > 0) {
            sorting.forEach((sort) => {
                sortParams[sort.id] = sort.desc ? 'desc' : 'asc';
            });
        }

        router.get(
            route(route().current() as string, route().routeParams),
            {
                page: 1,
                perPage: currentTableOptions?.perPage,
                filters: filterQuery as any,
                sort: Object.keys(sortParams).length > 0 ? sortParams : undefined,
            },
            {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
            },
        );
    };

    useEffect(() => {
        if (!_.isEqual(currentFilters, newFilters)) {
            setNewFilters(currentFilters ?? {});
        }
    }, [currentFilters]);

    useEffect(() => {
        handleFilterChange(newFilters);
    }, [newFilters]);

    const handlePaginationChange = (pagination: { pageIndex: number; pageSize: number }) => {
        if (
            !currentTableOptions ||
            pagination.pageIndex + 1 !== currentTableOptions.page ||
            pagination.pageSize !== currentTableOptions.perPage
        ) {
            let filterQuery = {};

            if (currentTableOptions?.filters && Object.keys(currentTableOptions.filters).length > 0) {
                filterQuery = Object.fromEntries(
                    Object.entries(currentTableOptions.filters).map(([key, value]) => {
                        return [key, Array.from(value as Set<string>)];
                    }),
                );
            }

            const sortParams: Record<string, string> = {};
            if (sorting.length > 0) {
                sorting.forEach((sort) => {
                    sortParams[sort.id] = sort.desc ? 'desc' : 'asc';
                });
            }

            router.get(route(route().current() as string, route().routeParams), {
                page: pagination.pageIndex + 1,
                perPage: pagination.pageSize,
                filters: filterQuery,
                sort: Object.keys(sortParams).length > 0 ? sortParams : undefined,
            });
        }
    };

    const table = useReactTable({
        data,
        columns,
        state: {
            sorting,
            columnVisibility: columnVisibilityState,
            rowSelection,
            columnFilters,
            pagination,
        },
        initialState: {
            pagination: {
                pageIndex: page - 1,
                pageSize,
            },
        },
        enableRowSelection,
        onRowSelectionChange: onRowSelectionChange ?? setRowSelection,
        onSortingChange: handleSortingChange,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibilityState,
        onPaginationChange: setPagination,
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFacetedRowModel: getFacetedRowModel(),
        getFacetedUniqueValues: getFacetedUniqueValues(),
        rowCount: totalRows ?? data.length,
        manualFiltering: totalRows !== undefined,
        manualSorting: totalRows !== undefined,
        manualPagination: totalRows !== undefined,
    });

    const isInitialMount = React.useRef(true);
    useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        if (page && pageSize && totalRows) {
            handlePaginationChange(pagination);
        }
    }, [pagination.pageIndex, pagination.pageSize]);

    return (
        <div className="space-y-4">
            <DataTableToolbar
                table={table}
                filter={filter}
                facets={facets}
                onFilterChange={(filters) => debounceFilterChange(filters)}
                onFilterReset={() => {
                    setNewFilters({});
                    table.resetColumnFilters();
                    if (tableOptions?.filters) {
                        router.get(route(route().current() as string, route().routeParams), undefined, {
                            onStart: () => setIsLoading(true),
                            onFinish: () => setIsLoading(false),
                        });
                    }
                }}
                currentFilters={currentFilters}
            />
            <div className="rounded-md border">
                <Table {...tableProps}>
                    <TableHeader className="bg-neutral-200 dark:bg-neutral-700">
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead
                                            key={header.id}
                                            colSpan={header.colSpan}
                                            className="text-md font-bold uppercase"
                                        >
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {isLoading ? (
                            Array.from({ length: pageSize }).map((_, index) => (
                                <TableRow key={`skeleton-${index}`}>
                                    {columns.map((column, cellIndex) => (
                                        <TableCell key={`skeleton-cell-${cellIndex}`}>
                                            <Skeleton className="h-8 w-full" />
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
            <DataTablePagination table={table} originalPageSize={pageSize} />
        </div>
    );
}
