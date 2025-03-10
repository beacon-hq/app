import {
    ApplicationCollection,
    EnvironmentCollection,
    FeatureFlag,
    FeatureFlagStatus,
    FeatureTypeCollection,
    PolicyCollection,
    TagCollection,
} from '@/Application';
import DefinitionList, { Definition, DefinitionDescription, DefinitionTerm } from '@/Components/DefinitionList';
import { IconColor } from '@/Components/IconColor';
import Tag from '@/Components/Tag';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Switch } from '@/Components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import StatusEditor from '@/Pages/FeatureFlags/Components/StatusEditor';
import { cn, localDateTime } from '@/lib/utils';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ChevronRight, CircleCheckBig, Info, PlusCircle, TriangleAlert } from 'lucide-react';
import React, { FormEvent } from 'react';
import { ulid } from 'ulidx';

export default function Edit({
    featureFlag,
    featureTypes,
    tags,
    applications,
    environments,
    policies,
}: {
    featureFlag: FeatureFlag;
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
    policies: PolicyCollection;
}) {
    function changeTab(routeName: string) {
        return () =>
            router.push({
                url: route(routeName, { slug: featureFlag.slug }),
            });
    }

    const { data, setData, put, errors, processing } = useForm<FeatureFlag>({
        slug: featureFlag.slug,
        name: featureFlag.name,
        description: featureFlag.description,
        feature_type: featureFlag.feature_type,
        tags: featureFlag.tags,
        statuses: featureFlag.statuses,
        last_seen_at: featureFlag.last_seen_at,
        created_at: null,
        updated_at: null,
        status: featureFlag.status ?? false,
    });

    const submitFeatureFlag = (e: FormEvent) => {
        e.preventDefault();
        put(route('feature-flags.update', { slug: featureFlag.slug }));
        changeTab('feature-flags.edit.overview')();
    };

    const submitPolicy = (e: FormEvent) => {
        e.preventDefault();

        put(route('feature-flags.update', { slug: featureFlag.slug }));
        changeTab('feature-flags.edit.overview')();
    };

    const handleCancel = () => {
        router.get(route('applications.index'));
    };

    const addStatus = () => {
        setData('statuses', [
            ...(data.statuses ?? []),
            {
                definition: [],
                application: null,
                environment: null,
                feature_flag: null,
                status: false,
                id: ulid(),
            },
        ]);
    };

    let tab = 'overview';

    if (route().current('feature-flags.edit')) {
        tab = 'edit';
    }

    if (route().current('feature-flags.edit.policy')) {
        tab = 'policy';

        if (!data.statuses?.length) {
            addStatus();
        }
    }

    const onStatusChange = (status: FeatureFlagStatus) => {
        const statuses = [
            ...(data.statuses?.map(function (currentStatus) {
                if (status?.id == currentStatus.id) {
                    return status;
                } else {
                    return currentStatus;
                }
            }) ?? []),
        ];

        if (statuses.length === 0) {
            addStatus();
        }

        setData('statuses', statuses);
    };

    const onDelete = (status: FeatureFlagStatus) => {
        setData('statuses', [...(data.statuses?.filter((currentStatus) => status?.id != currentStatus.id) ?? [])]);
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Feature Flags', href: route('feature-flags.index'), icon: 'Flag' },
                { name: featureFlag.name as string },
            ]}
        >
            <Head title="Feature Flag | Edit" />
            <div className="mt-6 w-full">
                <div className="flex gap-4">
                    <Tabs defaultValue={tab} className="w-full mx-auto rounded-lg">
                        <TabsList className="flex w-full mx-auto">
                            <TabsTrigger
                                value="overview"
                                className="rounded-lg grow px-6"
                                onClick={changeTab('feature-flags.edit.overview')}
                            >
                                Overview
                            </TabsTrigger>
                            <TabsTrigger
                                value="edit"
                                className="rounded-lg grow px-6"
                                onClick={changeTab('feature-flags.edit')}
                            >
                                Edit
                            </TabsTrigger>
                            <TabsTrigger
                                value="policy"
                                className="rounded-lg grow px-6"
                                onClick={changeTab('feature-flags.edit.policy')}
                            >
                                Status
                            </TabsTrigger>
                        </TabsList>
                        {featureFlag.status && (
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
                        {!featureFlag.status && (
                            <Alert variant="error" className="mt-4">
                                <TriangleAlert />
                                <AlertTitle className="prose text-red-500">Feature Flag Inactive</AlertTitle>
                                <AlertDescription>
                                    <p>This feature flag is currently inactive and will not be displayed to users.</p>
                                </AlertDescription>
                            </Alert>
                        )}
                        <div className="flex flex-row gap-4">
                            <Card className="md:w-1/2 lg:w-1/4 w-full mt-4 h-fit">
                                <CardHeader>
                                    <CardTitle className="flex flex-row items-center gap-4 text-2xl mb-4">
                                        <IconColor
                                            color={featureFlag.feature_type?.color as string}
                                            icon={featureFlag.feature_type?.icon}
                                            className="w-8 h-8"
                                        />
                                        {featureFlag.name}
                                    </CardTitle>
                                    <p className="text-xs text-neutral-500">{featureFlag.description}</p>
                                </CardHeader>
                                <DefinitionList>
                                    <Definition>
                                        <DefinitionTerm>Type</DefinitionTerm>
                                        <DefinitionDescription>{featureFlag.feature_type?.name}</DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Last Seen At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {featureFlag.last_seen_at === null
                                                ? 'never'
                                                : localDateTime(featureFlag.last_seen_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Updated At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {localDateTime(featureFlag.updated_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Created At</DefinitionTerm>
                                        <DefinitionDescription>
                                            {localDateTime(featureFlag.created_at)}
                                        </DefinitionDescription>
                                    </Definition>
                                    <Definition>
                                        <DefinitionTerm>Tags</DefinitionTerm>
                                        <DefinitionDescription>
                                            {featureFlag.tags?.map((tag) => (
                                                <Tag key={tag.slug} tag={tag} className="mb-2 inline-block"></Tag>
                                            ))}
                                            {featureFlag.tags === null && 'No tags'}
                                        </DefinitionDescription>
                                    </Definition>
                                </DefinitionList>
                            </Card>
                            <div className="mt-2 grow">
                                <TabsContent value="overview" className="flex flex-col gap-4">
                                    {featureFlag.statuses?.map((status, index) => (
                                        <Card key={index}>
                                            <CardHeader>
                                                <CardTitle className="flex flex-row justify-between">
                                                    <div className="flex flex-row gap-2 items-center">
                                                        <IconColor color={status.application?.color as string} />
                                                        {status.application?.display_name}
                                                        <ChevronRight />
                                                        <IconColor color={status.environment?.color as string} />
                                                        {status.environment?.name}
                                                    </div>
                                                    <div>
                                                        <Badge
                                                            className={cn('w-16 text-center', {
                                                                'bg-green-600 hover:bg-green-600': status.status,
                                                                'bg-neutral-500 hover:bg-neutral-500': !status.status,
                                                            })}
                                                        >
                                                            {status.status ? 'Enabled' : 'Disabled'}
                                                        </Badge>
                                                    </div>
                                                </CardTitle>
                                            </CardHeader>
                                        </Card>
                                    ))}
                                    {featureFlag.statuses?.length === 0 && (
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
                                                <Button className="mt-4 w-32 mx-auto block">
                                                    <Link
                                                        href={route('feature-flags.edit.policy', {
                                                            slug: featureFlag.slug,
                                                        })}
                                                        className="flex items-center"
                                                    >
                                                        <PlusCircle className="inline-block mr-2" />
                                                        Add a Policy
                                                    </Link>
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
                                                    checked={data.status ?? false}
                                                    onCheckedChange={(active) => setData('status', active)}
                                                />
                                                Enabled
                                            </div>
                                        </CardHeader>
                                        <CardContent>
                                            <Form
                                                submit={submitFeatureFlag}
                                                data={data}
                                                tags={tags}
                                                setData={setData}
                                                errors={errors}
                                                processing={processing}
                                                onCancel={handleCancel}
                                                featureTypes={featureTypes}
                                            />
                                        </CardContent>
                                    </Card>
                                </TabsContent>
                                <TabsContent value="policy" className="flex flex-col gap-4">
                                    {data.statuses?.length === 0 && (
                                        <StatusEditor
                                            applications={applications}
                                            environments={environments}
                                            policies={policies}
                                            onStatusChange={onStatusChange}
                                            onDelete={onDelete}
                                        />
                                    )}
                                    {data.statuses?.map((status, index) => (
                                        <StatusEditor
                                            key={status.id}
                                            status={status}
                                            applications={applications}
                                            environments={environments}
                                            policies={policies}
                                            onStatusChange={onStatusChange}
                                            onDelete={onDelete}
                                        />
                                    ))}
                                    <div className="flex flex-row justify-between w-full">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            className="w-fit flex flex-row items-center"
                                            onClick={addStatus}
                                        >
                                            <PlusCircle className="inline-block" />
                                            Add Application
                                        </Button>
                                        <Button type="button" onClick={submitPolicy} className="w-fit block">
                                            Save
                                        </Button>
                                    </div>
                                </TabsContent>
                            </div>
                        </div>
                    </Tabs>
                </div>
            </div>
        </Authenticated>
    );
}
