import CopyToClipboard from '@/Components/CopyToClipboard';
import HttpRequest from '@/Components/HttpRequest';
import { IconColor } from '@/Components/IconColor';
import { Accordion, AccordionContent, AccordionItem } from '@/Components/ui/accordion';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
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
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { DotFilledIcon } from '@radix-ui/react-icons';
import { ColumnDef, ColumnSort, createColumnHelper } from '@tanstack/react-table';
import axios from 'axios';
import { ArrowDownLeft, ArrowRight, ArrowUpRight, HeartPulse, TrendingDown, TrendingUp } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Light as SyntaxHighlighter } from 'react-syntax-highlighter';
import php from 'react-syntax-highlighter/dist/esm/languages/hljs/php';
import docco from 'react-syntax-highlighter/dist/esm/styles/hljs/docco';
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

    if (ratio >= 0.8) {
        score = 'Excellent';
        colorClass = 'fill-green-500';
    } else if (ratio >= 0.6 || totalFlags < 10) {
        score = 'Good';
        colorClass = 'fill-green-400';
    } else if (ratio >= 0.4) {
        score = 'Fair';
        colorClass = 'fill-yellow-500';
    } else if (ratio >= 0.2) {
        score = 'Poor';
        colorClass = 'fill-orange-500';
    } else {
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

async function completeOnboarding() {
    await axios.post(route('api.onboarding.complete'));
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
        };
    });

    console.log(dashboardMetrics.ageData);

    dashboardMetrics.flagStatusData = [...dashboardMetrics.flagStatusData];
    const healthScore = calculateHealthScore(dashboardMetrics.flagStatusData);

    const [showOnboarding, setShowOnboarding] = useState(onboarding);
    const [onboardingStep, setOnboardingStep] = useState('install');
    const [onboardingStepsComplete, setOnboardingStepsComplete] = useState<string[]>([]);
    const [apiOnboardingStatus, setApiOnboardingStatus] = useState<boolean | null>(null);

    const interval = useRef<any>(null);

    useEffect(() => {
        if (!showOnboarding) return;

        const pollOnboardingStatus = async () => {
            try {
                const response = await fetch('/api/onboarding/status');
                const data = await response.json();
                setApiOnboardingStatus(data.onboarding);
            } catch (error) {
                console.error('Failed to fetch onboarding status:', error);
            }
        };

        interval.current = setInterval(pollOnboardingStatus, 3000);
    }, [showOnboarding]);

    useEffect(() => {
        if (apiOnboardingStatus === false || showOnboarding === false) {
            clearInterval(interval.current);
        }
    }, [apiOnboardingStatus, showOnboarding]);

    const CustomLabel = ({
        viewBox,
        total = 0,
        totalLabel = 'Total',
    }: {
        viewBox?: {
            cx: number;
            cy: number;
            startAngle: number;
            endAngle: number;
            innerRadius: number;
            outerRadius: number;
        };
        total?: number;
        totalLabel?: string;
    }) => {
        const { cx, cy } = viewBox as { cx: number; cy: number };
        return (
            <>
                <text x={cx - `${total}`.length * 12} y={cy + 10}>
                    <tspan
                        className="text-primary"
                        style={{
                            fontWeight: 700,
                            fontSize: '4em',
                            fontFamily: '"Source Sans 3", sans-serif',
                        }}
                    >
                        {total}
                    </tspan>
                </text>
                <text x={cx - 20} y={cy + 20}>
                    <tspan
                        className="fill-primary/80"
                        style={{
                            fontSize: '0.8em',
                            fontFamily: '"Source Sans 3", sans-serif',
                        }}
                    >
                        {totalLabel}
                    </tspan>
                </text>
            </>
        );
    };

    return (
        <Authenticated header="Dashboard" icon="Gauge">
            <Head title="Dashboard" />

            {showOnboarding && (
                <Dialog defaultOpen open={showOnboarding} onOpenChange={setShowOnboarding}>
                    <DialogContent className="w-full" data-dusk="onboarding-dialog">
                        <DialogHeader>
                            <DialogTitle>
                                <h2 className="text-primary">Get Started</h2>
                            </DialogTitle>
                            <DialogDescription className="w-full">
                                <Accordion type="single" value={onboardingStep} onValueChange={setOnboardingStep}>
                                    <AccordionItem value="install" className="border-0 mt-4">
                                        <div onClick={() => setOnboardingStep('install')}>
                                            <div className="flex flex-row justify-between w-full">
                                                <span className="text-md font-bold">Install Beacon</span>
                                                {onboardingStepsComplete.includes('install') && (
                                                    <Badge
                                                        variant="default"
                                                        className="bg-success hover:bg-success"
                                                        data-dusk="done-install"
                                                    >
                                                        Done
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                        <AccordionContent className="py-4 flex flex-col gap-4 w-full">
                                            <p>Install the Beacon Pennant Driver package to get started:</p>
                                            <div className="flex flex-row justify-between border-1 border-muted-foreground rounded-md p-2">
                                                <code>
                                                    <pre className="whitespace-pre-wrap font-mono">
                                                        composer require beacon-hq/pennant-driver
                                                    </pre>
                                                </code>
                                                <CopyToClipboard text="composer require beacon-hq/pennant-driver" />
                                            </div>
                                            <div className="flex flex-row justify-end">
                                                <Button
                                                    onClick={function () {
                                                        setOnboardingStepsComplete([
                                                            ...onboardingStepsComplete,
                                                            'install',
                                                        ]);
                                                        setOnboardingStep('config');
                                                    }}
                                                    data-dusk="next-config"
                                                >
                                                    Next
                                                </Button>
                                            </div>
                                        </AccordionContent>
                                    </AccordionItem>
                                    <AccordionItem value="config" className="border-0 mt-4">
                                        <div onClick={() => setOnboardingStep('config')}>
                                            <div className="flex flex-row justify-between w-full">
                                                <span className="text-md font-bold">Edit Configuration</span>
                                                {onboardingStepsComplete.includes('config') && (
                                                    <Badge
                                                        variant="default"
                                                        className="bg-success hover:bg-success"
                                                        data-dusk="done-config"
                                                    >
                                                        Done
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                        <AccordionContent className="py-4 flex flex-col gap-4">
                                            <p>Add your Beacon access token to your applications `.env` file:</p>
                                            <div className="flex flex-row justify-between border-1 border-muted-foreground rounded-md p-2 w-full">
                                                <SyntaxHighlighter
                                                    language="dotenv"
                                                    style={docco}
                                                    customStyle={{
                                                        margin: 0,
                                                        padding: '0.5rem',
                                                        background: 'transparent',
                                                        fontSize: '0.875rem',
                                                        width: '400px',
                                                        overflow: 'auto',
                                                    }}
                                                >
                                                    {`PENNANT_STORE=beacon\nBEACON_ACCESS_TOKEN="${accessToken}"`}
                                                </SyntaxHighlighter>
                                                <CopyToClipboard
                                                    text={`PENNANT_STORE=beacon\nBEACON_ACCESS_TOKEN="${accessToken}"`}
                                                />
                                            </div>
                                            <div className="flex flex-row justify-between">
                                                <Button
                                                    variant="secondary"
                                                    onClick={function () {
                                                        setOnboardingStep('install');
                                                    }}
                                                    type="button"
                                                >
                                                    Back
                                                </Button>
                                                <div className="flex flex-row justify-end">
                                                    <Button
                                                        onClick={function () {
                                                            setOnboardingStepsComplete([
                                                                ...onboardingStepsComplete,
                                                                'config',
                                                            ]);
                                                            setOnboardingStep('integration');
                                                        }}
                                                        data-dusk="next-integration"
                                                    >
                                                        Next
                                                    </Button>
                                                </div>
                                            </div>
                                        </AccordionContent>
                                    </AccordionItem>
                                    <AccordionItem value="integration" className="border-0 mt-4">
                                        <div onClick={() => setOnboardingStep('integration')}>
                                            <div className="flex flex-row justify-between w-full">
                                                <span className="text-md font-bold">Activate Beacon</span>
                                                {onboardingStepsComplete.includes('integration') && (
                                                    <Badge variant="default" className="bg-success hover:bg-success">
                                                        Complete
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                        <AccordionContent className="py-4 flex flex-col gap-4">
                                            <p>Confirm your integration by sending a feature flag request:</p>
                                            <HttpRequest
                                                apiKey={accessToken as string}
                                                contextValues={{
                                                    app_name: 'Beacon Onboarding',
                                                    environment: 'production',
                                                }}
                                                featureFlagName="test-flag"
                                                fullWidth={true}
                                            />
                                            <div className="flex flex-row justify-between">
                                                <Button
                                                    variant="secondary"
                                                    onClick={function () {
                                                        setOnboardingStep('config');
                                                    }}
                                                    type="button"
                                                >
                                                    Back
                                                </Button>
                                                <Button
                                                    variant="default"
                                                    disabled={apiOnboardingStatus !== false}
                                                    className="bg-success hover:bg-success/80"
                                                    onClick={async () => {
                                                        setShowOnboarding(false);
                                                        await completeOnboarding();
                                                        router.reload();
                                                    }}
                                                    data-dusk="finish-onboarding"
                                                >
                                                    {apiOnboardingStatus && <HeartPulse className="animate-pulse" />}
                                                    {apiOnboardingStatus === false ? 'Finish' : 'Waiting…'}
                                                </Button>
                                            </div>
                                        </AccordionContent>
                                    </AccordionItem>
                                </Accordion>
                                <Separator className="my-4" />
                                <div className="w-full text-right">
                                    <Button
                                        variant="link"
                                        onClick={async function () {
                                            setShowOnboarding(false);
                                            await completeOnboarding();
                                        }}
                                        className="text-xs text-primary/60"
                                        data-dusk="skip-onboarding"
                                    >
                                        Skip
                                    </Button>
                                </div>
                            </DialogDescription>
                        </DialogHeader>
                    </DialogContent>
                </Dialog>
            )}

            {!showOnboarding && (
                <div className="mt-4" data-dusk="dashboard">
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
                                <CardContent className="flex-1 pb-0 pt-3">
                                    {!healthScore && (
                                        <div className="flex h-full items-center justify-center text-center text-muted-foreground">
                                            Not enough data
                                        </div>
                                    )}
                                    {healthScore && (
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
                                                                total={dashboardMetrics.flagStatusData.reduce(
                                                                    (sum, item) => sum + item.flags,
                                                                    0,
                                                                )}
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
                                                <ChartLegend content={<ChartLegendContent nameKey="state" />} />
                                            </PieChart>
                                        </ChartContainer>
                                    )}
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
                                                                total={dashboardMetrics.flagTypeData.total}
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
                                                <ChartLegend content={<ChartLegendContent nameKey="label" />} />
                                            </PieChart>
                                        </ChartContainer>
                                    )}
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
                                                top: 12,
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
            )}
        </Authenticated>
    );
}
