import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Tags/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React from 'react';

export default function Edit({ tag }: { tag: any }) {
    const { data, setData, put, errors, processing } = useForm({
        slug: tag.slug,
        name: tag.name,
        description: tag.description,
        color: tag.color,
    });

    const submit = (e: any) => {
        e.preventDefault();
        put(route('tags.update', tag.slug));
    };

    const handleCancel = () => {
        router.get(route('tags.index'));
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Tags', href: route('tags.index') }, { name: 'Edit' }]}>
            <Head title="Tags | Edit" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <Card className="p-8">
                            <CardTitle className="text-2xl">Edit Tag</CardTitle>
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
