import { Tag, TagCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Tags/Components/Form';
import Table from '@/Pages/Tags/Components/Table';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({ tags }: PageProps & { tags: TagCollection }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm<Tag>({
        name: '',
        description: '',
        color: '#e6e6e6',
        id: null,
        slug: null,
        created_at: null,
        updated_at: null,
    });

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('tags.store'), {
            onSuccess: function () {
                setShowSheet(false);
                reset();
            },
        });
    };

    const handleCancel = () => {
        setShowSheet(false);
        reset();
    };

    return (
        <Authenticated
            breadcrumbs={[{ name: 'Tags', icon: 'Tag' }]}
            headerAction={
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Tag
                </Button>
            }
        >
            <Head title="Tags" />
            <div className="w-full">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <Table tags={tags} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()}>
                    <SheetTitle className="mb-4">New Tag</SheetTitle>
                    <Form
                        submit={submit}
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onCancel={handleCancel}
                    />
                </SheetContent>
            </Sheet>
        </Authenticated>
    );
}
