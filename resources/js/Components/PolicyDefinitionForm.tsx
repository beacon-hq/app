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
import { Calendar } from '@/Components/ui/calendar';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/Components/ui/hover-card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Separator } from '@/Components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn, containsPolicy } from '@/lib/utils';
import { FormErrors } from '@/types/global';
import { arrayMove } from '@dnd-kit/sortable';
import {
    ChevronDownIcon,
    CircleHelp,
    CircleX,
    GripVertical,
    Infinity,
    PlusCircle,
    Trash,
    TriangleAlert,
} from 'lucide-react';
import { DateTime } from 'luxon';
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

const dateTimeOperators = [
    PolicyDefinitionMatchOperator.LESS_THAN_EQUAL,
    PolicyDefinitionMatchOperator.GREATER_THAN_EQUAL,
];

function DateTimePicker(props: {
    definition: PolicyDefinition;
    onSelect: (date: Date | undefined) => void;
    onClear: () => void;
}) {
    const [open, setOpen] = useState(false);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button variant="outline" id="date" className="w-48 justify-between font-normal">
                    {props.definition.subject &&
                    (props.definition.subject.includes('T') || !props.definition.subject.includes(':'))
                        ? DateTime.fromISO(props.definition.subject).toJSDate().toLocaleDateString()
                        : 'Any date'}
                    <ChevronDownIcon />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto overflow-hidden p-0" align="start">
                <Calendar
                    mode="single"
                    selected={DateTime.fromISO(props.definition.subject).toJSDate()}
                    captionLayout="dropdown"
                    onSelect={(value) => {
                        setOpen(false);
                        props.onSelect(value);
                    }}
                />
                <Button variant="secondary" className="w-full" onClick={props.onClear}>
                    Clear
                </Button>
            </PopoverContent>
        </Popover>
    );
}

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
        ...(data.definition === undefined || data.definition.length === 0
            ? [{ type: 'expression', subject: '', operator: '', values: [] }]
            : data.definition),
    ] as PolicyDefinitionCollection);

    useEffect(() => {
        setData('definition', definitions);
    }, [definitions]);

    const onSortEnd = (oldIndex: number, newIndex: number) => {
        setDefinitions(arrayMove(definitions, oldIndex, newIndex));
    };

    const handleAddNew = () => {
        return setDefinitions([
            ...definitions,
            { type: 'expression', subject: '', operator: '', values: [] },
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
                                            let updatedDefinitions = definitions.map(
                                                (item: PolicyDefinition, index: number) => {
                                                    if (index === id) {
                                                        return { ...item, subject: '', type: value };
                                                    }
                                                    return item;
                                                },
                                            );
                                            return setDefinitions(updatedDefinitions as PolicyDefinitionCollection);
                                        }}
                                    >
                                        <SelectTrigger id={`type_${id}`}>
                                            <SelectValue placeholder="Select a type…" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="expression">Expression</SelectItem>
                                                <SelectItem value="policy">Policy</SelectItem>
                                                <SelectItem value="datetime">Date/Time</SelectItem>
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
                                                            let updatedDefinitions = definitions.map(
                                                                (item: PolicyDefinition, index: number) => {
                                                                    if (index === id) {
                                                                        return {
                                                                            ...item,
                                                                            subject: e.target.value,
                                                                        };
                                                                    }
                                                                    return item;
                                                                },
                                                            );
                                                            return setDefinitions(
                                                                updatedDefinitions as PolicyDefinitionCollection,
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
                                                    value={definition.operator as string}
                                                    onValueChange={(value) => {
                                                        let updatedDefinitions = definitions.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, operator: value };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        return setDefinitions(
                                                            updatedDefinitions as PolicyDefinitionCollection,
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
                                                    let updatedDefinitions = definitions.map(
                                                        (item: PolicyDefinition, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }
                                                            return item;
                                                        },
                                                    );
                                                    return setDefinitions(updatedDefinitions);
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
                                    {definition.type === PolicyDefinitionType.DATETIME && (
                                        <div className="flex flex-row gap-2 items-end">
                                            <div className="flex flex-col w-1/3">
                                                <Label htmlFor={`operator_${id}`} aria-required hidden>
                                                    Operator
                                                </Label>
                                                <Select
                                                    name={`operator_${id}`}
                                                    value={definition.operator as string}
                                                    onValueChange={(value) => {
                                                        let updatedDefinitions = definitions.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, operator: value };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        return setDefinitions(
                                                            updatedDefinitions as PolicyDefinitionCollection,
                                                        );
                                                    }}
                                                >
                                                    <SelectTrigger id={`operator_${id}`}>
                                                        <SelectValue placeholder="Select an operator…" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {Object.entries(PolicyDefinitionMatchOperator)
                                                                .filter(([, operator]) => {
                                                                    return dateTimeOperators.includes(operator);
                                                                })
                                                                .map(([, operator]) => {
                                                                    return (
                                                                        <SelectItem key={operator} value={operator}>
                                                                            {operator ===
                                                                            PolicyDefinitionMatchOperator.LESS_THAN_EQUAL
                                                                                ? 'before'
                                                                                : 'after'}
                                                                        </SelectItem>
                                                                    );
                                                                })}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="w-1/3">
                                                <DateTimePicker
                                                    definition={definition}
                                                    onSelect={(date: Date | undefined) => {
                                                        let updatedDefinitions = definitions.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                let dateTime = date?.toISOString().split('T')[0];

                                                                if (
                                                                    item.subject.includes(':') &&
                                                                    !item.subject.includes('T')
                                                                ) {
                                                                    dateTime += 'T' + item.subject;
                                                                } else if (item.subject.includes('T')) {
                                                                    dateTime += 'T' + item.subject.split('T')[1];
                                                                }

                                                                if (index === id) {
                                                                    return {
                                                                        ...item,
                                                                        subject: dateTime as string,
                                                                    };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        setDefinitions(updatedDefinitions);
                                                    }}
                                                    onClear={function () {
                                                        let updatedDefinitions = definitions.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                let dateTime = item.subject;
                                                                if (dateTime.includes('T')) {
                                                                    dateTime = dateTime.split('T')[1];
                                                                } else if (!dateTime.includes(':')) {
                                                                    dateTime = '';
                                                                }

                                                                if (index === id) {
                                                                    return {
                                                                        ...item,
                                                                        subject: dateTime,
                                                                    };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        setDefinitions(updatedDefinitions);
                                                    }}
                                                />
                                            </div>
                                            <div className="w-1/3">
                                                <input
                                                    aria-label="Time"
                                                    type="time"
                                                    step={3}
                                                    value={
                                                        definition.subject.includes('T')
                                                            ? definition.subject
                                                                  .split('T')[1]
                                                                  .split('-')[0]
                                                                  .split('+')[0]
                                                                  .split('Z')[0]
                                                            : definition.subject.includes(':')
                                                              ? definition.subject
                                                                    .split('-')[0]
                                                                    .split('+')[0]
                                                                    .split('Z')[0]
                                                              : ''
                                                    }
                                                    className="h-9 w-fit rounded-md border border-input bg-transparent px-3 py-1 text-primary shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:border-0 focus-visible:ring-2 focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pr-16"
                                                    onChange={(e) => {
                                                        let updatedDefinitions = definitions.map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                let dateTime = item.subject;
                                                                if (dateTime.includes('T')) {
                                                                    dateTime =
                                                                        dateTime.split('T')[0] + 'T' + e.target.value;
                                                                } else if (
                                                                    dateTime.length > 0 &&
                                                                    !dateTime.includes(':')
                                                                ) {
                                                                    dateTime += 'T' + e.target.value;
                                                                } else {
                                                                    dateTime = e.target.value;
                                                                }

                                                                dateTime += 'Z';

                                                                if (index === id) {
                                                                    return {
                                                                        ...item,
                                                                        subject: dateTime,
                                                                    };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        setDefinitions(updatedDefinitions);
                                                    }}
                                                />
                                                <div className="inline-block -ml-16">
                                                    <p className="inline-block mr-1 text-sm">UTC</p>
                                                    <CircleX
                                                        className="inline-block"
                                                        onClick={function () {
                                                            let updatedDefinitions = definitions.map(
                                                                (item: PolicyDefinition, index: number) => {
                                                                    let dateTime = item.subject;
                                                                    if (dateTime.includes('T')) {
                                                                        dateTime = dateTime.split('T')[0];
                                                                    } else if (dateTime.includes(':')) {
                                                                        dateTime = '';
                                                                    }

                                                                    if (index === id) {
                                                                        return {
                                                                            ...item,
                                                                            subject: dateTime,
                                                                        };
                                                                    }
                                                                    return item;
                                                                },
                                                            );
                                                            setDefinitions(updatedDefinitions);
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        </div>
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
                                                    let updatedDefinitions = definitions.map(
                                                        (item: PolicyDefinition, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }
                                                            return item;
                                                        },
                                                    );
                                                    return setDefinitions(
                                                        updatedDefinitions as PolicyDefinitionCollection,
                                                    );
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
                                            key={`policy_value_editor_${id}`}
                                            value={definition.values ?? null}
                                            setValue={(values) => {
                                                let updatedDefinitions = definitions.map(
                                                    (item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, values };
                                                        }
                                                        return item;
                                                    },
                                                );
                                                return setDefinitions(updatedDefinitions as PolicyDefinitionCollection);
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
                                                'text-primary/20 cursor-not-allowed': definitions.length === 1,
                                                'cursor-move': definitions.length > 1,
                                            })}
                                        />
                                    </SortableKnob>
                                    <Trash
                                        className={cn({
                                            'text-primary/20 cursor-not-allowed': definitions.length === 1,
                                            'cursor-pointer': definitions.length > 1,
                                        })}
                                        onClick={() => {
                                            if (definitions.length === 1) {
                                                return;
                                            }

                                            let updatedDefinitions = definitions.filter(
                                                (item: PolicyDefinition, index: number) => index !== id,
                                            );
                                            return setDefinitions(updatedDefinitions as PolicyDefinitionCollection);
                                        }}
                                    />
                                </div>
                            </div>
                            {id < definitions.length - 1 && <Separator className="mt-2" />}
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
