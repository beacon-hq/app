import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureTypes/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React from 'react';

export default function Edit({ featureType }: { featureType: any }) {
    const { data, setData, put, errors, processing } = useForm({
        slug: featureType.slug,
        name: featureType.name,
        description: featureType.description,
        temporary: featureType.temporary,
        color: featureType.color,
        icon: featureType.icon,
    });

    const submit = (e: any) => {
        e.preventDefault();
        put(route('feature-types.update', featureType.slug));
    };

    const handleCancel = () => {
        router.get(route('feature-types.index'));
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Feature Types', href: route('feature-types.index') }, { name: 'Edit' }]}>
            <Head title="Feature Types | Edit" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <Card className="p-8">
                            <CardTitle className="text-2xl">Edit Feature Type</CardTitle>
                            <CardContent className="py-4">
                                <Form
                                    submit={submit}
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                    processing={processing}
                                    onCancel={handleCancel}
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
