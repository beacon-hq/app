import { cn } from '@/lib/utils';
import * as React from 'react';

const CardGroup = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, ...props }, ref) => (
        <div
            ref={ref}
            className={cn(
                'rounded-lg border-1 border-muted-foreground/20 bg-muted/5 p-1 flex flex-col gap-4',
                className,
            )}
            {...props}
        />
    ),
);
CardGroup.displayName = 'CardGroup';

const CardGroupHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, ...props }, ref) => (
        <div ref={ref} className={cn('flex flex-col space-y-1.5 pb-4', className)} {...props} />
    ),
);
CardGroupHeader.displayName = 'CardGroupHeader';

const CardGroupTitle = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, ...props }, ref) => (
        <div ref={ref} className={cn('text-lg font-semibold leading-none tracking-tight', className)} {...props} />
    ),
);
CardGroupTitle.displayName = 'CardGroupTitle';

const CardGroupContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, ...props }, ref) => <div ref={ref} className={className} {...props} />,
);
CardGroupContent.displayName = 'CardGroupContent';

const CardGroupFooter = React.forwardRef<
    HTMLDivElement,
    React.HTMLAttributes<HTMLDivElement> & { variant?: 'default' | 'inset' }
>(({ className, variant = 'default', ...props }, ref) => (
    <div
        ref={ref}
        className={cn(
            'flex items-center pt-4',
            {
                '': variant === 'default',
                'px-2 py-2 rounded-b-lg bg-muted/10': variant === 'inset',
            },
            className,
        )}
        {...props}
    />
));
CardGroupFooter.displayName = 'CardGroupFooter';

export { CardGroup, CardGroupHeader, CardGroupFooter, CardGroupTitle, CardGroupContent };
