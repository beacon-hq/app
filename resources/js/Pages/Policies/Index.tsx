import { Policy, PolicyCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Sheet, SheetContent, SheetTitle } from '@/Components/ui/sheet';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Policies/Components/Form';
import Table from '@/Pages/Policies/Components/Table';
import { usePolicyStore } from '@/stores/policyStore';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useEffect, useState } from 'react';

export default function Index({ policies }: PageProps & { policies: PolicyCollection }) {
    const [showSheet, setShowSheet] = useState(false);
    const newPolicy = {
        id: undefined,
        name: '',
        description: '',
        definition: undefined,
        created_at: null,
        updated_at: null,
    };
    // @ts-ignore
    const { setData, post, errors, reset, processing } = useForm<Policy>(newPolicy);

    const { policy, setPolicy } = usePolicyStore();

    useEffect(
        function () {
            if (policy) {
                setData(policy);
            }
        },
        [policy],
    );

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('policies.store'), {
            onSuccess: function () {
                setShowSheet(false);
                reset();
                setPolicy(newPolicy);
            },
        });
    };

    const handleCancel = () => {
        setShowSheet(false);
        reset();
        setPolicy(newPolicy);
    };

    return (
        <Authenticated
            breadcrumbs={[{ name: 'Policies', icon: 'SlidersHorizontal' }]}
            headerAction={
                <Button
                    onClick={function () {
                        setPolicy(newPolicy);
                        setShowSheet(true);
                    }}
                    data-dusk="button-new-policy"
                >
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Policy
                </Button>
            }
        >
            <Head title="Policies" />
            <div className="w-full">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <Table policies={policies} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Sheet open={showSheet} onOpenChange={setShowSheet}>
                <SheetContent onOpenAutoFocus={(event) => event.preventDefault()} data-dusk="sheet-policy-form">
                    <SheetTitle className="mb-4">New Policy</SheetTitle>
                    <Form submit={submit} errors={errors} processing={processing} onCancel={handleCancel} />
                </SheetContent>
            </Sheet>
        </Authenticated>
    );
}
