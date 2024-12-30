import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import Table from '@/Pages/FeatureFlags/Components/Table';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({
    featureFlags,
    featureTypes,
    tags,
}: PageProps & { featureFlags: any; featureTypes: any; tags: any[] }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm({
        name: '',
        description: '',
        feature_type: '',
        tags: [],
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
        <Authenticated breadcrumbs={[{ name: 'Feature Flags' }]}>
            <Head title="Feature Flags" />
            <div className="mx-auto w-full py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <div className="flex justify-end">
                            <Button onClick={() => setShowSheet(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New Feature Flag
                            </Button>
                        </div>
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <Table featureFlags={featureFlags} />
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
