import { cn } from '@/lib/utils';
import { PropsWithChildren } from 'react';

const DefinitionList = function ({ children, className }: PropsWithChildren & { className?: string }) {
    return <dl className={cn('sm:divide-y sm:divide-gray-200', className)}>{children}</dl>;
};

export const Definition = function ({ children, className }: PropsWithChildren & { className?: string }) {
    return <div className={cn('py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6', className)}>{children}</div>;
};

export const DefinitionTerm = function ({ children, className }: PropsWithChildren & { className?: string }) {
    return <dt className={cn('text-sm text-primary font-bold', className)}>{children}</dt>;
};

export const DefinitionDescription = function ({ children, className }: PropsWithChildren & { className?: string }) {
    return <dd className={cn('mt-1 text-sm text-primary sm:mt-0 sm:col-span-2', className)}>{children}</dd>;
};

export default DefinitionList;
