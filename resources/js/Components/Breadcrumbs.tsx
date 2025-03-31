import Icon from '@/Components/Icon';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import React, { Fragment } from 'react';

export type Breadcrumb = {
    name: string;
    href?: string;
    icon?: string | React.ReactNode;
};

const Breadcrumbs = ({ breadcrumbs }: { breadcrumbs: Breadcrumb[] }) => {
    return (
        <Breadcrumb className="inline-block">
            <BreadcrumbList>
                {breadcrumbs.map((breadcrumb: Breadcrumb, index: number) => (
                    <Fragment key={breadcrumb.name}>
                        <BreadcrumbItem className="text-3xl leading-tight text-gray-800 dark:text-gray-200">
                            {breadcrumb.href && (
                                <BreadcrumbLink asChild>
                                    <Link href={breadcrumb.href} className="flex flex-row gap-2 items-center">
                                        {breadcrumb.icon && typeof breadcrumb.icon === 'string' && (
                                            <Icon name={breadcrumb.icon} className={cn('h-8 w-8 inline-block')} />
                                        )}
                                        {breadcrumb.icon && typeof breadcrumb.icon !== 'string' && breadcrumb.icon}
                                        {breadcrumb.name}
                                    </Link>
                                </BreadcrumbLink>
                            )}
                            {!breadcrumb.href && (
                                <BreadcrumbPage
                                    className={cn('flex flex-row items-center gap-2', {
                                        'font-semibold': breadcrumbs.length - 1 === index,
                                    })}
                                >
                                    {breadcrumb.icon && typeof breadcrumb.icon === 'string' && (
                                        <Icon name={breadcrumb.icon} className={cn('h-8 w-8 inline-block')} />
                                    )}
                                    {breadcrumb.icon && typeof breadcrumb.icon !== 'string' && breadcrumb.icon}
                                    {breadcrumb.name}
                                </BreadcrumbPage>
                            )}
                        </BreadcrumbItem>
                        {breadcrumbs.length - 1 !== index && <BreadcrumbSeparator />}
                    </Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
};

export default Breadcrumbs;
