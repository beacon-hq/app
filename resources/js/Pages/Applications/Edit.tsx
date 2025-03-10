import { Application } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Applications/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({ application }: { application: Application }) {
    const { data, setData, put, errors, processing, transform } = useForm<Application>({
        slug: application.slug,
        name: application.name,
        display_name: application.display_name,
        description: application.description,
        color: application.color,
        environments: application.environments,
        last_seen_at: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        put(route('applications.update', { slug: application.slug }));
    };

    const handleCancel = () => {
        router.get(route('applications.index'));
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Applications', href: route('applications.index'), icon: 'AppWindowMac' },
                { name: (application.display_name ?? application.name) as string },
            ]}
        >
            <Head title="Applications" />
            <div className="mx-auto md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
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
                                    transform={transform}
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
