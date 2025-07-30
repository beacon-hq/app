import { Application, ApplicationCollection } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { Badge } from '@/Components/ui/badge';
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
        id: undefined,
        color: '#e6e6e6',
        description: '',
        display_name: '',
        environments: [],
        last_seen_at: '',
        name: '',
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
                <Button onClick={() => setShowSheet(true)} data-dusk="button-new-application">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Application
                </Button>
            }
        >
            <Head title="Applications" />
            <div className="grid grid-cols-3 gap-6 mt-6">
                {applications.map((application: Application) => (
                    <Card className="w-full relative" key={application.id} data-dusk="card-application">
                        <CardHeader className="flex flex-row justify-between items-center">
                            <CardTitle>
                                <h2 className="text-xl truncate">
                                    <Link
                                        href={route('applications.edit', {
                                            application: application.id as string,
                                        })}
                                        className="flex flex-row items-center"
                                    >
                                        <span className="absolute inset-0"></span>
                                        <IconColor color={application.color} className="inline-block mr-2" />
                                        {application.display_name}
                                    </Link>
                                </h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grow">
                            <p className="text-sm overflow-hidden truncate -mt-2 text-neutral-500">
                                {application.description}
                            </p>
                            <div className="flex flex-wrap gap-2 mt-4">
                                {(application?.environments?.length ?? 0) > 0 &&
                                    application?.environments?.map((environment) => (
                                        <Badge
                                            className="bg-gray-200 dark:bg-gray-800 rounded-full text-primary py-0 pl-0"
                                            key={environment.id}
                                        >
                                            <IconColor color={environment.color} className="mr-2" />
                                            {environment.name}
                                        </Badge>
                                    ))}
                            </div>
                        </CardContent>
                        <CardFooter variant="inset" className="bg-neutral-100">
                            <p className="text-xs text-neutral-500">
                                Last Seen:{' '}
                                {application.last_seen_at ? localDateTime(application.last_seen_at) : 'never'}
                            </p>
                        </CardFooter>
                    </Card>
                ))}
            </div>
            {applications.length === 0 && (
                <div className="h-full flex items-center">
                    <Card className="mx-auto">
                        <CardHeader>
                            <CardTitle>
                                <h2 className="text-xl">No Applications</h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <p className="text-sm text-neutral-500">You have not created any applications yet.</p>
                            <Button onClick={() => setShowSheet(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New Application
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            )}
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
