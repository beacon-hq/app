import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/Components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Separator } from '@/Components/ui/separator';
import { TableFilters } from '@/Pages/FeatureFlags/Components/Table';
import { cn } from '@/lib/utils';
import { Column } from '@tanstack/react-table';
import _ from 'lodash';
import { Check, PlusCircle } from 'lucide-react';
import React, { ReactNode } from 'react';
import { useDebounceCallback } from 'usehooks-ts';

interface DataTableFacetedFilterProps<TData, TValue> {
    column: Column<TData, TValue>;
    title?: string;
    options: {
        label: string;
        value: string;
        icon?: React.ComponentType<{ className?: string }>;
    }[];
    currentFilters?: TableFilters;
    onFilterChange?: (filterState: TableFilters) => void;
}

export function DataTableFacetedFilter<TData, TValue>({
    column,
    title,
    options,
    onFilterChange,
    currentFilters,
}: DataTableFacetedFilterProps<TData, TValue>) {
    const facets = column.getFacetedUniqueValues();
    const [selectedValues, setSelectedValues] = React.useState<Set<string>>(
        currentFilters && currentFilters[column.id] && currentFilters[column.id].size > 0
            ? currentFilters[column.id]
            : new Set(column?.getFilterValue() as string[]),
    );

    React.useEffect(() => {
        const newValues =
            currentFilters && currentFilters[column.id] && currentFilters[column.id].size > 0
                ? currentFilters[column.id]
                : new Set(column?.getFilterValue() as string[]);

        if (!_.isEqual(newValues, selectedValues)) {
            setSelectedValues(newValues);
        }
    }, [currentFilters, column.id]);

    const debounce = useDebounceCallback((values) => {
        if (currentFilters && !_.isEqual(currentFilters[column?.id as string], values)) {
            onFilterChange
                ? onFilterChange({
                      ...currentFilters,
                      [column?.id as string]: values,
                  })
                : column?.setFilterValue(selectedValues.size > 0 ? Array.from(values) : undefined);
        }
    }, 700);
    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 border-dashed">
                    <PlusCircle />
                    {title}
                    {selectedValues?.size > 0 && (
                        <>
                            <Separator orientation="vertical" className="mx-2 h-4" />
                            <Badge variant="secondary" className="rounded-xs px-1 font-normal lg:hidden">
                                {selectedValues.size}
                            </Badge>
                            <div className="hidden space-x-1 lg:flex">
                                {selectedValues.size > 2 ? (
                                    <Badge variant="secondary" className="rounded-xs px-1 font-normal">
                                        {selectedValues.size} selected
                                    </Badge>
                                ) : (
                                    options
                                        .filter((option) => selectedValues.has(option.value))
                                        .map((option) => (
                                            <Badge
                                                variant="secondary"
                                                key={option.value}
                                                className="rounded-xs px-1 font-normal"
                                            >
                                                {option.label}
                                            </Badge>
                                        ))
                                )}
                            </div>
                        </>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[200px] p-0" align="start">
                <Command>
                    <CommandInput placeholder={title} />
                    <CommandList>
                        <CommandEmpty>No results found.</CommandEmpty>
                        <CommandGroup>
                            {options.map((option) => {
                                const isSelected = selectedValues.has(option.value);
                                return (
                                    <CommandItem
                                        key={option.value}
                                        onSelect={() => {
                                            let newValues = new Set(selectedValues);
                                            if (isSelected) {
                                                newValues.delete(option.value);
                                                setSelectedValues(newValues);
                                                debounce(newValues);
                                            } else {
                                                newValues.add(option.value);
                                                setSelectedValues(newValues);
                                                debounce(newValues);
                                            }
                                        }}
                                    >
                                        <div
                                            className={cn(
                                                'mr-2 flex h-4 w-4 items-center justify-center rounded-xs border border-primary',
                                                {
                                                    'opacity-50 [&_svg]:invisible': !isSelected,
                                                },
                                            )}
                                        >
                                            <Check />
                                        </div>
                                        {option.icon as ReactNode}
                                        <span>{option.label}</span>
                                        {facets?.get(option.value) && (
                                            <span className="ml-auto flex h-4 w-4 items-center justify-center font-mono text-xs">
                                                {facets.get(option.value)}
                                            </span>
                                        )}
                                    </CommandItem>
                                );
                            })}
                        </CommandGroup>
                        {selectedValues.size > 0 && (
                            <>
                                <CommandSeparator />
                                <CommandGroup>
                                    <CommandItem
                                        onSelect={() => {
                                            setSelectedValues(new Set());
                                            debounce(new Set());
                                            onFilterChange
                                                ? onFilterChange({
                                                      ...currentFilters,
                                                      [column?.id as string]: new Set(),
                                                  })
                                                : column?.setFilterValue(undefined);
                                        }}
                                        className="justify-center text-center"
                                    >
                                        Clear filters
                                    </CommandItem>
                                </CommandGroup>
                            </>
                        )}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
