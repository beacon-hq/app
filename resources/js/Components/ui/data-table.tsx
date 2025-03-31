'use client';

import { DataTablePagination } from './data-table-pagination';
import { DataTableFacets, DataTableToolbar, FilterProps } from './data-table-toolbar';
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
    const [currentTableOptions] = useState(tableOptions);
    const [newFilters, setNewFilters] = useState<TableFilters>({});

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
    };

    const handleFilterChange = (filters?: TableFilters) => {
        if (!tableOptions?.filters) {
            return;
        }

        if (!filters) {
            router.get(route(route().current() as string, route().routeParams));
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

        if (Object.entries(filters).length === 0) {
            return;
        }

        const filterQuery = Object.fromEntries(
            Object.entries(nonEmptyFilters).map(function ([key, value]) {
                return [key, Array.from(value)];
            }),
        );

        router.get(route(route().current() as string, route().routeParams), {
            page: 1,
            perPage: currentTableOptions?.perPage,
            filters: filterQuery as any,
        });
    };

    useEffect(() => {
        handleFilterChange(newFilters);
    }, [newFilters]);

    const handlePaginationChange = (pagination: { pageIndex: number; pageSize: number }) => {
        if (
            pagination.pageIndex + 1 !== currentTableOptions?.page ||
            pagination.pageSize !== currentTableOptions?.perPage
        ) {
            router.get(route(route().current() as string, route().routeParams), {
                page: pagination.pageIndex + 1,
                perPage: pagination.pageSize,
                filters:
                    Object.entries(currentTableOptions?.filters ?? {}).length > 0
                        ? Object.entries(currentTableOptions?.filters ?? {}).map(([values]) => {
                              return Array.from(values);
                          })
                        : {},
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

    useEffect(() => {
        if (page && pageSize && totalRows) {
            handlePaginationChange(pagination);
        }
    }, [pagination]);

    return (
        <div className="space-y-4">
            <DataTableToolbar
                table={table}
                filter={filter}
                facets={facets}
                onFilterChange={(filters) => debounceFilterChange(filters)}
                onFilterReset={() => setNewFilters({})}
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
                        {table.getRowModel().rows?.length ? (
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
