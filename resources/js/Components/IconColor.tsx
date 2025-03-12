import Icon from '@/Components/Icon';
import { cn } from '@/lib/utils';
import { PropsWithChildren } from 'react';

export function IconColor({
    icon,
    color,
    className,
    children,
}: PropsWithChildren & {
    icon?: string | null;
    color: string | null;
    className?: string;
}) {
    if (color == null) {
        color = 'primary';
    }

    return (
        <>
            {icon != null && <Icon name={icon} className={cn(`h-6 w-6 stroke-${color}-400`, className)} />}
            {icon == null && (
                <div
                    className={cn(
                        'h-6 w-6 rounded-full border aspect-square',
                        color?.charAt(0) !== '#' ? `bg-${color}-400` : '',
                        className,
                    )}
                    style={color?.charAt(0) === '#' ? { backgroundColor: color } : {}}
                >
                    {children}
                </div>
            )}
        </>
    );
}
