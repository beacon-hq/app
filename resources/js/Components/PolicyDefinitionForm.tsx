import {
    Boolean,
    FeatureFlagStatus,
    Policy,
    PolicyCollection,
    PolicyDefinition,
    PolicyDefinitionCollection,
    PolicyDefinitionMatchOperator,
    PolicyDefinitionType,
} from '@/Application';
import { PolicyValueEditor } from '@/Components/PolicyValueEditor';
import { Button } from '@/Components/ui/button';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/Components/ui/hover-card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Separator } from '@/Components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn, containsPolicy } from '@/lib/utils';
import { FormErrors } from '@/types/global';
import { arrayMove } from '@dnd-kit/sortable';
import { CircleHelp, GripVertical, Infinity, PlusCircle, Trash, TriangleAlert } from 'lucide-react';
import React, { forwardRef, useEffect, useState } from 'react';
import SortableList, { SortableItem, SortableKnob } from 'react-easy-sort';

const singleValueOperators = [
    PolicyDefinitionMatchOperator.EQUAL,
    PolicyDefinitionMatchOperator.NOT_EQUAL,
    PolicyDefinitionMatchOperator.GREATER_THAN,
    PolicyDefinitionMatchOperator.GREATER_THAN_EQUAL,
    PolicyDefinitionMatchOperator.LESS_THAN,
    PolicyDefinitionMatchOperator.LESS_THAN_EQUAL,
];

const multiValueOperators = [
    PolicyDefinitionMatchOperator.CONTAINS_ALL,
    PolicyDefinitionMatchOperator.NOT_CONTAINS_ALL,
    PolicyDefinitionMatchOperator.CONTAINS_ANY,
    PolicyDefinitionMatchOperator.NOT_CONTAINS_ANY,
    PolicyDefinitionMatchOperator.MATCHES_ANY,
    PolicyDefinitionMatchOperator.NOT_MATCHES_ANY,
    PolicyDefinitionMatchOperator.MATCHES_ALL,
    PolicyDefinitionMatchOperator.NOT_MATCHES_ALL,
    PolicyDefinitionMatchOperator.ONE_OF,
    PolicyDefinitionMatchOperator.NOT_ONE_OF,
];

export function PolicyDefinitionForm({
    data,
    setData,
    errors,
    processing,
    policies,
}: {
    data: Policy | FeatureFlagStatus;
    setData: (key: keyof Policy, value: any) => void;
    errors: FormErrors;
    processing: any;
    policies?: PolicyCollection;
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
            <HoverCard>
                <HoverCardTrigger asChild>
                    <CircleHelp className="ml-auto" />
                </HoverCardTrigger>
                <HoverCardContent className="prose">
                    <h4>Context Properties</h4>
                    <p>
                        Context properties are used to define which value from the Pennant context to match against when
                        evaluating the policy. You can use dot-notation to access nested properties and array keys (e.g.{' '}
                        <code>user.id</code>).
                    </p>
                </HoverCardContent>
            </HoverCard>
            <SortableList onSortEnd={onSortEnd} className="list" draggedItemClassName="dragged" lockAxis="y">
                {definitions.map((definition: PolicyDefinition, id: number) => (
                    <SortableItem key={id}>
                        <div
                            className={cn('bg-background w-full mb-2 pt-2', {
                                'bg-red-50': id === 0 && definition.type == PolicyDefinitionType.OPERATOR,
                            })}
                        >
                            <div className="flex justify-between gap-2 items-start p-2">
                                {id === 0 && definition.type === PolicyDefinitionType.OPERATOR && (
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <TriangleAlert className="text-red-500 w-6 h-6 inline-block" />
                                        </TooltipTrigger>
                                        <TooltipContent
                                            className={cn('bg-red-400', {
                                                hidden: id !== 0 || definition.type !== PolicyDefinitionType.OPERATOR,
                                            })}
                                        >
                                            <p>The first item cannot be an operator.</p>
                                        </TooltipContent>
                                    </Tooltip>
                                )}
                                <div className="flex flex-col grow-0 w-1/6">
                                    <Label htmlFor={`type_${id}`} aria-required hidden>
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
                                        <SelectTrigger id={`type_${id}`}>
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
                                <div className={cn('flex flex-col grow w-1/2')}>
                                    {definition.type === PolicyDefinitionType.EXPRESSION && (
                                        <div className="flex flex-row gap-2 items-end">
                                            <div className="w-1/2">
                                                <Label htmlFor={`subject_${id}`} aria-required hidden>
                                                    Context Property
                                                </Label>
                                                <div className="flex flex-row items-center w-full">
                                                    <Input
                                                        id={`subject_${id}`}
                                                        type="text"
                                                        value={definition.subject}
                                                        autoComplete="off"
                                                        placeholder="Context property"
                                                        onChange={function (e) {
                                                            let definitions =
                                                                data.definition?.map(
                                                                    (item: PolicyDefinition, index: number) => {
                                                                        if (index === id) {
                                                                            return {
                                                                                ...item,
                                                                                subject: e.target.value,
                                                                            };
                                                                        }

                                                                        return item;
                                                                    },
                                                                ) ?? [];

                                                            return setDefinitions(
                                                                definitions as PolicyDefinitionCollection,
                                                            );
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex flex-col w-1/2">
                                                <Label htmlFor={`operator_${id}`} aria-required hidden>
                                                    Operator
                                                </Label>
                                                <Select
                                                    name={`operator_${id}`}
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

                                                        return setDefinitions(
                                                            definitions as PolicyDefinitionCollection,
                                                        );
                                                    }}
                                                >
                                                    <SelectTrigger id={`operator_${id}`}>
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
                                        </div>
                                    )}
                                    {definition.type === PolicyDefinitionType.POLICY && (
                                        <>
                                            <Label htmlFor={`subject_${id}`} aria-required hidden>
                                                Policy
                                            </Label>
                                            <Select
                                                name={`subject_${id}`}
                                                value={definition.subject}
                                                onValueChange={(value) => {
                                                    let definitions =
                                                        data.definition?.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, subject: value };
                                                                }

                                                                return item;
                                                            },
                                                        ) ?? [];

                                                    return setDefinitions(definitions);
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a policy…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {policies
                                                            ?.filter((policy: Policy) => policy.id != data.id)
                                                            .map(function (policy: Policy) {
                                                                const disabled = containsPolicy(policy, data, policies);
                                                                return (
                                                                    <SelectItem
                                                                        key={`${id}-${policy.id}`}
                                                                        value={policy.id as string}
                                                                        disabled={disabled}
                                                                    >
                                                                        <div className="w-full flex flex-row items-center justify-between">
                                                                            {disabled ? (
                                                                                <Infinity className="inline-block text-red-700 mr-2" />
                                                                            ) : null}
                                                                            {policy.name}
                                                                        </div>
                                                                    </SelectItem>
                                                                );
                                                            })}
                                                        {policies?.filter((policy: Policy) => policy.id != data.id)
                                                            .length === 0 && (
                                                            <SelectItem value="none" disabled>
                                                                No policies found.
                                                            </SelectItem>
                                                        )}
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </>
                                    )}
                                    {definition.type === PolicyDefinitionType.OPERATOR && (
                                        <>
                                            <Label htmlFor={`subject_${id}`} aria-required hidden>
                                                Operator
                                            </Label>
                                            <Select
                                                name={`subject_${id}`}
                                                value={definition.subject}
                                                onValueChange={(value) => {
                                                    let definitions =
                                                        data.definition?.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, subject: value };
                                                                }

                                                                return item;
                                                            },
                                                        ) ?? [];

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
                                        </>
                                    )}
                                </div>
                                {definition.type === PolicyDefinitionType.EXPRESSION && (
                                    <div className="w-1/3">
                                        <PolicyValueEditor
                                            id={id}
                                            key={id}
                                            value={definition.values}
                                            setValue={(values) => {
                                                let definitions =
                                                    data.definition?.map((item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, values };
                                                        }
                                                        return item;
                                                    }) ?? [];

                                                return setDefinitions(definitions as PolicyDefinitionCollection);
                                            }}
                                            allowMultiple={
                                                !definition.operator
                                                    ? false
                                                    : !singleValueOperators.includes(definition.operator)
                                            }
                                            disabled={!definition.operator}
                                        />
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
                            {id < (data.definition?.length ?? 1) - 1 && <Separator className="mt-2" />}
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
