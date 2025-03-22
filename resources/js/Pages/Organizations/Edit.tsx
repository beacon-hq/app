import { Organization } from '@/Application';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Form from '@/Pages/Organizations/Components/Form';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import React, { FormEventHandler, useRef, useState } from 'react';

export default function Edit({ organization }: { organization: Organization }) {
    const { data, setData, patch, errors, reset, processing } = useForm<Organization>({
        name: organization.name,
        id: organization.id,
        owner: organization.owner,
        slug: organization.slug,
    });

    const handleSubmit = () => {
        patch(route('organizations.update', { id: organization.id }));
    };

    const [confirmingOrganizationDeletion, setConfirmingOrganizationDeletion] = useState(false);
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

    const confirmOrganizationDeletion = () => {
        setConfirmingOrganizationDeletion(true);
    };

    const handleDelete: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('organizations.destroy', { id: organization.id }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingOrganizationDeletion(false);

        clearErrors();
        reset();
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
                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <section className={cn('flex flex-row gap-8')}>
                            <header className="w-1/4">
                                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Organization</h2>
                            </header>
                            <div className="mt-6 space-y-6 w-3/4 grow">
                                <Form organization={organization} />
                            </div>
                        </section>
                    </div>
                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <section className="space-y-6">
                            <header>
                                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Delete Organization
                                </h2>

                                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Once this organization is deleted, all of its resources and data will be permanently
                                    deleted.
                                </p>
                            </header>

                            <Button variant="destructive" onClick={confirmOrganizationDeletion}>
                                Delete Organization
                            </Button>

                            <Modal show={confirmingOrganizationDeletion} onClose={closeModal}>
                                <form onSubmit={handleDelete} className="p-6">
                                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        Are you sure you want to delete this organization?
                                    </h2>

                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Once this organization is deleted, all of its resources and data will be
                                        permanently deleted. Please enter your password to confirm you would like to
                                        permanently delete this organization.
                                    </p>

                                    <div className="mt-6">
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
                                        />
                                        <InputError message={deleteErrors?.password} />
                                    </div>

                                    <div className="mt-6 flex justify-end">
                                        <Button variant="secondary" onClick={closeModal}>
                                            Cancel
                                        </Button>

                                        <Button variant="destructive" className="ms-3" disabled={processing}>
                                            Delete Organization
                                        </Button>
                                    </div>
                                </form>
                            </Modal>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
