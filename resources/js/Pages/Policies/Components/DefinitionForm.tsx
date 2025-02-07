import {
    Boolean,
    Policy,
    PolicyCollection,
    PolicyDefinition,
    PolicyDefinitionCollection,
    PolicyDefinitionMatchOperator,
} from '@/Application';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Separator } from '@/Components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { FormErrors } from '@/types/global';
import { arrayMove } from '@dnd-kit/sortable';
import { GripVertical, PlusCircle, Trash, TriangleAlert } from 'lucide-react';
import React, { forwardRef, useEffect, useState } from 'react';
import SortableList, { SortableItem, SortableKnob } from 'react-easy-sort';

export function DefinitionForm({
    data,
    setData,
    errors,
    processing,
    policies,
    subjects,
}: {
    data: Policy;
    setData: (key: keyof Policy, value: any) => void;
    errors: FormErrors;
    processing: any;
    policies?: PolicyCollection;
    subjects?: string[];
}) {
    const [definitions, setDefinitions] = useState<PolicyDefinitionCollection>([
        ...(data.definition === null || data.definition.length === 0
            ? [{ type: 'expression', subject: '', operator: '' }]
            : data.definition),
    ] as PolicyDefinitionCollection);

    useEffect(() => {
        setData('definition', definitions);
    }, [definitions]);

    const onSortEnd = (oldIndex: number, newIndex: number) => {
        setDefinitions(arrayMove(data?.definition ?? [], oldIndex, newIndex));
    };

    const handleAddNew = () => {
        return setDefinitions([
            ...definitions,
            { type: 'expression', subject: '', operator: '' },
        ] as PolicyDefinitionCollection);
    };

    return (
        <div className="border p-2 flex flex-col gap-4">
            <SortableList onSortEnd={onSortEnd} className="list" draggedItemClassName="dragged" lockAxis="y">
                {definitions.map((definition: PolicyDefinition, id: number) => (
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
                                            let definitions =
                                                data.definition?.map((item: PolicyDefinition, index: number) => {
                                                    if (index === id) {
                                                        return { ...item, subject: '', type: value };
                                                    }

                                                    return item;
                                                }) ?? [];

                                            return setDefinitions(definitions as PolicyDefinitionCollection);
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
                                                let definitions =
                                                    data.definition?.map((item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, subject: e.target.value };
                                                        }

                                                        return item;
                                                    }) ?? [];

                                                return setDefinitions(definitions as PolicyDefinitionCollection);
                                            }}
                                        />
                                    )}
                                    {definition.type === 'policy' && (
                                        <Select
                                            value={definition.subject}
                                            onValueChange={(value) => {
                                                let definitions =
                                                    data.definition?.map((item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, subject: value };
                                                        }

                                                        return item;
                                                    }) ?? [];

                                                return setDefinitions(definitions);
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a policy…" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {policies
                                                        ?.filter((policy: Policy) => policy.slug != data.slug)
                                                        .map((policy: Policy) => (
                                                            <SelectItem
                                                                key={`${id}-${policy.slug}`}
                                                                value={policy.slug as string}
                                                            >
                                                                {policy.name}
                                                            </SelectItem>
                                                        ))}
                                                    {policies?.length === 0 && (
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
                                                let definitions =
                                                    data.definition?.map((item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, subject: value };
                                                        }

                                                        return item;
                                                    }) ?? [];

                                                return setDefinitions(definitions as PolicyDefinitionCollection);
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select an operator…" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {Object.entries(Boolean).map(([, operator]) => {
                                                        return (
                                                            <SelectItem key={operator} value={operator}>
                                                                {operator}
                                                            </SelectItem>
                                                        );
                                                    })}
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
                                                let definitions = data.definition?.map(
                                                    (item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, operator: value };
                                                        }

                                                        return item;
                                                    },
                                                );

                                                return setDefinitions(definitions as PolicyDefinitionCollection);
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select an operator…" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {Object.entries(PolicyDefinitionMatchOperator).map(
                                                        ([, operator]) => {
                                                            return (
                                                                <SelectItem key={operator} value={operator}>
                                                                    {operator}
                                                                </SelectItem>
                                                            );
                                                        },
                                                    )}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}
                                <div className="flex flex-row pt-2">
                                    <SortableKnob>
                                        <SortableThumb
                                            className={cn({
                                                'text-primary/20 cursor-not-allowed': data.definition?.length === 1,
                                                'cursor-move': (data.definition?.length as number) > 1,
                                            })}
                                        />
                                    </SortableKnob>
                                    <Trash
                                        className={cn({
                                            'text-primary/20 cursor-not-allowed': data.definition?.length === 1,
                                            'cursor-pointer': (data.definition?.length as number) > 1,
                                        })}
                                        onClick={() => {
                                            if (data.definition?.length === 1) {
                                                return;
                                            }

                                            let definitions =
                                                data.definition
                                                    ?.filter((item: PolicyDefinition, index: number) => index !== id)
                                                    .slice() ?? [];
                                            return setDefinitions(definitions as PolicyDefinitionCollection);
                                        }}
                                    />
                                </div>
                            </div>
                            {id < (data.definition?.length ?? 1 - 1) && (
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
    );
}

const SortableThumb = forwardRef<HTMLDivElement, { className?: string }>((props, ref) => {
    return (
        <div ref={ref} {...props} className={cn(props.className ?? 'cursor-move')}>
            <GripVertical />
        </div>
    );
});
