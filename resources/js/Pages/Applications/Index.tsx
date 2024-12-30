import { IconColor } from '@/Components/IconColor';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Applications/Components/Form';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ChevronRight, Pencil, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

export default function Index({ applications }: PageProps & { applications: any }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm({
        name: '',
        display_name: '',
        description: '',
    });

    const submit = (e: any) => {
        e.preventDefault();
        post(route('applications.store'), {
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
        <Authenticated header="Applications">
            <Head title="Applications" />
            <div className="mx-auto py-12 md:w-7/12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <div className="flex justify-end">
                            <Button onClick={() => setShowSheet(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New Application
                            </Button>
                        </div>
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <ul>
                                    {applications.map((application: any) => (
                                        <li key={application.id} className="w-full">
                                            <div className="flex h-24 items-center justify-between">
                                                <div className="flex flex-col space-y-2">
                                                    <div className="text-lg font-semibold leading-none">
                                                        {application.display_name}
                                                    </div>
                                                    <div className="flex">
                                                        {application.environments.map(
                                                            (environment: any, key: number) => (
                                                                <div
                                                                    key={environment.id}
                                                                    className="flex flex-row gap-1 items-center p-1 group first:-ml-0 -ml-4"
                                                                >
                                                                    <IconColor color={environment.color} />
                                                                    <span className="hidden group-hover:inline-block pr-3">
                                                                        {environment.name}
                                                                    </span>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex gap-4">
                                                    <Link href={route('applications.edit', application.slug)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                    <ChevronRight className="h-4 w-4" />
                                                </div>
                                            </div>
                                            {applications[applications.length - 1].id !== application.id && (
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
                    <SheetTitle className="mb-4">New Application</SheetTitle>
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
