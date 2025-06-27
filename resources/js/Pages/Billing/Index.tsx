import { ProductCollection, Subscription } from '@/Application';
import ProductCard from '@/Components/ProductCard';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent } from '@/Components/ui/chart';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import * as ProgressPrimitive from '@radix-ui/react-progress';
import { DateTime } from 'luxon';
import React from 'react';
import { CartesianGrid, Label, Line, LineChart, ReferenceLine, XAxis, YAxis } from 'recharts';

type ProgressSegment = {
    value: number;
    name?: string;
    color?: string;
};

type Props = React.ComponentPropsWithoutRef<typeof ProgressPrimitive.Root> & {
    segments: ProgressSegment[];
};

const Progress = React.forwardRef<React.ElementRef<typeof ProgressPrimitive.Root>, Props>(
    ({ className, segments, ...props }, ref) => {
        const sortedSegments = segments.toSorted((a, b) => a.value - b.value);

        return (
            <ProgressPrimitive.Root
                ref={ref}
                className={cn('relative h-2 w-full overflow-hidden rounded bg-slate-200', className)}
                {...props}
            >
                {sortedSegments.map((segment, index) => (
                    <ProgressPrimitive.Indicator
                        key={segment?.name ?? index}
                        className={cn('h-full transition-all absolute top-0 rounded-r', segment?.color ?? 'bg-primary')}
                        style={{
                            width: `${segment.value}%`,
                            left: '0%',
                            zIndex: sortedSegments.length - index,
                        }}
                    />
                ))}
            </ProgressPrimitive.Root>
        );
    },
);

Progress.displayName = 'Progress';

export default function Index({
    products,
    subscription,
    trialEnd,
    periodEnd,
    usage,
    predictedBill,
    invoices,
    subscriptionStatus,
}: PageProps & {
    products: ProductCollection;
    subscription: Subscription;
    trialEnd: string | null;
    periodEnd: string;
    usage: any;
    predictedBill: { total: string; base: string; metered: string };
    invoices: any;
    subscriptionStatus: {
        active: boolean;
        grace_period: boolean;
        trialing: boolean;
        ends_at: string;
    };
}) {
    console.log(subscriptionStatus);

    let currentUsage = 0;
    if (usage.evaluations.total > subscription.plan.entitlements.evaluations) {
        currentUsage = Math.ceil((subscription.plan.entitlements.evaluations / usage.evaluations.total) * 100);
    } else {
        currentUsage = Math.ceil((usage.evaluations.total / subscription.plan.entitlements.evaluations) * 100);
    }

    const daysRemaining = Math.ceil(DateTime.fromISO(periodEnd).diffNow('days').days);

    const projectedUsageChartConfig = {
        usage: {
            label: 'Usage',
            color: 'var(--chart-1)',
        },
        projected: {
            label: 'Projected',
            color: 'var(--chart-3)',
        },
    } satisfies ChartConfig;

    if (
        usage.evaluations.data[usage.evaluations.data.length - 1] &&
        !usage.evaluations.data[usage.evaluations.data.length - 1].projected
    ) {
        usage.evaluations.data[usage.evaluations.data.length - 1].projected =
            usage.evaluations.data[usage.evaluations.data.length - 1].value;

        usage.evaluations.projections.when &&
            usage.evaluations.data.push({
                date: DateTime.fromFormat(
                    usage.evaluations.projections.when.projected_date,
                    'yyyy-MM-dd HH:mm:ss',
                ).toFormat('yyyy-MM-dd'),
                projected: usage.evaluations.projections.when.target_value,
            });

        usage.evaluations.data.push({
            date: DateTime.fromFormat(usage.evaluations.projections.date.target_date, 'yyyy-MM-dd HH:mm:ss').toFormat(
                'yyyy-MM-dd',
            ),
            projected: usage.evaluations.projections.date.projected_total,
        });
    }

    return (
        <AuthenticatedLayout breadcrumbs={[{ name: 'Billing', icon: 'CreditCard' }]}>
            <Head title="Billing Settings" />
            <div className="py-10">
                <div className="p-6 text-gray-900">
                    <div className="flex flex-row justify-between gap-4">
                        <Card className="w-1/3">
                            {subscriptionStatus.trialing && !subscriptionStatus.grace_period && (
                                <div className="bg-green-600 text-secondary rounded-t-md text-sm text-center">
                                    Trial Ends on {new Date(trialEnd).toLocaleDateString()}
                                </div>
                            )}
                            {subscriptionStatus.grace_period && (
                                <div className="bg-orange-500 text-secondary rounded-t-md text-sm text-center">
                                    Grace Period Ends on {new Date(subscriptionStatus.ends_at).toLocaleDateString()}
                                </div>
                            )}
                            <CardHeader>
                                <CardTitle className="flex flex-row justify-between items-center">
                                    <div className="flex flex-col">
                                        <div className="flex flex-row">Current Subscription:</div>
                                        <h2 className="text-3xl">{subscription.plan.name}</h2>
                                    </div>
                                    <div className="flex flex-col">
                                        <p>{subscription.plan.base_price}/month</p>
                                        <p>
                                            {subscription.plan.metered_price}/
                                            {subscription.plan.metadata.evaluation_tier_size / 1000}K evaluations
                                        </p>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription className="flex flex-col gap-4">
                                    <p className="text-sm text-gray-500 mt-2">Current Monthly Usage:</p>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Progress
                                                segments={[
                                                    { value: currentUsage, color: 'bg-primary' },
                                                    {
                                                        value: 100,
                                                        color:
                                                            usage.evaluations.total >
                                                            subscription.plan.entitlements.evaluations
                                                                ? 'bg-red-500'
                                                                : 'bg-slate-200',
                                                    },
                                                ]}
                                            />
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            {usage.evaluations.total} / {subscription.plan.entitlements.evaluations}
                                        </TooltipContent>
                                    </Tooltip>
                                    <p className="text-xs">
                                        There are <strong>{daysRemaining} days</strong> remaining in the current billing
                                        cycle
                                    </p>
                                    <div className="flex justify-end">
                                        {subscriptionStatus.active && !subscriptionStatus.grace_period && (
                                            <AlertDialog>
                                                <AlertDialogTrigger asChild>
                                                    <Button variant="ghost">Cancel Subscription</Button>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>
                                                            Are you sure you want to cancel?
                                                        </AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            You will continue to have access to your account until{' '}
                                                            {new Date(periodEnd).toLocaleDateString()}.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogAction asChild>
                                                            <Button
                                                                variant="destructive"
                                                                onClick={() => {
                                                                    router.delete(
                                                                        route('billing.destroy', {
                                                                            billing: subscription.id,
                                                                        }),
                                                                    );
                                                                }}
                                                            >
                                                                Cancel Subscription
                                                            </Button>
                                                        </AlertDialogAction>
                                                        <AlertDialogCancel asChild>
                                                            <Button variant="outline">Close</Button>
                                                        </AlertDialogCancel>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        )}
                                        {subscriptionStatus.grace_period && (
                                            <Button
                                                className="bg-success hover:bg-success/80"
                                                type="button"
                                                onClick={() => {
                                                    router.put(
                                                        route('billing.update', {
                                                            billing: subscription.id,
                                                        }),
                                                    );
                                                }}
                                            >
                                                Resume Subscription
                                            </Button>
                                        )}
                                    </div>
                                </CardDescription>
                            </CardContent>
                        </Card>
                        <Card className="w-2/3">
                            <CardHeader>
                                <CardTitle>
                                    <div className="flex flex-row justify-between items-center">
                                        <h2 className="text-2xl">Projected Usage</h2>
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <h3>Predicted Bill: {predictedBill.total}</h3>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <ul>
                                                    <li>Base Rate: {predictedBill.base}</li>
                                                    <li>Evaluations Overage: {predictedBill.metered}</li>
                                                </ul>
                                            </TooltipContent>
                                        </Tooltip>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm">
                                {usage.evaluations.data.length > 0 && (
                                    <ChartContainer config={projectedUsageChartConfig} className="h-36 w-full">
                                        <LineChart accessibilityLayer data={usage.evaluations.data}>
                                            <CartesianGrid vertical={false} />
                                            <ReferenceLine
                                                y={subscription.plan.entitlements.evaluations}
                                                stroke="red"
                                                strokeDasharray="5 5"
                                                ifOverflow="extendDomain"
                                                style={{
                                                    strokeWidth: '1px',
                                                }}
                                            >
                                                <Label className="text-sm fill-primary">Plan Quota</Label>
                                            </ReferenceLine>
                                            {usage.evaluations.projections?.when?.projected_date && (
                                                <ReferenceLine
                                                    x={DateTime.fromFormat(
                                                        usage.evaluations.projections.when.projected_date,
                                                        'yyyy-MM-dd HH:mm:ss',
                                                    ).toFormat('yyyy-MM-dd')}
                                                    stroke="orange"
                                                    strokeDasharray="5 5"
                                                    ifOverflow="extendDomain"
                                                    style={{
                                                        strokeWidth: '1px',
                                                    }}
                                                >
                                                    <Label className="text-sm fill-primary">Quota Used</Label>
                                                </ReferenceLine>
                                            )}
                                            <XAxis dataKey="date" tickLine={false} axisLine={false} tickMargin={8} />
                                            <YAxis dataKey="projected" tickLine={false} axisLine={false} />
                                            <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                                            <Line dataKey="value" type="linear" strokeWidth={2} />
                                            <Line
                                                dataKey="projected"
                                                type="linear"
                                                strokeWidth={2}
                                                strokeDasharray="5 5"
                                            />
                                        </LineChart>
                                    </ChartContainer>
                                )}
                                {usage.evaluations.data.length === 0 && (
                                    <div className="h-40 flex flex-row items-center">
                                        <p className="h-full mx-auto w-fit pt-16">No Usage Data Found</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                    <div className="prose dark:prose-invert mt-12">
                        <h2>Change Plan</h2>
                        <p>You can change your plan at any time.</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
                        {products
                            .filter((product) => {
                                return product.id !== subscription.plan.id;
                            })
                            .map((product) => {
                                return (
                                    <ProductCard
                                        key={product.id}
                                        product={product}
                                        onClick={() => {
                                            router.put(
                                                route('checkout.update', { checkout: product.id }),
                                                {},
                                                {
                                                    preserveState: false,
                                                },
                                            );
                                        }}
                                        activeSubscription={subscription}
                                    />
                                );
                            })}
                    </div>
                    <p className="mt-6 text-center text-primary">
                        Need more than 2 million evaluations?{' '}
                        <a href="mailto:sales@beacon-hq.dev" className="underline">
                            Contact us
                        </a>{' '}
                        for a custom Enterprise plan.
                    </p>
                    <p className="text-xs mt-12 text-center text-primary">
                        Your bill will be prorated based on the time remaining in your current billing cycle. Any
                        current trial period will be cancelled upon upgrading.
                    </p>
                    <div className="prose dark:prose-invert mt-12 mb-6">
                        <h2>Invoices</h2>
                    </div>
                    <Table>
                        <TableCaption className="hidden">A list of your recent invoices.</TableCaption>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-[200px]">Invoice</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead className="text-right">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {invoices.map((invoice: any) => (
                                <TableRow>
                                    <TableCell className="font-medium">{invoice.number}</TableCell>
                                    <TableCell>{new Date(invoice.date).toLocaleDateString()}</TableCell>
                                    <TableCell>{invoice.total}</TableCell>
                                    <TableCell className="text-right">{invoice.status}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
