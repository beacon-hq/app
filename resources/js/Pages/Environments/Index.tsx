import { Environment, EnvironmentCollection } from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Environments/Components/Form';
import { localDateTime } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';

export default function Index({ environments }: PageProps & { environments: EnvironmentCollection }) {
    const [showSheet, setShowSheet] = useState(false);
    const { data, setData, post, errors, reset, processing } = useForm<Environment>({
        id: undefined,
        name: '',
        color: '',
        description: '',
        last_seen_at: '',
    });

    const submit = (e: FormEvent) => {
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
        <Authenticated
            header="Environments"
            icon="Globe"
            headerAction={
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Environment
                </Button>
            }
        >
            <Head title="Environments" />
            <div className="grid grid-cols-3 gap-6 mt-6">
                {environments.map((environment: Environment) => (
                    <Card className="w-full relative" key={environment.id} data-dusk="card-environment">
                        <CardHeader className="flex flex-row justify-between items-center">
                            <CardTitle>
                                <h2 className="text-xl truncate">
                                    <Link
                                        href={route('environments.edit', {
                                            environment: environment.id as string,
                                        })}
                                    >
                                        <span className="absolute inset-0"></span>
                                        <div className="flex flex-row items-center gap-2">
                                            <IconColor color={environment.color} /> {environment.name}
                                        </div>
                                    </Link>
                                </h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grow">
                            <p className="text-sm overflow-hidden truncate -mt-2 text-neutral-500">
                                {environment.description}
                            </p>
                        </CardContent>
                        <CardFooter variant="inset" className="bg-neutral-100">
                            <p className="text-xs text-neutral-500">
                                Last Seen:{' '}
                                {environment.last_seen_at ? localDateTime(environment.last_seen_at) : 'never'}
                            </p>
                        </CardFooter>
                    </Card>
                ))}
            </div>
            {environments.length === 0 && (
                <div className="h-full flex items-center">
                    <Card className="mx-auto">
                        <CardHeader>
                            <CardTitle>
                                <h2 className="text-xl">No Environments</h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <p className="text-sm text-neutral-500">You have not created any environments yet.</p>
                            <Button onClick={() => setShowSheet(true)} data-dusk="button-create-environment">
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New Environment
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            )}
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
