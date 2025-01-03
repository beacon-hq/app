import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Policies/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React from 'react';

export default function Edit({ policy, policies }: { policy: any; policies: any[] }) {
    const subjects: any[] = [];
    const { data, setData, put, errors, processing } = useForm({
        slug: policy.slug,
        name: policy.name,
        description: policy.description,
        definition: policy.definition,
    });

    const submit = (e: any) => {
        e.preventDefault();
        put(route('policies.update', policy.slug), { preserveScroll: true });
    };

    const handleCancel = () => {
        router.get(route('policies.index'));
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Feature Types', href: route('feature-types.index') }, { name: 'Edit' }]}>
            <Head title="Feature Types | Edit" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
                        <Card className="p-8">
                            <CardTitle className="text-2xl">Edit Policy</CardTitle>
                            <CardContent className="py-4">
                                <Form
                                    submit={submit}
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                    processing={processing}
                                    onCancel={handleCancel}
                                    policies={policies}
                                    subjects={subjects}
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
