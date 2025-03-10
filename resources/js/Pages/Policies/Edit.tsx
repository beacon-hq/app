import { Policy, PolicyCollection } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Policies/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({ policy, policies }: { policy: Policy; policies: PolicyCollection }) {
    const subjects: string[] = [];
    const { data, setData, put, errors, processing } = useForm<Policy>({
        id: null,
        slug: policy.slug,
        name: policy.name,
        description: policy.description,
        definition: policy.definition,
        created_at: policy.created_at,
        updated_at: policy.updated_at,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('policies.update', { slug: policy.slug }), { preserveScroll: true });
    };

    const handleCancel = () => {
        router.get(route('policies.index'));
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Policies', href: route('policies.index'), icon: 'SlidersHorizontal' },
                { name: policy.name as string },
            ]}
        >
            <Head title="Policies | Edit" />
            <div className="mx-auto py-12">
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
