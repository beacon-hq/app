import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Applications/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React from 'react';

export default function Edit({ application }: { application: any }) {
    const { data, setData, put, errors, processing } = useForm({
        slug: application.slug,
        name: application.name,
        display_name: application.display_name,
        description: application.description,
    });

    const submit = (e: any) => {
        e.preventDefault();
        put(route('applications.update', application.slug));
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
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
