'use client';

import { Button } from '@/Components/ui/button';
import { DataTableFacetedFilter } from '@/Components/ui/data-table-faceted-filter';
import { DataTableViewOptions } from '@/Components/ui/data-table-view-options';
import { Input } from '@/Components/ui/input';
import { TableFilters } from '@/Pages/FeatureFlags/Components/Table';
import { Column, Table } from '@tanstack/react-table';
import { X } from 'lucide-react';
import * as React from 'react';
import { useEffect } from 'react';
import { useDebounce } from 'use-debounce';

export interface FilterProps {
    placeholder: string;
    column: string;
    value?: string;
}

interface DataTableToolbarProps<TData> {
    table: Table<TData>;
    filter?: FilterProps;
    facets?: DataTableFacets;
    currentFilters?: TableFilters;
    onFilterChange?: (filterState?: any) => void;
    onFilterReset?: () => void;
}

export type DataTableFacets = Record<
    string,
    {
        label: string;
        values: { value: string; label: string; icon?: React.ComponentType<{ className?: string }> }[];
    }
>;

export function DataTableToolbar<TData>({
    table,
    filter,
    facets,
    currentFilters,
    onFilterChange,
    onFilterReset,
}: DataTableToolbarProps<TData>) {
    const isFiltered =
        currentFilters && onFilterChange
            ? Object.entries(currentFilters).length > 0
            : table.getState().columnFilters.length > 0;

    const [filterValue, setFilterValue] = React.useState<string>(
        currentFilters && currentFilters.name?.size > 0 ? Array.from(currentFilters.name)[0] : '',
    );
    const [currentFilterValue] = useDebounce(filterValue, 300);

    useEffect(() => {
        if (
            currentFilters &&
            currentFilters.name?.size > 0 &&
            currentFilterValue == Array.from(currentFilters.name)[0]
        ) {
            return;
        }

        if (
            currentFilterValue === '' &&
            currentFilters &&
            (Object.entries(currentFilters).length === 0 || (currentFilters.name && currentFilters.name.size === 0))
        ) {
            return;
        }

        if (onFilterChange) {
            onFilterChange({
                ...currentFilters,
                [filter?.column ?? 'name']: new Set([currentFilterValue]),
            });
            return;
        }
    }, [currentFilterValue]);

    return (
        <div className="flex items-center justify-between">
            <div className="flex flex-1 items-start space-x-2">
                {filter && (
                    <Input
                        placeholder={filter.placeholder}
                        value={
                            currentFilters && currentFilters[filter.column]
                                ? (filterValue ?? '')
                                : ((table.getColumn(filter.column)?.getFilterValue() as string) ?? '')
                        }
                        onChange={function (event) {
                            setFilterValue(event.target.value);
                            table.getColumn(filter.column)?.setFilterValue(event.target.value);
                        }}
                        className="h-8 w-[150px] lg:w-[250px]"
                    />
                )}
                {facets && (
                    <div className="flex flex-wrap gap-2">
                        {Object.entries(facets).map(([filterName, filterValues]) => (
                            <DataTableFacetedFilter
                                column={table.getColumn(filterName) as Column<TData, any>}
                                key={filterName}
                                title={filterValues.label}
                                options={filterValues.values}
                                currentFilters={currentFilters}
                                onFilterChange={onFilterChange}
                            />
                        ))}
                    </div>
                )}
                {isFiltered && (
                    <Button
                        variant="ghost"
                        onClick={() => {
                            if (onFilterReset) {
                                onFilterReset();
                                return;
                            }

                            if (onFilterChange) {
                                onFilterChange();
                            }
                        }}
                        className="h-8 px-2 lg:px-3"
                    >
                        Reset
                        <X />
                    </Button>
                )}
            </div>
            <DataTableViewOptions table={table} />
        </div>
    );
}
