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
import { TableOptions } from '@/Components/ui/data-table';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import Table from '@/Pages/FeatureFlags/Components/Table';
import { useFeatureFlagStore } from '@/stores/featureFlagStore';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useEffect, useState } from 'react';

export default function Index({
    featureFlags,
    featureFlagsCount,
    page,
    perPage,
    filters,
    sort,
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
    sort: { [key: string]: string };
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
}) {
    const { featureFlag, setFeatureFlag } = useFeatureFlagStore();

    const [showSheet, setShowSheet] = useState(false);
    const { setData, post, errors, reset, processing, transform } = useForm<FeatureFlag>({
        id: undefined,
        status: false,
        description: '',
        feature_type: undefined,
        name: '',
        tags: [],
        statuses: [],
        last_seen_at: null,
        created_at: null,
        updated_at: null,
        completed_at: null,
    });
    const [tableOptions] = useState<TableOptions>({
        page,
        perPage,
        filters: Object.fromEntries(Object.entries(filters).map(([key, value]) => [key, new Set(value)])),
        sort,
    });

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('feature-flags.store'), {
            onSuccess: function () {
                setShowSheet(false);
                reset();
                setFeatureFlag(null);
            },
        });
    };

    const handleCancel = () => {
        setShowSheet(false);
        reset();
        setFeatureFlag(null);
    };

    useEffect(() => {
        if (featureFlag !== null) {
            setData(featureFlag);
        }
    }, [featureFlag]);

    return (
        <Authenticated
            breadcrumbs={[{ name: 'Feature Flags', icon: 'Flag' }]}
            headerAction={
                <Button onClick={() => setShowSheet(true)} data-dusk="button-new-feature-flag">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Feature Flag
                </Button>
            }
        >
            <Head title="Feature Flags" />
            <div className="w-full">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="mt-8" data-dusk="table-feature-flags">
                            <CardContent className="px-12 py-4">
                                <Table
                                    featureFlags={featureFlags}
                                    featureFlagCount={featureFlagsCount}
                                    featureTypes={featureTypes}
                                    tags={tags}
                                    applications={applications}
                                    environments={environments}
                                    tableOptions={tableOptions}
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()} data-dusk="sheet-feature-flag-form">
                    <SheetTitle className="mb-4">New Feature Flag</SheetTitle>
                    <Form
                        submit={submit}
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
