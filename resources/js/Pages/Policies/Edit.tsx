import { Policy, PolicyCollection } from '@/Application';
import { Card, CardContent, CardTitle } from '@/Components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Form } from '@/Pages/Policies/Components/Form';
import { usePolicyStore } from '@/stores/policyStore';
import { Head, router, useForm } from '@inertiajs/react';
import React, { FormEvent, useEffect } from 'react';

export default function Edit({ policies, ...props }: { policy: Policy; policies: PolicyCollection }) {
    const { policy, setPolicy } = usePolicyStore();

    useEffect(
        function () {
            if (props.policy) {
                setPolicy(props.policy);
            }
        },
        [props.policy],
    );

    useEffect(
        function () {
            if (policy) {
                setData(policy);
            }
        },
        [policy],
    );

    // @ts-ignore
    const { setData, put, errors, processing } = useForm<Policy>(props.policy);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('policies.update', { policy: policy?.id as string }), { preserveScroll: true });
    };

    const handleCancel = () => {
        router.get(route('policies.index'));
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Policies', href: route('policies.index'), icon: 'SlidersHorizontal' },
                { name: (policy?.name as string) ?? 'Edit Policy' },
            ]}
        >
            <Head title="Policies | Edit" />
            <div className="mx-auto py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4">
                        <Card className="p-8" data-dusk="card-policies-edit">
                            <CardTitle className="text-2xl">Edit Policy</CardTitle>
                            <CardContent className="py-4">
                                <Form
                                    submit={submit}
                                    errors={errors}
                                    processing={processing}
                                    onCancel={handleCancel}
                                    policies={policies}
                                />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
