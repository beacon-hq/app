import { Policy, PolicyCollection } from '@/Application';
import InputError from '@/Components/InputError';
import { PolicyDefinitionForm } from '@/Components/PolicyDefinitionForm';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { cn } from '@/lib/utils';
import { FormErrors } from '@/types/global';
import { GripVertical } from 'lucide-react';
import React, { FormEvent, forwardRef, useState } from 'react';

export function Form({
    submit,
    data,
    setData,
    errors,
    processing,
    onCancel,
    policies,
    subjects,
}: {
    submit: (e: FormEvent) => void;
    data: Policy;
    setData: (key: keyof Policy, value: any) => void;
    errors: FormErrors;
    processing: any;
    onCancel: any;
    policies?: PolicyCollection;
    subjects?: string[];
}) {
    return (
        <form onSubmit={(e) => e.preventDefault()} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name" aria-required>
                    Policy Name
                </Label>
                <div className="flex">
                    <Input
                        id="name"
                        type="text"
                        value={data.name as string}
                        autoComplete="off"
                        onChange={(e) => setData('name', e.target.value)}
                    />
                </div>
                <InputError message={errors?.name} />
            </div>
            <div>
                <Label htmlFor="description" aria-required>
                    Description
                </Label>
                <Textarea
                    id="description"
                    value={data.description ?? ''}
                    rows={8}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError message={errors?.description} />
            </div>
            {data.slug && (
                <PolicyDefinitionForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    policies={policies}
                />
            )}
            <div className="flex justify-end">
                <Button variant="link" className="mr-2" type="button" onClick={onCancel}>
                    Cancel
                </Button>
                <Button type="submit" className="w-24" disabled={processing} onClick={submit}>
                    {data.slug ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
}

function Toggle({ value, onValueChange }: { value: boolean; onValueChange: (value: boolean) => void }) {
    const [checked, setChecked] = useState(value);

    function setState(value: boolean) {
        setChecked(value);
        onValueChange(value);
    }

    return (
        <div
            className="rounded-md flex flex-row w-full mx-auto h-10 p-0.5 group"
            data-state={checked ? 'checked' : 'unchecked'}
        >
            <div
                className="bg-secondary rounded-l-md w-1/2 h-full pt-1.5 text-center group-data-[state=checked]:bg-primary group-data-[state=checked]:text-secondary cursor-pointer group-data-[state=checked]:font-semibold"
                onClick={() => setState(true)}
            >
                AND
            </div>
            <div
                className="bg-secondary rounded-r-md w-1/2 h-full pt-1.5 text-center group-data-[state=unchecked]:bg-primary group-data-[state=unchecked]:text-secondary cursor-pointer group-data-[state=unchecked]:font-semibold"
                onClick={() => setState(false)}
            >
                OR
            </div>
        </div>
    );
}

const SortableThumb = forwardRef<HTMLDivElement, { className?: string }>((props, ref) => {
    return (
        <div ref={ref} {...props} className={cn(props.className ?? 'cursor-move')}>
            <GripVertical />
        </div>
    );
});
