import { Color } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
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
import { Head } from '@inertiajs/react';
import { DotFilledIcon } from '@radix-ui/react-icons';
import { ColumnDef, ColumnSort, createColumnHelper } from '@tanstack/react-table';
import { ArrowDownLeft, ArrowRight, ArrowUpRight, TrendingDown, TrendingUp } from 'lucide-react';
import {
    Area,
    AreaChart,
    CartesianGrid,
    Label,
    LabelList,
    Line,
    LineChart,
    Pie,
    PieChart,
    XAxis,
    YAxis,
} from 'recharts';

// Dashboard metrics interface
interface DashboardMetricValue {
    value: number;
    previous: {
        value: number;
        type?: 'increase' | 'decrease';
        difference?: number;
        percentage?: number;
    };
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
    flagTypeData: Array<{
        type: string;
        flags: number;
        fill: string;
    }>;
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

let flagTypeChartConfig: Record<string, { label: string }> = {
    type: {
        label: 'Flag Type',
    },
    operational: {
        label: 'Operational',
    },
};

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
        cell: function ({
            row,
        }: {
            row: {
                original: {
                    name: string;
                    application: { name: string; color: Color | string };
                    feature_type: { icon: string; color: Color | string };
                };
            };
        }) {
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
        cell: function ({
            row,
        }: {
            row: {
                original: {
                    name: string;
                    application: { name: string; color: Color | string };
                    feature_type: { icon: string; color: Color | string };
                };
            };
        }) {
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
    flagTypeData: [],
    ageData: [],
    usageOverTimeData: [],
    usageData: [],
    oldestData: [],
};

function chartKey(value: string): string {
    return value.replace(' ', '_').toLowerCase();
}

function MetricCard({ title, metric }: { title: string; metric: DashboardMetricValue }) {
    return (
        <Card className="flex flex-col p-4">
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
                        {metric.previous.type !== undefined && metric.previous.type === 'increase' ? '+' : '-'}
                        {metric.previous.value}
                    </p>
                    <p>this month</p>
                </div>
            </div>
        </Card>
    );
}

export default function Dashboard({ metrics = defaultMetrics }: DashboardProps) {
    // Use provided metrics or fallback to defaults
    let dashboardMetrics = metrics || defaultMetrics;

    dashboardMetrics.flagTypeData = dashboardMetrics.flagTypeData.map(function (item) {
        return {
            ...item,
            fill:
                item.fill.startsWith('#') || item.fill.startsWith('var(--color-')
                    ? item.fill
                    : `var(--color-${item.fill}-400)`,
        };
    });

    dashboardMetrics.flagTypeData.forEach((item) => {
        flagTypeChartConfig[chartKey(item.type)] = {
            label: item.type,
        };
    });

    return (
        <Authenticated header="Dashboard" icon="Gauge">
            <Head title="Dashboard" />

            <div className="mt-4">
                <div className="overflow-hidden">
                    <div className="mx-auto grid grid-cols-2 justify-center gap-6 lg:grid-cols-4">
                        <MetricCard title="Total Flags" metric={dashboardMetrics.totalFlags} />
                        <MetricCard title="Changes" metric={dashboardMetrics.changesMetrics} />
                        <MetricCard title="Created" metric={dashboardMetrics.createdMetrics} />
                        <MetricCard title="Archived" metric={dashboardMetrics.archivedMetrics} />
                    </div>
                    <div className="mt-4 grid grid-cols-1 justify-center gap-6 lg:grid-cols-3">
                        <Card className="flex h-80 flex-col">
                            <CardHeader className="items-center pb-0">
                                <CardTitle>System Health</CardTitle>
                                <CardDescription>Last 30 Days</CardDescription>
                            </CardHeader>
                            <CardContent className="flex-1 pb-0">
                                <ChartContainer
                                    config={flagStatusChartConfig}
                                    className="mx-auto aspect-square max-h-[250px]"
                                >
                                    <PieChart>
                                        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                                        <Pie
                                            data={dashboardMetrics.flagStatusData}
                                            dataKey="flags"
                                            nameKey="state"
                                            innerRadius={60}
                                            strokeWidth={5}
                                        >
                                            <Label
                                                content={({ viewBox }) => {
                                                    if (viewBox && 'cx' in viewBox && 'cy' in viewBox) {
                                                        return (
                                                            <text
                                                                x={viewBox.cx}
                                                                y={viewBox.cy}
                                                                textAnchor="middle"
                                                                dominantBaseline="middle"
                                                            >
                                                                <tspan
                                                                    x={viewBox.cx}
                                                                    y={(viewBox.cy ?? 0) - 14}
                                                                    className="fill-muted-foreground"
                                                                >
                                                                    Health Status
                                                                </tspan>
                                                                <tspan
                                                                    x={viewBox.cx}
                                                                    y={(viewBox.cy || 0) + 14}
                                                                    className="fill-foreground fill-green-400 text-3xl font-bold"
                                                                >
                                                                    OK
                                                                </tspan>
                                                            </text>
                                                        );
                                                    }
                                                }}
                                            />
                                        </Pie>
                                        <ChartLegend content={<ChartLegendContent nameKey="state" />} />
                                    </PieChart>
                                </ChartContainer>
                            </CardContent>
                        </Card>
                        <Card className="h-80 lg:col-span-2">
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
                                        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
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
                                            {dashboardMetrics.ageData[dashboardMetrics.ageData.length - 1].age >
                                            dashboardMetrics.ageData[dashboardMetrics.ageData.length - 2].age ? (
                                                <>
                                                    Trending up this month <TrendingUp className="h-4 w-4" />
                                                </>
                                            ) : (
                                                <>
                                                    Trending down this month <TrendingDown className="h-4 w-4" />
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
                        <Card className="flex h-80 flex-col">
                            <CardHeader className="items-center pb-0">
                                <CardTitle>Flag Types</CardTitle>
                            </CardHeader>
                            <CardContent className="flex-1 pb-0">
                                <ChartContainer
                                    config={flagTypeChartConfig}
                                    className="mx-auto aspect-square max-h-[250px]"
                                >
                                    <PieChart accessibilityLayer>
                                        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                                        <Pie
                                            data={dashboardMetrics.flagTypeData}
                                            dataKey="flags"
                                            nameKey="type"
                                            strokeWidth={5}
                                        >
                                            <LabelList
                                                dataKey="type"
                                                className="fill-background"
                                                stroke="none"
                                                fontSize={12}
                                            />
                                        </Pie>
                                    </PieChart>
                                </ChartContainer>
                            </CardContent>
                        </Card>
                        <Card className="h-80 lg:col-span-2">
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
                                        }}
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
                                                <stop offset="5%" stopColor="var(--color-active)" stopOpacity={0.8} />
                                                <stop offset="95%" stopColor="var(--color-active)" stopOpacity={0.1} />
                                            </linearGradient>
                                            <linearGradient id="fillInactive" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="var(--color-inactive)" stopOpacity={0.8} />
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
                                                    Trending up this month <TrendingUp className="h-4 w-4" />
                                                </>
                                            ) : (
                                                <>
                                                    Trending down this month <TrendingDown className="h-4 w-4" />
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
                    </div>
                    <div className="mt-4 grid grid-cols-1 justify-center gap-6 lg:grid-cols-2">
                        <Card className="flex h-fit flex-col">
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
                        <Card className="flex h-fit flex-col">
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
        </Authenticated>
    );
}
