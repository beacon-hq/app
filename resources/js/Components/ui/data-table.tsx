'use client';

import { DataTablePagination } from './data-table-pagination';
import { DataTableFacets, DataTableToolbar, FilterProps } from './data-table-toolbar';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { TableFilters } from '@/Pages/FeatureFlags/Components/Table';
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
import * as React from 'react';
import { TableHTMLAttributes, useEffect } from 'react';

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
    onPageChange?: (paginationState: PaginationState) => void;
    onFilterChange?: (filterState: TableFilters) => void;
    onSortingChange?: (sortingState: SortingState) => void;
    sortBy?: SortingState;
    columnVisibility?: VisibilityState;
    currentFilters?: TableFilters;
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
    onPageChange,
    onFilterChange,
    onSortingChange,
    sortBy,
    columnVisibility,
    totalRows,
    currentFilters,
    ...tableProps
}: TableHTMLAttributes<HTMLTableElement> & DataTableProps<TData, TValue>) {
    const [rowSelection, setRowSelection] = React.useState({});
    const [columnVisibilityState, setColumnVisibilityState] = React.useState<VisibilityState>(columnVisibility ?? {});
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [sorting, setSorting] = React.useState<SortingState>(sortBy ?? []);
    const [pagination, setPagination] = React.useState<PaginationState>({ pageIndex: page - 1, pageSize });

    const handleSortingChange = (updater: any) => {
        console.log(updater);
        const newSortingValue: SortingState = updater instanceof Function ? updater(sorting) : updater;
        console.log(newSortingValue);

        if (onSortingChange) {
            onSortingChange(newSortingValue);
        }

        setSorting(updater);
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
        if (onPageChange) {
            onPageChange(pagination);
        }
    }, [pagination]);

    return (
        <div className="space-y-4">
            <DataTableToolbar
                table={table}
                filter={filter}
                facets={facets}
                onFilterChange={onFilterChange}
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
