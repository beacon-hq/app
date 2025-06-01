import { Tag } from '@/Application';
import { Form } from '@/Pages/Tags/Components/Form';
import { useForm } from '@inertiajs/react';
import React, { FormEvent } from 'react';

export function NewTagForm({ onComplete, name = '' }: { onComplete: () => void; name?: string }) {
    const { data, setData, post, errors, reset, processing } = useForm<Tag>({
        id: undefined,
        name: name,
        description: '',
        color: '#e6e6e6',
        created_at: null,
        updated_at: null,
    });

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        post(route('tags.store'), {
            preserveState: true,
            onSuccess: function () {
                onComplete();
                reset();
            },
        });
    };

    const handleCancel = () => {
        onComplete();
        reset();
    };

    return (
        <Form
            submit={submit}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onCancel={handleCancel}
        />
    );
}
