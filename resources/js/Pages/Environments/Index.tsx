import { IconColor } from '@/Components/IconColor';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Environments/Components/Form';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Pencil, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

export default function Index({ environments }: PageProps & { environments: any }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm({
        name: '',
        color: '',
        description: '',
    });

    const submit = (e: any) => {
        e.preventDefault();
        post(route('environments.store'), {
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
        <Authenticated header="Environments">
            <Head title="Environments" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <div className="flex justify-end">
                            <Button onClick={() => setShowSheet(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New Environment
                            </Button>
                        </div>
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <ul>
                                    {environments.map((environment: any) => (
                                        <li key={environment.id} className="w-full">
                                            <div className="flex h-24 items-center justify-between">
                                                <div className="flex flex-row items-center space-x-2">
                                                    <IconColor color={environment.color} />
                                                    <div className="text-lg font-semibold leading-none">
                                                        {environment.name}
                                                    </div>
                                                </div>
                                                <div className="flex gap-4">
                                                    <Link href={route('environments.edit', environment.slug)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </div>
                                            </div>
                                            {environments[environments.length - 1].id !== environment.id && (
                                                <Separator />
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()}>
                    <SheetTitle className="mb-4">New Environment</SheetTitle>
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
