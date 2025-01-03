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
import { Area, AreaChart, CartesianGrid, Label, Line, LineChart, Pie, PieChart, XAxis, YAxis } from 'recharts';

const flagStatusData = [
    { state: 'active', flags: 275, fill: 'var(--color-active)' },
    { state: 'stale', flags: 200, fill: 'var(--color-stale)' },
    { state: 'inactive', flags: 35, fill: 'var(--color-inactive)' },
];
const flagStatusChartConfig = {
    flags: {
        label: 'Known Flags',
    },
    active: {
        label: 'Active',
        color: 'hsl(var(--chart-1))',
    },
    stale: {
        label: 'Stale',
        color: 'hsl(var(--chart-2))',
    },
    inactive: {
        label: 'Inactive',
        color: 'hsl(var(--chart-3))',
    },
} satisfies ChartConfig;

const flagTypeData = [
    { type: 'release', flags: 160, fill: 'var(--color-release)' },
    { type: 'kill', flags: 30, fill: 'var(--color-kill)' },
    { type: 'experiment', flags: 213, fill: 'var(--color-experiment)' },
];
const flagTypeChartConfig = {
    release: {
        label: 'Release',
        color: 'hsl(var(--chart-1))',
    },
    kill: {
        label: 'Kill Switch',
        color: 'hsl(var(--chart-2))',
    },
    experiment: {
        label: 'Experiment',
        color: 'hsl(var(--chart-3))',
    },
} satisfies ChartConfig;

const ageData = [
    { month: 'January', age: 186 },
    { month: 'February', age: 305 },
    { month: 'March', age: 237 },
    { month: 'April', age: 73 },
    { month: 'May', age: 209 },
    { month: 'June', age: 214 },
];
const ageChartConfig = {
    age: {
        label: 'Days Old',
        color: 'hsl(var(--chart-1))',
    },
} satisfies ChartConfig;

const usageOverTimeData = [
    { month: 'January', active: 186, inactive: 20 },
    { month: 'February', active: 305, inactive: 10 },
    { month: 'March', active: 237, inactive: 30 },
    { month: 'April', active: 73, inactive: 40 },
    { month: 'May', active: 209, inactive: 50 },
    { month: 'June', active: 214, inactive: 60 },
];
const usageOverTimeChartConfig = {
    active: {
        label: 'Active',
        color: 'hsl(var(--chart-1))',
    },
    inactive: {
        label: 'Inactive',
        color: 'hsl(var(--chart-2))',
    },
} satisfies ChartConfig;

const columnHelper = createColumnHelper();

const usageColumns: ColumnDef<any, any>[] = [
    columnHelper.display({
        id: 'flag',
        header: () => <div className="text-left">Flag</div>,
        cell: function ({ row }) {
            return (
                <div className="flex">
                    {(row.original as any).application}
                    <ArrowRight className="h-4 w-4 pt-2 align-middle" />
                    {(row.original as any).name}
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

const usageData = [
    {
        application: 'Application A',
        name: 'Feature A',
        usages: 500,
    },
    {
        application: 'Application A',
        name: 'Feature F',
        usages: 600,
    },
    {
        application: 'Application B',
        name: 'Feature C',
        usages: 700,
    },
    {
        application: 'Application B',
        name: 'Feature H',
        usages: 800,
    },
    {
        application: 'Application C',
        name: 'Feature D',
        usages: 900,
    },
    {
        application: 'Application C',
        name: 'Feature J',
        usages: 1000,
    },
];

const oldestColumns: ColumnDef<any, any>[] = [
    columnHelper.display({
        id: 'flag',
        header: () => <div className="text-left">Flag</div>,
        cell: function ({ row }) {
            return (
                <div className="flex">
                    {(row.original as any).application}
                    <ArrowRight className="h-4 w-4 pt-2 align-middle" />
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

const oldestData = [
    {
        application: 'Application A',
        name: 'Feature A',
        created_at: '2021-01-01',
    },
    {
        application: 'Application A',
        name: 'Feature F',
        created_at: '2023-04-18',
    },
    {
        application: 'Application B',
        name: 'Feature C',
        created_at: '2024-12-24',
    },
    {
        application: 'Application B',
        name: 'Feature H',
        created_at: '2023-01-16',
    },
    {
        application: 'Application C',
        name: 'Feature D',
        created_at: '2022-07-04',
    },
    {
        application: 'Application C',
        name: 'Feature J',
        created_at: '2021-01-01',
    },
];

export default function Dashboard() {
    return (
        <Authenticated header="Dashboard">
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <div className="mx-auto grid grid-cols-2 justify-center gap-4 lg:grid-cols-4">
                            <Card className="flex flex-col p-4">
                                <h2 className="mb-4 text-2xl font-bold">Total Flags</h2>
                                <div className="grid grid-cols-2">
                                    <p className="text-4xl">510</p>
                                </div>
                            </Card>
                            <Card className="flex flex-col p-4">
                                <h2 className="mb-4 text-2xl font-bold">Changes</h2>
                                <div className="grid grid-cols-2">
                                    <p className="text-4xl">16</p>
                                    <div className="">
                                        <p>
                                            <ArrowUpRight className="mr-4 inline-block h-6 w-6 text-green-400" />
                                            +4
                                        </p>
                                        <p>this month</p>
                                    </div>
                                </div>
                            </Card>
                            <Card className="flex flex-col p-4">
                                <h2 className="mb-4 text-2xl font-bold">Created</h2>
                                <div className="grid grid-cols-2">
                                    <p className="text-4xl">12</p>
                                    <div className="">
                                        <p>
                                            <ArrowDownLeft className="mr-4 inline-block h-6 w-6 text-red-400" />
                                            -2
                                        </p>
                                        <p>this month</p>
                                    </div>
                                </div>
                            </Card>
                            <Card className="flex flex-col p-4">
                                <h2 className="mb-4 text-2xl font-bold">Archived</h2>
                                <div className="grid grid-cols-2">
                                    <p className="text-4xl">4</p>
                                    <div className="">
                                        <p className="grid grid-cols-2">
                                            <DotFilledIcon className="mr-4 inline-block h-6 w-6 text-neutral-400" />
                                            +0
                                        </p>
                                        <p>this month</p>
                                    </div>
                                </div>
                            </Card>
                        </div>
                        <div className="mt-4 grid grid-cols-1 justify-center gap-4 lg:grid-cols-3">
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
                                                data={flagStatusData}
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
                                    <CardDescription>January - June 2024</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ChartContainer config={ageChartConfig} className="h-36 w-full">
                                        <LineChart
                                            accessibilityLayer
                                            data={ageData}
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
                                    <div className="flex gap-2 font-medium leading-none">
                                        Trending up by 5.2% this month <TrendingUp className="h-4 w-4" />
                                    </div>
                                    <div className="text-muted-foreground leading-none">
                                        Showing total visitors for the last 6 months
                                    </div>
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
                                        <PieChart>
                                            <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                                            <Pie
                                                data={flagTypeData}
                                                dataKey="flags"
                                                nameKey="type"
                                                strokeWidth={5}
                                            ></Pie>
                                            <ChartLegend content={<ChartLegendContent />} />
                                        </PieChart>
                                    </ChartContainer>
                                </CardContent>
                            </Card>
                            <Card className="flex h-80 flex-col">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Top Usage</CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 overflow-x-scroll pb-0">
                                    <DataTable
                                        columns={usageColumns}
                                        data={usageData}
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
                            <Card className="flex h-80 flex-col">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Oldest Flags</CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 overflow-x-scroll pb-0">
                                    <DataTable
                                        columns={oldestColumns}
                                        data={oldestData}
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
                            <Card className="h-80 lg:col-span-3">
                                <CardHeader>
                                    <CardTitle>Usage</CardTitle>
                                    <CardDescription>January - June 2024</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ChartContainer config={usageOverTimeChartConfig} className="h-36 w-full">
                                        <AreaChart
                                            accessibilityLayer
                                            data={usageOverTimeData}
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
                                    <div className="flex gap-2 font-medium leading-none">
                                        Trending down by 18.9% this month <TrendingDown className="h-4 w-4" />
                                    </div>
                                    <div className="text-muted-foreground leading-none">
                                        Showing all requests for the last 6 months
                                    </div>
                                </CardFooter>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
