import { Application, ApplicationCollection, Environment } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Applications/Components/Form';
import { localDateTime } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({ applications }: PageProps & { applications: ApplicationCollection }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing, transform } = useForm<Application>({
        color: '#e6e6e6',
        description: '',
        display_name: '',
        environments: [],
        last_seen_at: '',
        name: '',
        slug: null,
    });

    const submit = (e: FormEvent) => {
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
        <Authenticated
            header="Applications"
            icon="AppWindowMac"
            headerAction={
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Application
                </Button>
            }
        >
            <Head title="Applications" />
            <div className="flex justify-end"></div>
            <div className="grid grid-cols-3 gap-6 mt-6">
                {applications.map((application: Application) => (
                    <Link
                        href={route('applications.edit', {
                            slug: application.slug,
                        })}
                    >
                        <Card className="w-full min-h-56" key={application.slug}>
                            <CardHeader className="flex flex-row justify-between items-center">
                                <CardTitle className="text-xl truncate">{application.name}</CardTitle>
                                <div className="flex">
                                    {application.environments?.map((environment: Environment, key: number) => (
                                        <div
                                            key={environment.slug}
                                            className="flex flex-row gap-1 items-center p-1 group first:-ml-0 -ml-4"
                                        >
                                            <IconColor color={environment.color} />
                                            <span className="hidden group-hover:inline-block pr-3">
                                                {environment.name}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </CardHeader>
                            <CardContent className="grow">
                                <p className="text-sm overflow-hidden truncate -mt-2 text-neutral-500">
                                    {application.description}
                                </p>
                            </CardContent>
                            <CardFooter variant="inset" className="bg-neutral-100">
                                <p className="text-xs text-neutral-500">
                                    Last Seen:{' '}
                                    {application.last_seen_at ? localDateTime(application.last_seen_at) : 'never'}
                                </p>
                            </CardFooter>
                        </Card>
                    </Link>
                ))}
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
                        transform={transform}
                    />
                </SheetContent>
            </Sheet>
        </Authenticated>
    );
}
