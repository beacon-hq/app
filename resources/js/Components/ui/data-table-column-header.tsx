import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import { Column } from '@tanstack/react-table';
import { ArrowDown, ArrowUp, ChevronsUpDown, EyeOff } from 'lucide-react';

interface DataTableColumnHeaderProps<TData, TValue> extends React.HTMLAttributes<HTMLDivElement> {
    column: Column<TData, TValue>;
    title: string;
}

export function DataTableColumnHeader<TData, TValue>({
    column,
    title,
    className,
}: DataTableColumnHeaderProps<TData, TValue>) {
    if (!column.getCanSort() && !column.getCanHide()) {
        return <div className={cn(className, 'text-md font-semibold uppercase')}>{title}</div>;
    }

    return (
        <div className={cn('flex items-center space-x-2', className)}>
            {!column.getCanHide() && column.getCanSort() && (
                <Button
                    variant="ghost"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === false ? true : column.getIsSorted() !== 'desc')
                    }
                    className="text-md font-semibold uppercase"
                >
                    {title}
                    {column.getIsSorted() === 'desc' ? (
                        <ArrowDown />
                    ) : column.getIsSorted() === 'asc' ? (
                        <ArrowUp />
                    ) : (
                        <ChevronsUpDown />
                    )}
                </Button>
            )}
            {column.getCanHide() && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="-ml-3 h-8 data-[state=open]:bg-accent">
                            <span className="text-md font-semibold uppercase">{title}</span>
                            {column.getIsSorted() === 'desc' ? (
                                <ArrowDown />
                            ) : column.getIsSorted() === 'asc' ? (
                                <ArrowUp />
                            ) : (
                                <ChevronsUpDown />
                            )}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start">
                        {column.getCanSort() && (
                            <>
                                <DropdownMenuItem onClick={() => column.toggleSorting(false)}>
                                    <ArrowUp className="h-3.5 w-3.5 text-muted-foreground/70" />
                                    Asc
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => column.toggleSorting(true)}>
                                    <ArrowDown className="h-3.5 w-3.5 text-muted-foreground/70" />
                                    Desc
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                            </>
                        )}
                        <DropdownMenuItem onClick={() => column.toggleVisibility(false)}>
                            <EyeOff className="h-3.5 w-3.5 text-muted-foreground/70" />
                            Hide
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </div>
    );
}
