import { cn } from '@/lib/utils';
import * as LabelPrimitive from '@radix-ui/react-label';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

const labelVariants = cva(
    'text-sm font-medium leading-none font-semibold peer-disabled:cursor-not-allowed peer-disabled:opacity-70 aria-required:after:content-["*"] aria-required:after:text-red-400 aria-required:after:ml-0.5',
);

const Label = React.forwardRef<
    React.ElementRef<typeof LabelPrimitive.Root>,
    React.ComponentPropsWithoutRef<typeof LabelPrimitive.Root> & VariantProps<typeof labelVariants>
>(({ className, ...props }, ref) => (
    <LabelPrimitive.Root ref={ref} className={cn(labelVariants(), className)} {...props} />
));
Label.displayName = LabelPrimitive.Root.displayName;

export { Label };
