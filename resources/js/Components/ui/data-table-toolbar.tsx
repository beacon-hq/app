'use client';

import { Table } from '@tanstack/react-table';
import { X } from 'lucide-react';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { DataTableViewOptions } from '@/Components/ui/data-table-view-options';

// import { DataTableFacetedFilter } from '@/Components/ui/data-table-faceted-filter';

export interface FilterProps {
    placeholder: string;
    column: string;
}

interface DataTableToolbarProps<TData> {
    table: Table<TData>;
    filter?: FilterProps;
}

export function DataTableToolbar<TData>({ table, filter }: DataTableToolbarProps<TData>) {
    const isFiltered = table.getState().columnFilters.length > 0;

    return (
        <div className="flex items-center justify-between">
            <div className="flex flex-1 items-center space-x-2">
                {filter && (
                    <Input
                        placeholder={filter.placeholder}
                        value={(table.getColumn(filter.column)?.getFilterValue() as string) ?? ''}
                        onChange={(event) => table.getColumn(filter.column)?.setFilterValue(event.target.value)}
                        className="h-8 w-[150px] lg:w-[250px]"
                    />
                )}
                {/*{table.getColumn('status') && (*/}
                {/*    <DataTableFacetedFilter column={table.getColumn('status')} title="Status" options={statuses} />*/}
                {/*)}*/}
                {/*{table.getColumn('priority') && (*/}
                {/*    <DataTableFacetedFilter*/}
                {/*        column={table.getColumn('priority')}*/}
                {/*        title="Priority"*/}
                {/*        options={priorities}*/}
                {/*    />*/}
                {/*)}*/}
                {isFiltered && (
                    <Button variant="ghost" onClick={() => table.resetColumnFilters()} className="h-8 px-2 lg:px-3">
                        Reset
                        <X />
                    </Button>
                )}
            </div>
            <DataTableViewOptions table={table} />
        </div>
    );
}
