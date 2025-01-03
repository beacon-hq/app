import { FeatureFlag, FeatureTypeCollection, TagCollection } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/FeatureFlags/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({
    featureFlag,
    featureTypes,
    tags,
}: {
    featureFlag: FeatureFlag;
    featureTypes: FeatureTypeCollection;
    tags: TagCollection;
}) {
    const { data, setData, put, errors, processing } = useForm<FeatureFlag>({
        slug: featureFlag.slug,
        name: featureFlag.name,
        description: featureFlag.description,
        feature_type: featureFlag.feature_type,
        tags: featureFlag.tags,
        last_seen_at: featureFlag.last_seen_at,
        created_at: null,
        updated_at: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('applications.update', { slug: featureFlag.slug }));
    };

    const handleCancel = () => {
        router.get(route('applications.index'));
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Applications', href: route('applications.index') }, { name: 'Edit' }]}>
            <Head title="Applications" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <Card className="p-8">
                            <CardTitle className="text-2xl">Edit Application</CardTitle>
                            <CardContent className="py-4">
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
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
