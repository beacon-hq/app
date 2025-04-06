import { OrganizationCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader } from '@/Components/ui/card';
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
                <Button onClick={() => setShowSheet(true)}>
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Organization
                </Button>
            }
        >
            <Head title="Organizations" />

            <div className="mt-12 flex flex-wrap gap-4">
                {organizations.map((organization) => (
                    <Card
                        className="relative flex flex-row items-center justify-between w-1/3 cursor-pointer"
                        key={organization.id}
                    >
                        <div>
                            <CardHeader>
                                <Link href={route('organizations.edit', { id: organization.id as string })}>
                                    <span className="absolute inset-0"></span>
                                    {organization.name}
                                </Link>
                            </CardHeader>
                            <CardContent>
                                <CardDescription>
                                    <p>Manage Organization settings.</p>
                                </CardDescription>
                            </CardContent>
                        </div>
                        <div>
                            <ChevronRight className="mr-4" />
                        </div>
                    </Card>
                ))}
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
