import { FeatureType } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureTypes/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({ featureType }: { featureType: FeatureType }) {
    const { data, setData, put, errors, processing } = useForm<FeatureType>({
        id: featureType.id,
        slug: featureType.slug,
        name: featureType.name,
        description: featureType.description,
        temporary: featureType.temporary,
        color: featureType.color,
        icon: featureType.icon,
        created_at: featureType.created_at,
        updated_at: featureType.updated_at,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('feature-types.update', { feature_type: featureType.slug }));
    };

    const handleCancel = () => {
        router.get(route('feature-types.index'));
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Feature Types', href: route('feature-types.index'), icon: 'Component' },
                { name: featureType.name as string },
            ]}
        >
            <Head title="Feature Types | Edit" />
            <div className="mx-auto md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
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
