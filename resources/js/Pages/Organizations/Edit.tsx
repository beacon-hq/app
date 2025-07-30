import { Organization } from '@/Application';
import InputError from '@/Components/InputError';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Form from '@/Pages/Organizations/Components/Form';
import { Head, useForm } from '@inertiajs/react';
import React, { FormEventHandler, useRef } from 'react';

export default function Edit({ organization }: { organization: Organization }) {
    const { data, setData, patch, errors, reset, processing } = useForm<Organization>({
        name: organization.name,
        id: organization.id,
        owner: organization.owner,
        onboarded_at: organization.onboarded_at,
    });

    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data: deleteData,
        setData: setDeleteData,
        delete: destroy,
        processing: deleteProcessing,
        reset: deleteReset,
        errors: deleteErrors,
        clearErrors,
    } = useForm<{ password: string }>({
        password: '',
    });

    const handleDelete: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('organizations.destroy', { id: organization.id as string }), {
            preserveScroll: true,
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Settings', icon: 'Settings', href: route('settings.index') },
                { name: 'Organizations', href: route('organizations.index'), icon: 'Network' },
                { name: organization?.name ?? '' },
            ]}
        >
            <Head title="Edit" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <Card
                        data-dusk="card-organizations-edit"
                        className="flex flex-row gap-36 bg-background dark:bg-background"
                    >
                        <CardHeader>
                            <CardTitle className="text-xl">Organization</CardTitle>
                        </CardHeader>
                        <CardContent className="grow pt-8">
                            <Form organization={organization} />
                        </CardContent>
                    </Card>
                    <Card data-dusk="card-organizations-delete">
                        <CardHeader>
                            <CardTitle className="text-xl">Delete Organization</CardTitle>
                            <CardDescription className="text-sm text-gray-600 dark:text-gray-400">
                                Once this organization is deleted, all of its resources and data will be permanently
                                deleted.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-8">
                            <AlertDialog>
                                <AlertDialogTrigger asChild>
                                    <Button variant="destructive" data-dusk="button-organizations-delete">
                                        Delete Organization
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent data-dusk="alert-organization-delete">
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            Are you sure you want to delete this organization?
                                        </AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Once this organization is deleted, all of its resources and data will be
                                            permanently deleted. Please enter your password to confirm you would like to
                                            permanently delete this organization.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <Label htmlFor="password" hidden>
                                        Password
                                    </Label>

                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        ref={passwordInput}
                                        value={deleteData.password}
                                        onChange={(e) => setDeleteData('password', e.target.value)}
                                        className="mt-1 block w-3/4"
                                        autoFocus
                                        placeholder="Password"
                                        aria-required
                                        data-dusk="input-organization-delete-password"
                                    />
                                    <InputError message={deleteErrors?.password} />

                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>

                                        <AlertDialogAction onClick={handleDelete}>
                                            Delete Organization
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
