import { Tag as TagValue } from '@/Application';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';
import { X } from 'lucide-react';
import React, { MouseEventHandler } from 'react';

const Tag = function ({
    tag,
    showClose = false,
    onClick,
    className,
}: {
    tag: TagValue;
    showClose?: boolean;
    onClick?: MouseEventHandler<HTMLDivElement>;
    className?: string;
}) {
    return (
        <Badge
            key={tag.id}
            className={cn(
                'bg-background border-2 hover:bg-background flex items-center',
                tag.color?.charAt(0) != '#' ? `border-${tag.color}-400` : '',
                className,
            )}
            onClick={onClick ?? (() => {})}
            style={tag.color?.charAt(0) === '#' ? { borderColor: tag.color } : {}}
        >
            <span className="text-primary">{tag.name}</span>
            {showClose && <X className="h-4 w-4 ml-1 text-primary/40 relative -right-2" />}
        </Badge>
    );
};

export default Tag;
