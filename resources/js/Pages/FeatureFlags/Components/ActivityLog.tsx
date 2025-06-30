import { ActivityLogCollection, Color } from '@/Application';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { ObjectDiff } from '@/Components/ui/object-diff';
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
                <div
                    key={entry.id}
                    className="font-mono mt-2 bg-muted p-2 rounded text-xs overflow-x-auto whitespace-pre"
                >
                    {Object.entries(entry.properties.attributes).map(([key, value]) => {
                        if (key === 'old') {
                            return null;
                        }

                        const oldValue = entry.properties.old?.[key];

                        if (oldValue !== undefined && JSON.stringify(oldValue) === JSON.stringify(value)) {
                            return null;
                        }

                        return <ObjectDiff key={key} keyName={key} oldValue={oldValue} newValue={value} />;
                    })}
                </div>
            ),
            id: entry.id,
            title: entry.event + (entry.subject !== '' ? ' > ' + entry.subject : ''),
            icon: (
                <TooltipProvider key={entry.id}>
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
