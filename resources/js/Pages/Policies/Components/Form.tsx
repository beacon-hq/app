import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Separator } from '@/Components/ui/separator';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { arrayMove } from '@dnd-kit/sortable';
import { GripVertical, PlusCircle, Trash, TriangleAlert } from 'lucide-react';
import React, { forwardRef, useEffect, useState } from 'react';
import SortableList, { SortableItem, SortableKnob } from 'react-easy-sort';

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
    submit: (e: React.FormEvent) => void;
    data: any;
    setData: (key: string, value: any) => void;
    errors: any;
    processing: any;
    onCancel: any;
    policies: any[];
    subjects: any[];
}) {
    const [definitions, setDefinitions] = useState([
        ...(data.definition ?? [{ type: 'expression', subject: '', operator: '' }]),
    ]);

    useEffect(() => {
        setData('definition', definitions);
    }, [definitions]);

    const onSortEnd = (oldIndex: number, newIndex: number) => {
        setDefinitions(arrayMove(data.definition, oldIndex, newIndex));
    };

    const handleAddNew = () => {
        return setDefinitions([...definitions, { type: 'expression', subject: '', operator: '' }]);
    };
    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name" aria-required>
                    Policy Name
                </Label>
                <div className="flex">
                    <Input
                        id="name"
                        type="text"
                        value={data.name}
                        autoComplete="off"
                        onChange={(e) => setData('name', e.target.value)}
                    />
                </div>
                {errors.name && <InputError message={errors.name} />}
            </div>
            <div>
                <Label htmlFor="description" aria-required>
                    Description
                </Label>
                <Textarea
                    id="description"
                    value={data.description}
                    rows={8}
                    onChange={(e) => setData('description', e.target.value)}
                />
            </div>
            <div className="border p-2 flex flex-col gap-4">
                <SortableList onSortEnd={onSortEnd} className="list" draggedItemClassName="dragged" lockAxis="y">
                    {definitions.map((definition: any, id: number) => (
                        <SortableItem key={id}>
                            <div
                                className={cn('bg-background w-full mb-2 pt-2', {
                                    'bg-red-50': id === 0 && definition.type == 'operator',
                                })}
                            >
                                <div className="flex justify-between gap-2 items-center p-2">
                                    {id === 0 && definition.type === 'operator' && (
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <TriangleAlert className="text-red-500 w-6 h-6 inline-block" />
                                            </TooltipTrigger>
                                            <TooltipContent
                                                className={cn('bg-red-400', {
                                                    hidden: id !== 0 || definition.type != 'operator',
                                                })}
                                            >
                                                <p>The first item cannot be an operator.</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    )}
                                    <div className="flex flex-col w-1/3">
                                        <Label htmlFor="type" aria-required hidden>
                                            Type
                                        </Label>
                                        <Select
                                            value={definition.type}
                                            onValueChange={(value) => {
                                                let definitions = data.definition.map((item: any, index: number) => {
                                                    if (index === id) {
                                                        return { ...item, subject: '', type: value };
                                                    }

                                                    return item;
                                                });

                                                return setDefinitions(definitions);
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a type…" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="expression">Expression</SelectItem>
                                                    <SelectItem value="policy">Policy</SelectItem>
                                                    <SelectItem value="operator">Operator</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div
                                        className={cn('flex flex-col', {
                                            'w-1/3': definition.type === 'expression',
                                            'w-1/2': definition.type === 'operator' || definition.type === 'policy',
                                        })}
                                    >
                                        <Label htmlFor="subject" aria-required hidden>
                                            {definition.type === 'expression' && 'Subject'}
                                            {definition.type === 'policy' && 'Policy'}
                                            {definition.type === 'operator' && 'Operator'}
                                        </Label>
                                        {definition.type === 'expression' && (
                                            <Input
                                                id="subject"
                                                type="text"
                                                value={definition.subject}
                                                autoComplete="off"
                                                onChange={function (e) {
                                                    let definitions = data.definition.map(
                                                        (item: any, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: e.target.value };
                                                            }

                                                            return item;
                                                        },
                                                    );

                                                    return setDefinitions(definitions);
                                                }}
                                            />
                                        )}
                                        {definition.type === 'policy' && (
                                            <Select
                                                value={definition.subject}
                                                onValueChange={(value) => {
                                                    let definitions = data.definition.map(
                                                        (item: any, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }

                                                            return item;
                                                        },
                                                    );

                                                    return setDefinitions(definitions);
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a policy…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {policies.map((policy) => (
                                                            <SelectItem key={policy.id} value={policy.id}>
                                                                {policy.name}
                                                            </SelectItem>
                                                        ))}
                                                        {policies.length === 0 && (
                                                            <SelectItem value="none" disabled>
                                                                No policies found.
                                                            </SelectItem>
                                                        )}
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        )}
                                        {definition.type === 'operator' && (
                                            <Select
                                                value={definition.subject}
                                                onValueChange={(value) => {
                                                    let definitions = data.definition.map(
                                                        (item: any, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }

                                                            return item;
                                                        },
                                                    );

                                                    return setDefinitions(definitions);
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select an operator…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value="AND">AND</SelectItem>
                                                        <SelectItem value="OR">OR</SelectItem>
                                                        <SelectItem value="NOT">AND NOT</SelectItem>
                                                        <SelectItem value="XOR">XOR</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        )}
                                    </div>
                                    {definition.type === 'expression' && (
                                        <div className="flex flex-col w-1/3">
                                            <Label htmlFor="operator" aria-required hidden>
                                                Operator
                                            </Label>
                                            <Select
                                                value={definition.operator}
                                                onValueChange={(value) => {
                                                    let definitions = data.definition.map(
                                                        (item: any, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, operator: value };
                                                            }

                                                            return item;
                                                        },
                                                    );

                                                    return setDefinitions(definitions);
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select an operator…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value="equals">=</SelectItem>
                                                        <SelectItem value="not_equal">!=</SelectItem>
                                                        <SelectItem value="contains">contains</SelectItem>
                                                        <SelectItem value="not_contains">does not contains</SelectItem>
                                                        <SelectItem value="matches">matches</SelectItem>
                                                        <SelectItem value="not_matches">does not match</SelectItem>
                                                        <SelectItem value="one_of">is one of</SelectItem>
                                                        <SelectItem value="not_one_of">is not one of</SelectItem>
                                                        <SelectItem value="less_than">&lt;</SelectItem>
                                                        <SelectItem value="less_than_equals">&lt;=</SelectItem>
                                                        <SelectItem value="greated_than">&gt;</SelectItem>
                                                        <SelectItem value="greater_than_equals">&gt;=</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    )}
                                    <div className="flex flex-row pt-2">
                                        <SortableKnob>
                                            <SortableThumb
                                                className={cn({
                                                    'text-primary/20 cursor-not-allowed': data.definition.length === 1,
                                                    'cursor-move': data.definition.length > 1,
                                                })}
                                            />
                                        </SortableKnob>
                                        <Trash
                                            className={cn({
                                                'text-primary/20 cursor-not-allowed': data.definition.length === 1,
                                                'cursor-pointer': data.definition.length > 1,
                                            })}
                                            onClick={() => {
                                                if (data.definition.length === 1) {
                                                    return;
                                                }

                                                let definitions = data.definition
                                                    .filter((item: any, index: number) => index !== id)
                                                    .slice();
                                                return setDefinitions(definitions);
                                            }}
                                        />
                                    </div>
                                </div>
                                {id < data.definition.length - 1 && (
                                    <>
                                        <Separator className="mt-2" />
                                    </>
                                )}
                            </div>
                        </SortableItem>
                    ))}
                </SortableList>
                <div className="flex justify-center">
                    <Button type="button" onClick={handleAddNew} variant="outline">
                        <PlusCircle /> Add
                    </Button>
                </div>
            </div>
            <div className="flex justify-end">
                <Button variant="link" className="mr-2" type="button" onClick={onCancel}>
                    Cancel
                </Button>
                <Button type="submit" className="w-24" disabled={processing}>
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
