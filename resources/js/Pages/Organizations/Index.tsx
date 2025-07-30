import { OrganizationCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardDescription, CardHeader } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Form from '@/Pages/Organizations/Components/Form';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

export default function Index({ organizations }: { organizations: OrganizationCollection }) {
    const [showSheet, setShowSheet] = useState(false);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { name: 'Settings', icon: 'Settings', href: route('settings.index') },
                { name: 'Organizations', icon: 'Network' },
            ]}
            headerAction={
                <Button onClick={() => setShowSheet(true)} data-dusk="button-new-organization">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Organization
                </Button>
            }
        >
            <Head title="Organizations" />
            <div className="w-full md:w-1/2 mx-auto pt-12">
                <div className="overflow-hidden p-4">
                    {organizations.map((organization) => (
                        <Card key={organization.id} className="mb-4 w-full relative" data-dusk="card-organization">
                            <CardHeader className="flex flex-row items-center justify-between">
                                <div>
                                    <h3 className="text-2xl font-bold">{organization.name}</h3>
                                    <CardDescription>
                                        Owner: {organization.owner?.name ?? 'No owner assigned'}
                                    </CardDescription>
                                </div>
                                <Link
                                    href={route('organizations.edit', {
                                        organization: organization?.id as string,
                                    })}
                                >
                                    <span className="absolute inset-0"></span>
                                    <ChevronRight className="ml-1 h-4 w-4" />
                                </Link>
                            </CardHeader>
                        </Card>
                    ))}

                    {organizations.length === 0 && (
                        <div className="p-8 text-center">
                            <p className="text-gray-500 mb-4">No organizations found</p>
                            <Button onClick={() => setShowSheet(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                Create your first organization
                            </Button>
                        </div>
                    )}
                </div>
            </div>

            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()}>
                    <SheetTitle className="mb-4">New Organization</SheetTitle>
                    <Form onCancel={() => setShowSheet(false)} />
                </SheetContent>
            </Sheet>
        </AuthenticatedLayout>
    );
}
