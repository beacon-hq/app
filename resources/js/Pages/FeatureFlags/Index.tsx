import {
    ApplicationCollection,
    EnvironmentCollection,
    FeatureFlag,
    FeatureFlagCollection,
    FeatureTypeCollection,
    TagCollection,
} from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import Table, { TableOptions } from '@/Pages/FeatureFlags/Components/Table';
import { PageProps } from '@/types';
import { Deferred, Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({
    featureFlags,
    featureFlagsCount,
    page,
    perPage,
    filters,
    featureTypes,
    tags,
    applications,
    environments,
}: PageProps & {
    featureFlags: FeatureFlagCollection;
    featureFlagsCount: number;
    page: number;
    perPage: number;
    filters: { [key: string]: string[] };
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
}) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing, transform } = useForm<FeatureFlag>({
        status: false,
        description: '',
        feature_type: null,
        last_seen_at: '',
        name: '',
        slug: null,
        tags: [],
        statuses: [],
        created_at: '',
        updated_at: '',
    });
    const [tableOptions, setTableOptions] = useState<TableOptions>({
        page,
        perPage,
        filters: Object.fromEntries(Object.entries(filters).map(([key, value]) => [key, new Set(value)])),
    });

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('feature-flags.store'), {
            onSuccess: function () {
                setShowSheet(false);
                reset();
            },
        });
    };

    const handleCancel = () => {
        setShowSheet(false);
        reset();
    };

    return (
        <Authenticated
            breadcrumbs={[{ name: 'Feature Flags', icon: 'Flag' }]}
            headerAction={
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Feature Flag
                </Button>
            }
        >
            <Head title="Feature Flags" />
            <div className="w-full">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <Deferred data="featureFlags" fallback={<div>Loading</div>}>
                                    <Table
                                        featureFlags={featureFlags}
                                        featureFlagCount={featureFlagsCount}
                                        featureTypes={featureTypes}
                                        tags={tags}
                                        applications={applications}
                                        environments={environments}
                                        tableOptions={tableOptions}
                                    />
                                </Deferred>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()}>
                    <SheetTitle className="mb-4">New Feature Flag</SheetTitle>
                    <Form
                        submit={submit}
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onCancel={handleCancel}
                        featureTypes={featureTypes}
                        tags={tags}
                    />
                </SheetContent>
            </Sheet>
        </Authenticated>
    );
}
