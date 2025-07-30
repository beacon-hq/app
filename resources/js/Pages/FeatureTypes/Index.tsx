import { FeatureType, FeatureTypeCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureTypes/Components/Form';
import Table from '@/Pages/FeatureTypes/Components/Table';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({ featureTypes }: PageProps & { featureTypes: FeatureTypeCollection }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm<FeatureType>({
        id: undefined,
        name: '',
        description: '',
        temporary: true,
        is_default: false,
        color: '#e6e6e6',
        icon: '',
        created_at: null,
        updated_at: null,
    });

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('feature-types.store'), {
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
            breadcrumbs={[{ name: 'Feature Types', icon: 'Component' }]}
            headerAction={
                <Button onClick={() => setShowSheet(true)} data-dusk="button-new-feature-type">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Feature Type
                </Button>
            }
        >
            <Head title="Feature Types" />
            <div className="w-full">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="mt-8" data-dusk="card-feature-types">
                            <CardContent className="px-12 py-4">
                                <Table featureTypes={featureTypes} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()} data-dusk="sheet-feature-type-form">
                    <SheetTitle className="mb-4">New Feature Type</SheetTitle>
                    <Form
                        submit={submit}
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onCancel={handleCancel}
                    />
                </SheetContent>
            </Sheet>
        </Authenticated>
    );
}
