import { ActivityLogCollection, Color } from '@/Application';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { TimelineLayout } from '@/Components/ui/timeline-layout';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { localDate } from '@/lib/utils';
import { TimelineElement } from '@/types/timeline';
import React from 'react';

export default function ActivityLog({ log }: { log: ActivityLogCollection }) {
    if (!log.length) return <div className="p-4 text-center text-muted-foreground">No activity found.</div>;

    let timelineData: TimelineElement[] = [];
    log.forEach((entry) => {
        timelineData.push({
            color: entry.event === 'created' ? Color.GREEN : entry.event === 'updated' ? Color.BLUE : Color.RED,
            date: localDate(entry.created_at),
            description: entry.properties && (
                <pre className="mt-2 bg-muted p-2 rounded text-xs overflow-x-auto">
                    {Object.entries(entry.properties.attributes).map(function ([key, value]) {
                        if (key === 'old') {
                            return;
                        }

                        if (
                            entry.properties.old &&
                            entry.properties.old[key] !== undefined &&
                            entry.properties.old[key] === value
                        ) {
                            return;
                        }

                        let diff = (
                            <span key={key} className="block text-green-600">
                                ++ {key}: {typeof value === 'object' && JSON.stringify(value)}
                                {typeof value === 'boolean' && value && 'true'}
                                {typeof value === 'boolean' && !value && 'false'}
                                {typeof value === 'string' && `${value}`}
                            </span>
                        );

                        if (entry.properties.old && entry.properties.old[key] !== undefined) {
                            diff = (
                                <>
                                    <span key={`old-${key}`} className="block text-red-600">
                                        -- {key}:{' '}
                                        {typeof entry.properties.old[key] === 'object' &&
                                            JSON.stringify(entry.properties.old[key])}
                                        {typeof entry.properties.old[key] === 'boolean' &&
                                            entry.properties.old[key] &&
                                            'true'}
                                        {typeof entry.properties.old[key] === 'boolean' &&
                                            !entry.properties.old[key] &&
                                            'false'}
                                        {typeof entry.properties.old[key] === 'string' &&
                                            `${entry.properties.old[key]}`}
                                    </span>
                                    {diff}
                                </>
                            );
                        }

                        return diff;
                    })}
                </pre>
            ),
            id: entry.id,
            title: entry.event,
            icon: (
                <TooltipProvider>
                    <Tooltip delayDuration={0}>
                        <TooltipTrigger asChild>
                            <Avatar className="w-8 h-8">
                                <AvatarImage src={entry.user?.avatar ?? entry.user?.gravatar} alt={entry.user?.name} />
                                <AvatarFallback>
                                    {entry.user?.first_name?.charAt(0)}
                                    {entry.user?.last_name?.charAt(0)}
                                </AvatarFallback>
                            </Avatar>
                        </TooltipTrigger>
                        <TooltipContent>{entry.user?.name}</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            ),
        });
    });

    return <TimelineLayout items={timelineData} />;
}
