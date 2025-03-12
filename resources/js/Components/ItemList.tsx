import { IconColor } from '@/Components/IconColor';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import React from 'react';

type Item = { slug: string; name: string; color: string };

export default function ItemList({
    items,
    className,
    asTooltip = false,
}: {
    items: Item[];
    className?: string;
    asTooltip?: boolean;
}) {
    return (
        <div className={cn('flex', className)}>
            {items.map(function (item: Item, key: number) {
                if (!asTooltip) {
                    return (
                        <div key={key} className="flex flex-row gap-1 items-center p-1 group first:-ml-0 -ml-4">
                            <IconColor color={item.color} />
                            <span className="hidden group-hover:inline-block pr-3">{item.name}</span>
                        </div>
                    );
                }

                return (
                    <TooltipProvider key={key} skipDelayDuration={0} delayDuration={0}>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <div key={key} className="flex flex-row gap-1 items-center p-1 group first:-ml-0 -ml-4">
                                    <IconColor color={item.color} />
                                </div>
                            </TooltipTrigger>
                            <TooltipContent className="bg-primary">
                                <p>{item.name}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                );
            })}
        </div>
    );
}
