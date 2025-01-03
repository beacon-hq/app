import { Environment } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Environments/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({ environment }: { environment: Environment }) {
    const { data, setData, put, errors, processing } = useForm<Environment>({
        slug: environment.slug,
        name: environment.name,
        color: environment.color,
        description: environment.description,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('environments.update', { environment: environment.slug }));
    };

    const handleCancel = () => {
        router.get(route('environments.index'));
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Environments', href: route('environments.index') }, { name: 'Edit' }]}>
            <Head title="Environments" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <Card className="p-8">
                            <CardTitle className="text-2xl">Edit Environment</CardTitle>
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
