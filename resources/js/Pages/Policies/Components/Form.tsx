import { Policy, PolicyCollection, PolicyDefinitionCollection, PolicyDefinitionType } from '@/Application';
import InputError from '@/Components/InputError';
import { PolicyDefinitionForm } from '@/Components/PolicyDefinitionForm';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { usePolicyStore } from '@/stores/policyStore';
import { FormErrors } from '@/types/global';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent } from 'react';

export function Form({
    submit,
    errors,
    processing,
    onCancel,
    policies,
}: {
    submit: (e: FormEvent) => void;
    errors: FormErrors;
    processing: any;
    onCancel: any;
    policies?: PolicyCollection;
}) {
    const { policy, updatePolicy } = usePolicyStore();

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
                        value={policy?.name ?? ''}
                        autoComplete="off"
                        onChange={(e) => updatePolicy({ ...policy, name: e.target.value } as Policy)}
                        data-dusk="input-policy-name"
                    />
                </div>
                <InputError message={errors?.name} />
            </div>
            <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={policy?.description ?? ''}
                    rows={8}
                    onChange={(e) => updatePolicy({ ...policy, description: e.target.value } as Policy)}
                    data-dusk="input-policy-description"
                />
                <InputError message={errors?.description} />
            </div>
            {policy !== undefined &&
                policy?.id !== undefined &&
                policy.definition !== undefined &&
                (policy.definition as PolicyDefinitionCollection).length > 0 && (
                    <PolicyDefinitionForm policies={policies} />
                )}
            {(policy === undefined || (policy?.definition?.length ?? 0) === 0) && (
                <Button
                    variant="outline"
                    className="mx-auto block"
                    type="button"
                    onClick={() => {
                        updatePolicy({
                            ...policy,
                            definition: [
                                ...(policy?.definition ?? []),
                                {
                                    type: PolicyDefinitionType.EXPRESSION,
                                    subject: '',
                                    operator: null,
                                    values: [],
                                },
                            ] as PolicyDefinitionCollection,
                        } as Policy);
                    }}
                    data-dusk="button-policies-add-conditions"
                >
                    <PlusCircle className="inline-block mr-2" /> Add Conditions
                </Button>
            )}
            <div className="flex justify-end">
                <Button variant="link" className="mr-2" type="button" onClick={onCancel}>
                    Cancel
                </Button>
                <Button
                    type="submit"
                    className="w-24"
                    disabled={processing}
                    onClick={submit}
                    data-dusk="button-policy-submit"
                >
                    {policy?.id ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
}
//
// function Toggle({ value, onValueChange }: { value: boolean; onValueChange: (value: boolean) => void }) {
//     const [checked, setChecked] = useState(value);
//
//     function setState(value: boolean) {
//         setChecked(value);
//         onValueChange(value);
//     }
//
//     return (
//         <div
//             className="rounded-md flex flex-row w-full mx-auto h-10 p-0.5 group"
//             data-state={checked ? 'checked' : 'unchecked'}
//         >
//             <div
//                 className="bg-secondary rounded-l-md w-1/2 h-full pt-1.5 text-center group-data-[state=checked]:bg-primary group-data-[state=checked]:text-secondary cursor-pointer group-data-[state=checked]:font-semibold"
//                 onClick={() => setState(true)}
//             >
//                 AND
//             </div>
//             <div
//                 className="bg-secondary rounded-r-md w-1/2 h-full pt-1.5 text-center group-data-[state=unchecked]:bg-primary group-data-[state=unchecked]:text-secondary cursor-pointer group-data-[state=unchecked]:font-semibold"
//                 onClick={() => setState(false)}
//             >
//                 OR
//             </div>
//         </div>
//     );
// }
//
// const SortableThumb = forwardRef<HTMLDivElement, { className?: string }>((props, ref) => {
//     return (
//         <div ref={ref} {...props} className={cn(props.className ?? 'cursor-move')}>
//             <GripVertical />
//         </div>
//     );
// });
