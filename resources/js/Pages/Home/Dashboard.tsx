import OnboardingDialog from './Components/Onboarding/OnboardingDialog';
import { IconColor } from '@/Components/IconColor';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { CardGroup, CardGroupContent } from '@/Components/ui/card-group';
import {
    ChartConfig,
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from '@/Components/ui/chart';
import { DataTable } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';
import { Head } from '@inertiajs/react';
import { DotFilledIcon } from '@radix-ui/react-icons';
import { ColumnDef, ColumnSort, createColumnHelper } from '@tanstack/react-table';
import { ArrowDownLeft, ArrowRight, ArrowUpRight, TrendingDown, TrendingUp } from 'lucide-react';
import { useState } from 'react';
import { Light as SyntaxHighlighter } from 'react-syntax-highlighter';
import php from 'react-syntax-highlighter/dist/esm/languages/hljs/php';
import { Area, AreaChart, CartesianGrid, Label, Line, LineChart, Pie, PieChart, XAxis, YAxis } from 'recharts';

interface DashboardMetricValue {
    value: number;
    previous: {
        value: number;
        type?: 'increase' | 'decrease';
        difference?: number;
        percentage?: number;
    };
}

interface HealthScore {
    score: string;
    ratio: number;
    percentage: number;
    colorClass: string;
}

interface DashboardMetrics {
    totalFlags: DashboardMetricValue;
    changesMetrics: DashboardMetricValue;
    createdMetrics: DashboardMetricValue;
    archivedMetrics: DashboardMetricValue;
    flagStatusData: Array<{
        state: string;
        flags: number;
        fill: string;
    }>;
    flagTypeData: {
        data: Array<{
            type: string;
            flags: number;
            fill: string;
            percentage: number;
        }>;
        total: number;
    };
    ageData: Array<{
        month: string;
        age: number;
    }>;
    usageOverTimeData: Array<{
        month: string;
        active: number;
        inactive: number;
    }>;
    usageData: Array<{
        color: string;
        application: string;
        name: string;
        usages: number;
    }>;
    oldestData: Array<{
        application: string;
        name: string;
        created_at: string;
    }>;
}

const flagStatusChartConfig = {
    flags: {
        label: 'Known Flags',
    },
    unused: {
        label: 'Unused',
        color: 'var(--chart-4)',
    },
    active: {
        label: 'Active',
        color: 'var(--chart-1)',
    },
    stale: {
        label: 'Stale',
        color: 'var(--chart-2)',
    },
    inactive: {
        label: 'Inactive',
        color: 'var(--chart-3)',
    },
} satisfies ChartConfig;

let flagTypeChartConfig: Record<string, { label: string }> = {};

const ageChartConfig = {
    age: {
        label: 'Days Old',
        color: 'var(--chart-1)',
    },
} satisfies ChartConfig;

const usageOverTimeChartConfig = {
    active: {
        label: 'Active',
        color: 'var(--chart-1)',
    },
    inactive: {
        label: 'Inactive',
        color: 'var(--chart-2)',
    },
} satisfies ChartConfig;

const columnHelper = createColumnHelper();

const usageColumns: ColumnDef<any, any>[] = [
    columnHelper.display({
        id: 'flag',
        header: () => <div className="text-left">Flag</div>,
        cell: function (props) {
            const row = props.row as any;
            return (
                <div className="flex">
                    <IconColor color={row.original.application.color} className="mr-2 h-4 w-4 rounded-full" />
                    {row.original.application.name}
                    <ArrowRight className="h-4 w-4 pt-2 align-middle" />
                    <IconColor
                        color={row.original.feature_type.color}
                        icon={row.original.feature_type.icon}
                        className="mr-2 h-4 w-4 rounded-full"
                    />
                    {row.original.name}
                </div>
            );
        },
    }) as ColumnDef<any, any>,
    columnHelper.accessor('usages', {
        header: ({ column }) => <DataTableColumnHeader column={column} title="Usages" />,
        enableSorting: true,
        enableHiding: false,
        cell: function ({ cell }) {
            return <div className="text-center">{cell.renderValue()}</div>;
        },
    }) as ColumnDef<any, any>,
];

const oldestColumns: ColumnDef<any, any>[] = [
    columnHelper.display({
        id: 'flag',
        header: () => <div className="text-left">Flag</div>,
        cell: function (props) {
            const row = props.row as any;
            return (
                <div className="flex">
                    <IconColor color={row.original.application.color} className="mr-2 h-4 w-4 rounded-full" />
                    {row.original.application.name}
                    <ArrowRight className="h-4 w-4 pt-2 align-middle" />
                    <IconColor
                        color={row.original.feature_type.color}
                        icon={row.original.feature_type.icon}
                        className="mr-2 h-4 w-4 rounded-full"
                    />
                    {(row.original as any).name}
                </div>
            );
        },
    }) as ColumnDef<any, any>,
    columnHelper.accessor('created_at', {
        header: ({ column }) => <DataTableColumnHeader column={column} title="Created" />,
        enableSorting: true,
        enableHiding: false,
        cell: function ({ cell }) {
            return <div className="text-center">{cell.renderValue()}</div>;
        },
    }) as ColumnDef<any, any>,
];

interface DashboardProps {
    metrics: DashboardMetrics;
    onboarding: boolean;
    accessToken: string | null;
}

const defaultMetrics: DashboardMetrics = {
    totalFlags: {
        value: 0,
        previous: {
            value: 0,
            type: 'increase',
            difference: 0,
            percentage: 0,
        },
    },
    changesMetrics: {
        value: 0,
        previous: {
            value: 0,
            type: 'increase',
            difference: 0,
            percentage: 0,
        },
    },
    createdMetrics: {
        value: 0,
        previous: {
            value: 0,
            type: 'increase',
            difference: 0,
            percentage: 0,
        },
    },
    archivedMetrics: {
        value: 0,
        previous: {
            value: 0,
            type: 'increase',
            difference: 0,
            percentage: 0,
        },
    },
    flagStatusData: [],
    flagTypeData: {
        data: [],
        total: 0,
    },
    ageData: [],
    usageOverTimeData: [],
    usageData: [],
    oldestData: [],
};

function calculateHealthScore(
    flagStatusData: Array<{ state: string; flags: number; fill: string }>,
): HealthScore | false {
    if (!flagStatusData || flagStatusData.length === 0) {
        return false;
    }

    const activeFlags = flagStatusData.find((item) => item.state === 'active')?.flags ?? 0;
    const totalFlags = flagStatusData.reduce((sum, item) => sum + item.flags, 0);

    if (totalFlags <= 10) {
        return false;
    }

    const ratio = activeFlags / totalFlags;
    const percentage = Math.round(ratio * 100);

    let score: string;
    let colorClass: string;

    switch (true) {
        case ratio >= 0.8 && totalFlags >= 10:
            score = 'Excellent';
            colorClass = 'fill-green-500';
            break;
        case ratio >= 0.6 || totalFlags < 10:
            score = 'Good';
            colorClass = 'fill-green-400';
            break;
        case ratio >= 0.4:
            score = 'Fair';
            colorClass = 'fill-yellow-500';
            break;
        case ratio >= 0.2:
            score = 'Poor';
            colorClass = 'fill-orange-500';
            break;
        default:
            score = 'Critical';
            colorClass = 'fill-red-500';
    }

    return {
        score,
        ratio,
        percentage,
        colorClass,
    };
}

function chartKey(value: string): string {
    return value.replace(' ', '_').toLowerCase();
}

function MetricCard({ title, metric }: { title: string; metric: DashboardMetricValue }) {
    return (
        <Card className="flex flex-col p-4" data-dusk={`metric-card-${chartKey(title)}`}>
            <h2 className="mb-4 text-2xl font-bold">{title}</h2>
            <div className="grid grid-cols-2">
                <p className="text-4xl">{metric.value}</p>
                <div>
                    <p>
                        {metric.previous.type === undefined && (
                            <DotFilledIcon className="mr-4 inline-block h-6 w-6 text-neutral-400" />
                        )}
                        {metric.previous.type === 'increase' && (
                            <ArrowUpRight className="mr-4 inline-block h-6 w-6 text-green-400" />
                        )}
                        {metric.previous.type === 'decrease' && (
                            <ArrowDownLeft className="mr-4 inline-block h-6 w-6 text-red-400" />
                        )}
                        {metric.previous.type !== undefined && metric.previous.type === 'increase' && '+'}
                        {metric.previous.type !== undefined && metric.previous.type === 'decrease' && '-'}
                        {metric.previous.difference}
                    </p>
                    <p>this month</p>
                </div>
            </div>
        </Card>
    );
}

export default function Dashboard({ metrics = defaultMetrics, onboarding = false, accessToken }: DashboardProps) {
    SyntaxHighlighter.registerLanguage('php', php);

    let dashboardMetrics = metrics || defaultMetrics;

    dashboardMetrics.flagTypeData.data = dashboardMetrics.flagTypeData.data.map(function (item) {
        flagTypeChartConfig[item.type] = {
            label: item.type,
        };

        return {
            ...item,
            fill:
                item.fill.startsWith('#') || item.fill.startsWith('var(--color-')
                    ? item.fill
                    : `var(--color-${item.fill}-400)`,
            percentage: Math.round((item.flags / dashboardMetrics.flagTypeData.total) * 100),
            label: item.type,
        };
    });

    dashboardMetrics.flagStatusData = [...dashboardMetrics.flagStatusData];
    const healthScore = calculateHealthScore(dashboardMetrics.flagStatusData);

    const CustomLabel = ({
        viewBox,
        value = 0,
        valueClassName,
        totalLabel = undefined,
        totalLabelClassName,
    }: {
        viewBox?: {
            cx: number;
            cy: number;
            startAngle: number;
            endAngle: number;
            innerRadius: number;
            outerRadius: number;
        };
        value?: number | string;
        valueClassName?: string;
        totalLabel?: string;
        totalLabelClassName?: string;
    }) => {
        const { cx, cy } = viewBox as { cx: number; cy: number };

        if (typeof value === 'number' && totalLabel === undefined) {
            totalLabel = 'Total';
        }

        return (
            <>
                <text x={cx} y={cy + 10} textAnchor="middle">
                    <tspan
                        className={cn('text-primary fill-primary', valueClassName)}
                        style={{
                            fontWeight: 700,
                            fontSize: `${Math.max(1, 5 - 0.3 * String(value).length)}em`,
                            fontFamily: '"Source Sans 3", sans-serif',
                        }}
                    >
                        {value}
                    </tspan>
                </text>
                {totalLabel !== undefined && (
                    <text x={cx - 20} y={cy + 20}>
                        <tspan
                            className={cn('fill-primary/80', totalLabelClassName)}
                            style={{
                                fontSize: '0.8em',
                                fontFamily: '"Source Sans 3", sans-serif',
                            }}
                        >
                            {totalLabel}
                        </tspan>
                    </text>
                )}
            </>
        );
    };

    return (
        <Authenticated header="Dashboard" icon="Gauge">
            <Head title="Dashboard" />

            <OnboardingDialog isOpen={onboarding} accessToken={accessToken} />

            {!onboarding && (
                <div className="mt-4" data-dusk="dashboard">
                    <div className="overflow-hidden">
                        <CardGroup>
                            <CardGroupContent>
                                <div className="mx-auto grid grid-cols-2 justify-center gap-2 lg:grid-cols-4">
                                    <MetricCard title="Total Flags" metric={dashboardMetrics.totalFlags} />
                                    <MetricCard title="Changes" metric={dashboardMetrics.changesMetrics} />
                                    <MetricCard title="Created" metric={dashboardMetrics.createdMetrics} />
                                    <MetricCard title="Complete" metric={dashboardMetrics.archivedMetrics} />
                                </div>
                            </CardGroupContent>
                        </CardGroup>
                        <CardGroup className="mt-8">
                            <CardGroupContent>
                                <div className="grid grid-cols-1 justify-center gap-2 lg:grid-cols-3">
                                    <Card className="flex h-80 flex-col" data-dusk="metric-card-system-health">
                                        <CardHeader className="items-center pb-0">
                                            <CardTitle>System Health</CardTitle>
                                            <CardDescription>Last 30 Days</CardDescription>
                                        </CardHeader>
                                        <CardContent className="flex-1 pb-0 pt-3">
                                            {!healthScore && (
                                                <div className="flex h-full items-center justify-center text-center text-muted-foreground">
                                                    Not enough data
                                                </div>
                                            )}
                                            <ChartContainer
                                                config={flagStatusChartConfig}
                                                className="mx-auto aspect-square max-h-[250px]"
                                            >
                                                <PieChart>
                                                    <Pie
                                                        data={dashboardMetrics.flagStatusData}
                                                        dataKey="flags"
                                                        nameKey="state"
                                                        innerRadius={60}
                                                        outerRadius={80}
                                                        strokeWidth={5}
                                                        label={{ className: 'text-lg font-semibold fill-primary' }}
                                                        labelLine={{ className: 'stroke-1' }}
                                                    >
                                                        <Label
                                                            content={
                                                                <CustomLabel
                                                                    value={
                                                                        healthScore !== false
                                                                            ? (healthScore as HealthScore).score
                                                                            : dashboardMetrics.flagStatusData.reduce(
                                                                                  (sum, item) => sum + item.flags,
                                                                                  0,
                                                                              )
                                                                    }
                                                                    valueClassName={
                                                                        healthScore !== false
                                                                            ? (healthScore as HealthScore).colorClass
                                                                            : undefined
                                                                    }
                                                                    totalLabel={
                                                                        healthScore !== false
                                                                            ? undefined
                                                                            : 'Total Flags'
                                                                    }
                                                                />
                                                            }
                                                            position="center"
                                                            fill="grey"
                                                            style={{
                                                                fontSize: '32px',
                                                                fontWeight: 'bold',
                                                                fontFamily: '"Source Sans 3"',
                                                            }}
                                                        />
                                                    </Pie>
                                                    <ChartLegend content={<ChartLegendContent nameKey="state" />} />
                                                </PieChart>
                                            </ChartContainer>
                                        </CardContent>
                                    </Card>
                                    <Card className="h-80 lg:col-span-2" data-dusk="metric-card-usage">
                                        <CardHeader>
                                            <CardTitle>Usage</CardTitle>
                                            <CardDescription>Last 6 Months</CardDescription>
                                        </CardHeader>
                                        <CardContent>
                                            <ChartContainer config={usageOverTimeChartConfig} className="h-36 w-full">
                                                <AreaChart
                                                    accessibilityLayer
                                                    data={dashboardMetrics.usageOverTimeData}
                                                    margin={{
                                                        left: 12,
                                                        right: 12,
                                                        top: 12,
                                                    }}
                                                    syncId="syncedCharts"
                                                >
                                                    <CartesianGrid vertical={false} />
                                                    <XAxis
                                                        dataKey="month"
                                                        tickLine={false}
                                                        axisLine={false}
                                                        tickMargin={8}
                                                        tickFormatter={(value) => value.slice(0, 3)}
                                                    />
                                                    <YAxis dataKey="active" tickLine={false} axisLine={false} />
                                                    <ChartTooltip cursor={false} content={<ChartTooltipContent />} />
                                                    <defs>
                                                        <linearGradient id="fillActive" x1="0" y1="0" x2="0" y2="1">
                                                            <stop
                                                                offset="5%"
                                                                stopColor="var(--color-active)"
                                                                stopOpacity={0.8}
                                                            />
                                                            <stop
                                                                offset="95%"
                                                                stopColor="var(--color-active)"
                                                                stopOpacity={0.1}
                                                            />
                                                        </linearGradient>
                                                        <linearGradient id="fillInactive" x1="0" y1="0" x2="0" y2="1">
                                                            <stop
                                                                offset="5%"
                                                                stopColor="var(--color-inactive)"
                                                                stopOpacity={0.8}
                                                            />
                                                            <stop
                                                                offset="95%"
                                                                stopColor="var(--color-inactive)"
                                                                stopOpacity={0.1}
                                                            />
                                                        </linearGradient>
                                                    </defs>
                                                    <Area
                                                        dataKey="active"
                                                        type="natural"
                                                        fill="url(#fillActive)"
                                                        fillOpacity={0.4}
                                                        stroke="var(--color-active)"
                                                        stackId="a"
                                                    />
                                                    <Area
                                                        dataKey="inactive"
                                                        type="natural"
                                                        fill="url(#fillInactive)"
                                                        fillOpacity={0.4}
                                                        stroke="var(--color-inactive)"
                                                        stackId="b"
                                                    />
                                                </AreaChart>
                                            </ChartContainer>
                                        </CardContent>
                                        <CardFooter className="flex-col items-start gap-2 text-sm">
                                            {dashboardMetrics.usageOverTimeData.length > 1 && (
                                                <>
                                                    <div className="flex gap-2 font-medium leading-none">
                                                        {dashboardMetrics.usageOverTimeData[
                                                            dashboardMetrics.usageOverTimeData.length - 1
                                                        ].active >
                                                        dashboardMetrics.usageOverTimeData[
                                                            dashboardMetrics.usageOverTimeData.length - 2
                                                        ].active ? (
                                                            <>
                                                                Trending up this month{' '}
                                                                <TrendingUp className="h-4 w-4" />
                                                            </>
                                                        ) : (
                                                            <>
                                                                Trending down this month{' '}
                                                                <TrendingDown className="h-4 w-4" />
                                                            </>
                                                        )}
                                                    </div>
                                                    <div className="text-muted-foreground leading-none">
                                                        Showing all flags for the last 6 months
                                                    </div>
                                                </>
                                            )}
                                        </CardFooter>
                                    </Card>
                                    <Card className="flex h-80 flex-col" data-dusk="metric-card-flag-types">
                                        <CardHeader className="items-center pb-0">
                                            <CardTitle>Flag Types</CardTitle>
                                        </CardHeader>
                                        <CardContent className="flex-1 pb-0 pt-2">
                                            {dashboardMetrics.flagTypeData.total === 0 && (
                                                <div className="flex h-full items-center justify-center text-center text-muted-foreground">
                                                    Not enough data
                                                </div>
                                            )}
                                            {dashboardMetrics.flagTypeData.total > 0 && (
                                                <ChartContainer
                                                    config={flagTypeChartConfig}
                                                    className="mx-auto aspect-square max-h-[250px]"
                                                >
                                                    <PieChart accessibilityLayer>
                                                        <Pie
                                                            data={dashboardMetrics.flagTypeData.data}
                                                            dataKey="flags"
                                                            nameKey="type"
                                                            strokeWidth={5}
                                                            innerRadius={60}
                                                            outerRadius={80}
                                                            label={{ className: 'text-lg font-semibold fill-primary ' }}
                                                            labelLine={{ className: 'stroke-1' }}
                                                        >
                                                            <Label
                                                                content={
                                                                    <CustomLabel
                                                                        value={dashboardMetrics.flagTypeData.total}
                                                                        totalLabel="Total Flags"
                                                                    />
                                                                }
                                                                position="center"
                                                                fill="grey"
                                                                style={{
                                                                    fontSize: '32px',
                                                                    fontWeight: 'bold',
                                                                    fontFamily: '"Source Sans 3"',
                                                                }}
                                                            />
                                                        </Pie>
                                                        <ChartLegend
                                                            content={
                                                                <ChartLegendContent
                                                                    nameKey="label"
                                                                    className="whitespace-nowrap"
                                                                />
                                                            }
                                                        />
                                                    </PieChart>
                                                </ChartContainer>
                                            )}
                                        </CardContent>
                                    </Card>
                                    <Card className="h-80 lg:col-span-2" data-dusk="metric-card-average-age">
                                        <CardHeader>
                                            <CardTitle>Average Flag Age</CardTitle>
                                            <CardDescription>Last 6 Months</CardDescription>
                                        </CardHeader>
                                        <CardContent>
                                            <ChartContainer config={ageChartConfig} className="h-36 w-full">
                                                <LineChart
                                                    accessibilityLayer
                                                    data={dashboardMetrics.ageData}
                                                    margin={{
                                                        left: 0,
                                                        right: 0,
                                                    }}
                                                    syncId="syncedCharts"
                                                >
                                                    <CartesianGrid vertical={false} />
                                                    <XAxis
                                                        dataKey="month"
                                                        tickLine={false}
                                                        axisLine={false}
                                                        tickMargin={8}
                                                        tickFormatter={(value) => value.slice(0, 3)}
                                                    />
                                                    <YAxis dataKey="age" tickLine={false} axisLine={false} />
                                                    <ChartTooltip
                                                        cursor={false}
                                                        content={<ChartTooltipContent hideLabel />}
                                                    />
                                                    <Line
                                                        dataKey="age"
                                                        type="linear"
                                                        stroke="var(--color-age)"
                                                        strokeWidth={2}
                                                        dot={false}
                                                    />
                                                </LineChart>
                                            </ChartContainer>
                                        </CardContent>
                                        <CardFooter className="flex-col items-start gap-2 text-sm">
                                            {dashboardMetrics.ageData.length > 1 && (
                                                <>
                                                    <div className="flex gap-2 font-medium leading-none">
                                                        {dashboardMetrics.ageData[dashboardMetrics.ageData.length - 1]
                                                            .age >
                                                        dashboardMetrics.ageData[dashboardMetrics.ageData.length - 2]
                                                            .age ? (
                                                            <>
                                                                Trending up this month{' '}
                                                                <TrendingUp className="h-4 w-4" />
                                                            </>
                                                        ) : (
                                                            <>
                                                                Trending down this month{' '}
                                                                <TrendingDown className="h-4 w-4" />
                                                            </>
                                                        )}
                                                    </div>
                                                    <div className="text-muted-foreground leading-none">
                                                        Showing average age for the last 6 months
                                                    </div>
                                                </>
                                            )}
                                        </CardFooter>
                                    </Card>
                                </div>
                            </CardGroupContent>
                        </CardGroup>
                        <div className="mt-8 grid grid-cols-1 justify-center gap-2 lg:grid-cols-2">
                            <Card className="flex h-fit flex-col" data-dusk="metric-card-top-usage">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Top Usage</CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 pb-0">
                                    <DataTable
                                        columns={usageColumns}
                                        data={dashboardMetrics.usageData}
                                        sortBy={[
                                            {
                                                id: 'usages',
                                                desc: true,
                                            } as ColumnSort,
                                        ]}
                                        pageSize={5}
                                    ></DataTable>
                                </CardContent>
                            </Card>
                            <Card className="flex h-fit flex-col" data-dusk="metric-card-oldest">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Oldest Flags</CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 pb-0">
                                    <DataTable
                                        columns={oldestColumns}
                                        data={dashboardMetrics.oldestData}
                                        sortBy={[
                                            {
                                                id: 'created_at',
                                                desc: false,
                                            } as ColumnSort,
                                        ]}
                                        pageSize={5}
                                    ></DataTable>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            )}
        </Authenticated>
    );
}
