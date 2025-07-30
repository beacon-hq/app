import { Tag } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Tags/Components/Form';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export default function Edit({ tag }: { tag: Tag }) {
    const { data, setData, put, errors, processing } = useForm<Tag>({
        id: tag.id,
        name: tag.name,
        description: tag.description,
        color: tag.color,
        created_at: tag.created_at,
        updated_at: tag.updated_at,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('tags.update', { tag: tag.id as string }));
    };

    const handleCancel = () => {
        router.get(route('tags.index'));
    };

    return (
        <Authenticated
            breadcrumbs={[{ name: 'Tags', href: route('tags.index'), icon: 'Tag' }, { name: tag.name as string }]}
        >
            <Head title="Tags | Edit" />
            <div className="mx-auto md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
                        <Card className="p-8" data-dusk="card-tags-edit">
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
