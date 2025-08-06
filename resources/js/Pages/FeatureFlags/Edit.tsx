import {
    ActivityLogCollection,
    ApplicationCollection,
    EnvironmentCollection,
    FeatureFlag,
    FeatureFlagStatus,
    FeatureTypeCollection,
    PolicyCollection,
    PolicyDefinitionCollection,
    TagCollection,
} from '@/Application';
import DefinitionList, { Definition, DefinitionDescription, DefinitionTerm } from '@/Components/DefinitionList';
import HttpRequestBuilder from '@/Components/HttpRequestBuilder';
import { IconColor } from '@/Components/IconColor';
import Tag from '@/Components/Tag';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from '@/Components/ui/chart';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Switch } from '@/Components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import ActivityLog from '@/Pages/FeatureFlags/Components/ActivityLog';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import StatusEditor from '@/Pages/FeatureFlags/Components/StatusEditor';
import { cn, localDateTime } from '@/lib/utils';
import { useFeatureFlagStore } from '@/stores/featureFlagStore';
import { Head, router, useForm } from '@inertiajs/react';
import { ChevronRight, CircleCheckBig, Info, PlusCircle, TriangleAlert } from 'lucide-react';
import React, { FormEvent, useEffect } from 'react';
import { Area, AreaChart, CartesianGrid, LabelList, Pie, PieChart, XAxis, YAxis } from 'recharts';
import { ulid } from 'ulidx';

function StatusCard({
    status,
    policies,
    featureFlag,
}: {
    status: FeatureFlagStatus;
    policies: PolicyCollection;
    featureFlag: FeatureFlag;
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex flex-row justify-between">
                    <div className="flex flex-row gap-2 items-center">
                        <IconColor color={status.application?.color as string} />
                        {status.application?.display_name}
                        <ChevronRight />
                        <IconColor color={status.environment?.color as string} />
                        {status.environment?.name}
                    </div>
                    <div className="flex flex-row items-center gap-4">
                        <Badge
                            className={cn('w-16 text-center', {
                                'bg-green-600 hover:bg-green-600': status.status,
                                'bg-neutral-500 hover:bg-neutral-500': !status.status,
                            })}
                        >
                            {status.status ? 'Enabled' : 'Disabled'}
                        </Badge>
                        <HttpRequestBuilder
                            status={status}
                            featureFlagName={featureFlag?.name}
                            definition={status.definition as PolicyDefinitionCollection}
                            policies={policies}
                        />
                    </div>
                </CardTitle>
            </CardHeader>
        </Card>
    );
}

export default function Edit({
    featureTypes,
    tags,
    applications,
    environments,
    policies,
    log,
    metrics,
    ...props
}: {
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
    policies: PolicyCollection;
    log: ActivityLogCollection;
    metrics: {
        evaluations: {
            total: number;
            data: {
                date: string;
                active: number;
                inactive: number;
                total: number;
            }[];
        };
        variants: { data: { value: string; count: number; percentage: number; fill: string }[]; total: number };
    };
} & { featureFlag?: FeatureFlag }) {
    // Initialize the feature flag store
    const { featureFlag, setFeatureFlag, addStatus } = useFeatureFlagStore();

    const { setData, put, errors, processing } = useForm<FeatureFlag>(featureFlag as FeatureFlag);
    const [hadFeatureFlags, setHadFeatureFlags] = React.useState<boolean>(false);

    // Metrics filter state with local storage persistence
    const [selectedApplicationId, setSelectedApplicationId] = React.useState<string>(() => {
        if (typeof window !== 'undefined') {
            return localStorage.getItem('metrics-filter-application') || '';
        }
        return '';
    });
    const [selectedEnvironmentId, setSelectedEnvironmentId] = React.useState<string>(() => {
        if (typeof window !== 'undefined') {
            return localStorage.getItem('metrics-filter-environment') || '';
        }
        return '';
    });
    const [metricsLoaded, setMetricsLoaded] = React.useState<boolean>(false);

    // Handle application filter change
    const handleApplicationChange = (applicationId: string) => {
        setSelectedApplicationId(applicationId);
        localStorage.setItem('metrics-filter-application', applicationId);
        setMetricsLoaded(false);
    };

    // Handle environment filter change
    const handleEnvironmentChange = (environmentId: string) => {
        setSelectedEnvironmentId(environmentId);
        localStorage.setItem('metrics-filter-environment', environmentId);
        setMetricsLoaded(false);
    };

    // Load metrics when both filters are selected
    const loadMetrics = () => {
        if (selectedApplicationId && selectedEnvironmentId && featureFlag?.id) {
            router.get(
                route('feature-flags.edit.metrics', { feature_flag: featureFlag.id }),
                {
                    application_id: selectedApplicationId,
                    environment_id: selectedEnvironmentId,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => setMetricsLoaded(true),
                },
            );
        }
    };

    useEffect(() => {
        if (props.featureFlag) {
            setFeatureFlag(props.featureFlag);
        }
    }, [props.featureFlag]);

    useEffect(() => {
        if (featureFlag !== null) {
            setData(featureFlag);
        }
        if (featureFlag === null) {
            setFeatureFlag(props.featureFlag as FeatureFlag);
        }

        if (!hadFeatureFlags) {
            setHadFeatureFlags((featureFlag?.statuses?.length ?? 0) > 0);
        }
    }, [featureFlag]);

    // Automatically load metrics when both filters are selected and we're on the metrics tab
    useEffect(() => {
        if (
            selectedApplicationId &&
            selectedEnvironmentId &&
            featureFlag?.id &&
            route().current('feature-flags.edit.metrics')
        ) {
            loadMetrics();
        }
    }, [selectedApplicationId, selectedEnvironmentId, featureFlag?.id]);

    function changeTab(routeName: string) {
        return function () {
            router.push({
                url: route(routeName, { feature_flag: featureFlag?.id }),
            });
        };
    }

    const submitFeatureFlag = (e: FormEvent) => {
        e.preventDefault();
        put(route('feature-flags.update', { feature_flag: featureFlag?.id as string }), {
            onSuccess: () => {
                changeTab('feature-flags.edit.overview')();
            },
            onError: () => {
                // router.get(route('feature-flags.edit.policy', { feature_flag: featureFlag.id as string }) as string);
            },
            preserveState: true,
        });
    };

    const handleCancel = () => {
        router.get(route('applications.index'));
    };

    let tab = 'overview';

    switch (true) {
        case route().current('feature-flags.edit'):
            tab = 'edit';
            break;
        case route().current('feature-flags.edit.policy'):
            tab = 'policy';
            if (featureFlag?.statuses?.length === 0) {
                addStatus();
            }
            break;
        case route().current('feature-flags.edit.activity'):
            tab = 'activity';
            break;
        case route().current('feature-flags.edit.metrics'):
            tab = 'metrics';
            break;
    }

    const variantMetricsConfig: Record<string, { label: string; fill: string }> = metrics.variants.data.reduce(
        (config, variant) => {
            config[variant.value] = {
                label: variant.value,
                fill: variant.fill,
            };
            return config;
        },
        {} as Record<string, { label: string; fill: string }>,
    );

    const evaluationsMetricsConfig = {
        active: {
            label: 'Active',
            fill: 'var(--color-green-600)',
        },
        inactive: {
            label: 'Inactive',
            fill: 'var(--color-neutral-500)',
        },
        total: {
            label: 'Total',
            fill: 'var(--color-blue-600)',
        },
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Feature Flags', href: route('feature-flags.index'), icon: 'Flag' },
                { name: (featureFlag?.name as string) ?? 'Edit Feature Flag' },
            ]}
        >
            <Head title="Feature Flag | Edit" />
            <div className="mt-6 w-full">
                <div className="flex gap-4">
                    <Tabs value={tab} className="w-full mx-auto rounded-lg">
                        <TabsList className="flex w-full mx-auto">
                            <TabsTrigger
                                value="overview"
                                className="rounded-lg grow px-6 cursor-pointer"
                                onClick={changeTab('feature-flags.edit.overview')}
                                data-dusk="tab-overview"
                            >
                                Overview
                            </TabsTrigger>
                            <TabsTrigger
                                value="edit"
                                className="rounded-lg grow px-6 cursor-pointer"
                                onClick={changeTab('feature-flags.edit')}
                                data-dusk="tab-edit"
                            >
                                Edit
                            </TabsTrigger>
                            <TabsTrigger
                                value="policy"
                                className="rounded-lg grow px-6 cursor-pointer"
                                onClick={changeTab('feature-flags.edit.policy')}
                                data-dusk="tab-configuration"
                            >
                                Configuration
                            </TabsTrigger>
                            <TabsTrigger
                                value="metrics"
                                className="rounded-lg grow px-6 cursor-pointer"
                                onClick={changeTab('feature-flags.edit.metrics')}
                                data-dusk="tab-metrics"
                            >
                                Metrics
                            </TabsTrigger>
                            <TabsTrigger
                                value="activity"
                                className="rounded-lg grow px-6 cursor-pointer"
                                onClick={changeTab('feature-flags.edit.activity')}
                                data-dusk="tab-activity"
                            >
                                Activity Log
                            </TabsTrigger>
                        </TabsList>
                        {featureFlag?.status && (
                            <Alert variant="success" className="mt-4">
                                <CircleCheckBig />
                                <AlertTitle className="prose text-green-500">Feature Flag Active</AlertTitle>
                                <AlertDescription>
                                    <p>
                                        This feature flag is currently active and will be enabled if policy conditions
                                        are met.
                                    </p>
                                </AlertDescription>
                            </Alert>
                        )}
                        {!featureFlag?.status && (
                            <Alert variant="error" className="mt-4">
                                <TriangleAlert />
                                <AlertTitle className="prose text-red-500">Feature Flag Inactive</AlertTitle>
                                <AlertDescription>
                                    <p>This feature flag is currently inactive and will not be enabled for users.</p>
                                </AlertDescription>
                            </Alert>
                        )}
                        <div className="flex flex-row gap-4">
                            <Card className="md:w-1/2 lg:w-1/4 w-full mt-4 h-fit">
                                <CardHeader>
                                    <CardTitle className="flex flex-row items-center gap-4 text-2xl mb-4">
                                        <IconColor
                                            color={featureFlag?.feature_type?.color as string}
                                            icon={featureFlag?.feature_type?.icon}
                                            className="w-8 h-8"
                                        />
                                        {featureFlag?.name}
                                    </CardTitle>
                                    <p className="text-xs text-neutral-500">{featureFlag?.description}</p>
                                </CardHeader>
                                <DefinitionList className="text-card-foreground">
                                    <Definition>
                                        <DefinitionTerm>Type</DefinitionTerm>
                                        <DefinitionDescription>{featureFlag?.feature_type?.name}</DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Last Seen At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {featureFlag?.last_seen_at === null
                                                ? 'never'
                                                : localDateTime(featureFlag?.last_seen_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Updated At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {localDateTime(featureFlag?.updated_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Created At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {localDateTime(featureFlag?.created_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Tags</DefinitionTerm>
                                        <DefinitionDescription>
                                            {featureFlag?.tags?.map((tag) => (
                                                <Tag key={tag.id} tag={tag} className="mb-2 inline-block"></Tag>
                                            ))}
                                            {featureFlag?.tags === null && 'No tags'}
                                        </DefinitionDescription>
                                    </Definition>
                                </DefinitionList>
                            </Card>
                            <div className="mt-2 grow">
                                <TabsContent value="overview" className="flex flex-col gap-4">
                                    {featureFlag?.statuses?.map((status) => (
                                        <StatusCard
                                            key={status.id ?? ulid()}
                                            status={status}
                                            policies={policies}
                                            featureFlag={featureFlag}
                                        />
                                    ))}
                                    {featureFlag?.statuses?.length === 0 && (
                                        <>
                                            <Alert variant="info" className="rounded-lg w-3/4 mx-auto">
                                                <Info className="text-blue-500" />
                                                <AlertTitle>No Policies Defined</AlertTitle>
                                                <AlertDescription>
                                                    <p>
                                                        You must add at least one application policy before this feature
                                                        flag can be used.
                                                    </p>
                                                </AlertDescription>
                                            </Alert>
                                            <div className="w-full">
                                                <Button
                                                    className="mt-4 w-32 mx-auto block"
                                                    onClick={function () {
                                                        changeTab('feature-flags.edit.policy')();
                                                    }}
                                                    data-dusk="button-add-policy"
                                                >
                                                    <PlusCircle className="inline-block mr-2" />
                                                    Add a Policy
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </TabsContent>
                                <TabsContent value="edit">
                                    <Card>
                                        <CardHeader className="flex flex-row justify-between items-center">
                                            <CardTitle>Edit Feature Flag</CardTitle>
                                            <div className="flex flex-row gap-2 items-center">
                                                <Switch
                                                    id="status"
                                                    checked={featureFlag?.status ?? false}
                                                    onCheckedChange={(active) =>
                                                        setFeatureFlag({
                                                            ...featureFlag,
                                                            status: active,
                                                        } as FeatureFlag)
                                                    }
                                                    data-dusk="switch-feature-flag-enabled"
                                                />
                                                Enabled
                                            </div>
                                        </CardHeader>
                                        <CardContent>
                                            <Form
                                                submit={submitFeatureFlag}
                                                tags={tags}
                                                errors={errors}
                                                processing={processing}
                                                onCancel={handleCancel}
                                                featureTypes={featureTypes}
                                            />
                                        </CardContent>
                                    </Card>
                                </TabsContent>
                                <TabsContent value="policy" className="flex flex-col gap-4">
                                    {(featureFlag?.statuses?.length ?? 0) > 0 &&
                                        featureFlag?.statuses?.map((status) => (
                                            <StatusEditor
                                                key={status.id}
                                                status={status}
                                                applications={applications}
                                                environments={environments}
                                                policies={policies}
                                            />
                                        ))}
                                    <div className="flex flex-row justify-between w-full">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            className="w-fit flex flex-row items-center"
                                            onClick={addStatus}
                                            data-dusk="button-add-application"
                                        >
                                            <PlusCircle className="inline-block" />
                                            Add Application
                                        </Button>
                                        {(hadFeatureFlags || (featureFlag?.statuses?.length ?? 0) > 0) && (
                                            <Button
                                                type="button"
                                                onClick={submitFeatureFlag}
                                                className="w-fit block"
                                                data-dusk="button-feature-flag-save"
                                            >
                                                Save
                                            </Button>
                                        )}
                                    </div>
                                </TabsContent>
                                <TabsContent value="metrics" className="flex flex-col gap-4">
                                    {/* Application and Environment Filters */}
                                    <Card>
                                        <CardHeader>
                                            <CardDescription>
                                                Select both an application and environment to load metric data.
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="flex flex-row gap-4">
                                                <div className="flex-1">
                                                    <Label
                                                        className="text-sm font-medium mb-2 block"
                                                        htmlFor="application"
                                                    >
                                                        Application
                                                    </Label>
                                                    <Select
                                                        value={selectedApplicationId}
                                                        onValueChange={handleApplicationChange}
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                placeholder="Select an application"
                                                                id="application"
                                                            />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {applications.map((application) => (
                                                                <SelectItem
                                                                    key={application.id}
                                                                    value={application.id || ''}
                                                                >
                                                                    <div className="flex items-center gap-2">
                                                                        <IconColor color={application.color} />
                                                                        {application.display_name}
                                                                    </div>
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                <div className="flex-1">
                                                    <Label
                                                        className="text-sm font-medium mb-2 block"
                                                        htmlFor="environment"
                                                    >
                                                        Environment
                                                    </Label>
                                                    <Select
                                                        value={selectedEnvironmentId}
                                                        onValueChange={handleEnvironmentChange}
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                placeholder="Select an environment"
                                                                id="environment"
                                                            />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {environments.map((environment) => (
                                                                <SelectItem
                                                                    key={environment.id}
                                                                    value={environment.id || ''}
                                                                >
                                                                    <div className="flex items-center gap-2">
                                                                        <IconColor color={environment.color} />
                                                                        {environment.name}
                                                                    </div>
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Metrics Charts - Only show when both filters are selected and metrics are loaded */}
                                    {selectedApplicationId && selectedEnvironmentId && metricsLoaded && (
                                        <div className="flex flex-row gap-4">
                                            <Card className="w-1/2 flex flex-col gap-2">
                                                <CardHeader className="items-center pb-0">
                                                    <CardTitle>Evaluations</CardTitle>
                                                    <CardDescription>Last 30 Days</CardDescription>
                                                </CardHeader>
                                                <CardContent className="flex-1 pb-0">
                                                    {metrics.evaluations.total > 0 && (
                                                        <ChartContainer
                                                            config={evaluationsMetricsConfig}
                                                            className="max-h-[320px]"
                                                        >
                                                            <AreaChart
                                                                data={metrics.evaluations.data}
                                                                margin={{
                                                                    left: 12,
                                                                    right: 12,
                                                                    top: 12,
                                                                    bottom: 32,
                                                                }}
                                                            >
                                                                <CartesianGrid vertical={false} />
                                                                <XAxis
                                                                    dataKey="date"
                                                                    tickLine={false}
                                                                    axisLine={false}
                                                                    hide={true}
                                                                />
                                                                <YAxis
                                                                    dataKey="total"
                                                                    tickLine={false}
                                                                    axisLine={false}
                                                                    width={20}
                                                                />
                                                                <ChartTooltip
                                                                    cursor={false}
                                                                    content={<ChartTooltipContent />}
                                                                />
                                                                <Area
                                                                    dataKey="total"
                                                                    type="linear"
                                                                    fill="var(--chart-3)"
                                                                    fillOpacity={0.4}
                                                                    stroke="var(--chart-2)"
                                                                    stackId="2"
                                                                />
                                                                <Area
                                                                    dataKey="active"
                                                                    type="linear"
                                                                    fill="var(--chart-1)"
                                                                    fillOpacity={0.4}
                                                                    stroke="var(--chart-1)"
                                                                    stackId="1"
                                                                />
                                                                <Area
                                                                    dataKey="inactive"
                                                                    type="linear"
                                                                    fill="var(--chart-2)"
                                                                    fillOpacity={0.4}
                                                                    stroke="var(--chart-2)"
                                                                    stackId="1"
                                                                />
                                                            </AreaChart>
                                                        </ChartContainer>
                                                    )}
                                                    {metrics.evaluations.total === 0 && (
                                                        <p className="flex items-center h-full w-fit mx-auto text-xs">
                                                            No evaluations have been recorded for this feature flag in
                                                            the last 30 days.
                                                        </p>
                                                    )}
                                                </CardContent>
                                                {metrics.evaluations.total > 0 && (
                                                    <CardFooter>Total: {metrics.evaluations.total}</CardFooter>
                                                )}
                                            </Card>
                                            <Card className="w-1/2">
                                                <CardHeader className="items-center pb-0">
                                                    <CardTitle>Variant Results</CardTitle>
                                                    <CardDescription>Last 30 Days</CardDescription>
                                                </CardHeader>
                                                <CardContent className="flex-1 pb-0">
                                                    {metrics.variants.total > 0 && (
                                                        <ChartContainer
                                                            config={variantMetricsConfig}
                                                            className="mx-auto aspect-square max-h-[250px]"
                                                        >
                                                            <PieChart accessibilityLayer>
                                                                <ChartTooltip
                                                                    cursor={false}
                                                                    content={<ChartTooltipContent hideLabel />}
                                                                />
                                                                <Pie
                                                                    data={metrics.variants.data}
                                                                    dataKey="count"
                                                                    nameKey="value"
                                                                    strokeWidth={5}
                                                                >
                                                                    <LabelList
                                                                        dataKey="percentage"
                                                                        stroke="none"
                                                                        className="fill-background"
                                                                        fontSize={12}
                                                                        formatter={(value: number) => `${value}%`}
                                                                    />
                                                                </Pie>
                                                                <ChartLegend
                                                                    content={<ChartLegendContent nameKey="value" />}
                                                                />
                                                            </PieChart>
                                                        </ChartContainer>
                                                    )}
                                                    {metrics.variants.total === 0 && (
                                                        <p className="flex items-center h-full w-fit mx-auto text-xs">
                                                            No variant data has been recorded for this feature flag in
                                                            the last 30 days.
                                                        </p>
                                                    )}
                                                </CardContent>
                                                {metrics.variants.total > 0 && (
                                                    <CardFooter>Total: {metrics.variants.total}</CardFooter>
                                                )}
                                            </Card>
                                        </div>
                                    )}

                                    {/* Message when filters are not selected */}
                                    {(!selectedApplicationId || !selectedEnvironmentId) && (
                                        <Card>
                                            <CardContent className="flex items-center justify-center h-64">
                                                <p className="text-center text-muted-foreground">
                                                    Please select both an application and environment to view metrics
                                                    data.
                                                </p>
                                            </CardContent>
                                        </Card>
                                    )}

                                    {/* Loading message */}
                                    {selectedApplicationId && selectedEnvironmentId && !metricsLoaded && (
                                        <Card>
                                            <CardContent className="flex items-center justify-center h-64">
                                                <p className="text-center text-muted-foreground">
                                                    Loading metrics data for the selected application and environment...
                                                </p>
                                            </CardContent>
                                        </Card>
                                    )}
                                </TabsContent>
                                <TabsContent value="activity" className="flex flex-col gap-4">
                                    <ActivityLog log={log} />
                                </TabsContent>
                            </div>
                        </div>
                    </Tabs>
                </div>
            </div>
        </Authenticated>
    );
}
