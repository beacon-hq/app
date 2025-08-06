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
import { TimePicker } from '@/Components/ui/time-picker';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn, containsPolicy } from '@/lib/utils';
import { useFeatureFlagStore } from '@/stores/featureFlagStore';
import { usePolicyStore } from '@/stores/policyStore';
import { arrayMove } from '@dnd-kit/sortable';
import {
    ChevronDownIcon,
    CircleHelp,
    CircleX,
    GripVertical,
    Infinity,
    Info,
    PlusCircle,
    Trash,
    TriangleAlert,
} from 'lucide-react';
import { DateTime } from 'luxon';
import React, { forwardRef, useEffect, useState } from 'react';
import SortableList, { SortableItem, SortableKnob } from 'react-easy-sort';

type DateTimeValue = {
    date?: string;
    time?: string;
    utcOffset?: string;
    combined?: string;
};

const AVAILABLE_UTC_OFFSETS = [
    { offset: '-12:00', label: 'UTC-12:00' },
    { offset: '-11:00', label: 'UTC-11:00' },
    { offset: '-10:00', label: 'UTC-10:00' },
    { offset: '-09:30', label: 'UTC-09:30' },
    { offset: '-09:00', label: 'UTC-09:00' },
    { offset: '-08:00', label: 'UTC-08:00' },
    { offset: '-07:00', label: 'UTC-07:00' },
    { offset: '-06:00', label: 'UTC-06:00' },
    { offset: '-05:00', label: 'UTC-05:00' },
    { offset: '-04:00', label: 'UTC-04:00' },
    { offset: '-03:30', label: 'UTC-03:30' },
    { offset: '-03:00', label: 'UTC-03:00' },
    { offset: '-02:00', label: 'UTC-02:00' },
    { offset: '-01:00', label: 'UTC-01:00' },
    { offset: '+00:00', label: 'UTC+00:00' },
    { offset: '+01:00', label: 'UTC+01:00' },
    { offset: '+03:00', label: 'UTC+03:00' },
    { offset: '+03:30', label: 'UTC+03:30' },
    { offset: '+04:00', label: 'UTC+04:00' },
    { offset: '+04:30', label: 'UTC+04:30' },
    { offset: '+05:00', label: 'UTC+05:00' },
    { offset: '+05:30', label: 'UTC+05:30' },
    { offset: '+05:45', label: 'UTC+05:45' },
    { offset: '+06:00', label: 'UTC+06:00' },
    { offset: '+06:30', label: 'UTC+06:30' },
    { offset: '+07:00', label: 'UTC+07:00' },
    { offset: '+09:30', label: 'UTC+09:30' },
    { offset: '+10:00', label: 'UTC+10:00' },
    { offset: '+10:30', label: 'UTC+10:30' },
    { offset: '+11:00', label: 'UTC+11:00' },
    { offset: '+13:00', label: 'UTC+13:00' },
    { offset: '+14:00', label: 'UTC+14:00' },
];

const getUserTimezoneOffset = (): string => {
    const date = new Date();
    const offsetMinutes = -date.getTimezoneOffset();
    const hours = Math.floor(Math.abs(offsetMinutes) / 60);
    const minutes = Math.abs(offsetMinutes) % 60;
    const sign = offsetMinutes >= 0 ? '+' : '-';

    return `${sign}${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
};

const getDefaultOffset = (): string => {
    const userOffset = getUserTimezoneOffset();
    const isAllowed = AVAILABLE_UTC_OFFSETS.some((item) => item.offset === userOffset);
    return isAllowed ? userOffset : '+00:00';
};

function parseDateTimeValue(subject: string): DateTimeValue {
    if (!subject) return { utcOffset: '' };

    if (subject.includes('T')) {
        const [datePart, timePart] = subject.split('T');

        let utcOffset = '+00:00';
        let cleanTime = timePart;

        if (timePart.includes('+') || timePart.includes('-')) {
            const offsetMatch = timePart.match(/([+-]\d{2}:\d{2})$/);
            if (offsetMatch) {
                utcOffset = offsetMatch[1];
                cleanTime = timePart.replace(offsetMatch[1], '');
            }
        } else if (timePart.endsWith('Z')) {
            utcOffset = '+00:00';
            cleanTime = timePart.replace('Z', '');
        }

        return {
            date: datePart,
            time: cleanTime,
            utcOffset,
            combined: subject,
        };
    }

    if (subject.includes(':')) {
        let utcOffset = '+00:00';
        let cleanTime = subject;

        if (subject.includes('+') || subject.includes('-')) {
            const offsetMatch = subject.match(/([+-]\d{2}:\d{2})$/);
            if (offsetMatch) {
                utcOffset = offsetMatch[1];
                cleanTime = subject.replace(offsetMatch[1], '');
            }
        } else if (subject.endsWith('Z')) {
            utcOffset = '+00:00';
            cleanTime = subject.replace('Z', '');
        }

        return {
            time: cleanTime,
            utcOffset,
            combined: subject,
        };
    }

    if (subject.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return {
            date: subject,
            utcOffset: '',
            combined: subject,
        };
    }

    return {
        combined: subject,
        utcOffset: '',
    };
}

function combineDateTimeValue(dateValue?: string, timeValue?: string, utcOffset?: string): string {
    if (dateValue && timeValue) {
        if (!utcOffset) {
            return `${dateValue}T${timeValue}`;
        }
        if (utcOffset === '+00:00') {
            return `${dateValue}T${timeValue}Z`;
        }
        return `${dateValue}T${timeValue}${utcOffset}`;
    }

    if (dateValue) {
        return dateValue;
    }

    if (timeValue) {
        if (!utcOffset) {
            return timeValue;
        }
        if (utcOffset === '+00:00') {
            return `${timeValue}Z`;
        }
        return `${timeValue}${utcOffset}`;
    }

    return '';
}

function formatTimeForDisplay(timeStr: string): Date | null {
    if (!timeStr) return null;

    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    const date = new Date();
    date.setHours(hours || 0, minutes || 0, seconds || 0, 0);
    return date;
}

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

function UtcOffsetSelector({
    value,
    onChange,
    disabled,
    'data-dusk': dataDusk,
}: {
    value: string;
    onChange: (offset: string) => void;
    disabled?: boolean;
    'data-dusk'?: string;
}) {
    const userOffset = getUserTimezoneOffset();

    return (
        <Select value={value} onValueChange={onChange} disabled={disabled}>
            <SelectTrigger data-dusk={dataDusk}>
                <SelectValue placeholder="Choose a timezone…" />
            </SelectTrigger>
            <SelectContent className="max-h-60 overflow-y-auto">
                {AVAILABLE_UTC_OFFSETS.map(({ offset, label }, index) => (
                    <SelectItem
                        key={offset}
                        value={offset}
                        data-dusk={dataDusk ? `${dataDusk}-option-${index}` : undefined}
                    >
                        <div
                            className={cn('flex items-center', {
                                'text-blue-500': offset === userOffset,
                                'text-green-500': offset === '+00:00',
                            })}
                        >
                            {label}
                        </div>
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function DateTimePicker(props: {
    definition: PolicyDefinition;
    onSelect: (date: Date | undefined) => void;
    onClear: () => void;
    'data-dusk'?: string;
}) {
    const [open, setOpen] = useState(false);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    id="date"
                    className="w-48 justify-between font-normal"
                    data-dusk={props['data-dusk']}
                >
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
                    data-dusk={props['data-dusk'] ? `${props['data-dusk']}-calendar` : undefined}
                />
                <Button
                    variant="secondary"
                    className="w-full"
                    onClick={props.onClear}
                    data-dusk={props['data-dusk'] ? `${props['data-dusk']}-clear` : undefined}
                >
                    Clear
                </Button>
            </PopoverContent>
        </Popover>
    );
}

export function PolicyDefinitionForm({
    status,
    policies,
    ...props
}: {
    status?: FeatureFlagStatus;
    policy?: Policy;
    policies?: PolicyCollection;
}) {
    const { updateStatus, addStatusPolicyDefinition } = useFeatureFlagStore();
    const { policy, updatePolicy, addPolicyDefinition } = usePolicyStore();

    function getDefinitions() {
        return status?.definition ?? policy?.definition ?? [];
    }

    const onSortEnd = (oldIndex: number, newIndex: number) => {
        const definitions = getDefinitions();
        const newOrder = arrayMove(definitions, oldIndex, newIndex);

        if (status !== null) {
            updateStatus({
                ...status,
                definition: newOrder as PolicyDefinitionCollection,
            } as FeatureFlagStatus);
        }

        if (policy !== null) {
            updatePolicy({
                ...policy,
                definition: newOrder as PolicyDefinitionCollection,
            });
        }
    };

    const addNewPolicyDefinition = () => {
        if (status !== null) {
            addStatusPolicyDefinition(status?.id as string);
        }

        if (policy !== null) {
            addPolicyDefinition();
        }
    };

    const updatePolicyDefinitions = (definitions: PolicyDefinitionCollection) => {
        if (status !== null) {
            updateStatus({
                ...status,
                definition: definitions,
            } as FeatureFlagStatus);
        }

        if (policy !== null) {
            updatePolicy({
                ...policy,
                definition: definitions,
            });
        }
    };

    return (
        <div className="border p-2 flex flex-col gap-4" data-dusk="policies-form-definition">
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
                {getDefinitions().map((definition: PolicyDefinition, id: number) => (
                    <SortableItem key={id}>
                        <div
                            className={cn('bg-background w-full mb-2 pt-2', {
                                'bg-red-50': id === 0 && definition.type == PolicyDefinitionType.OPERATOR,
                            })}
                        >
                            <div className="flex gap-2 items-start p-2">
                                {id === 0 && definition.type === PolicyDefinitionType.OPERATOR && (
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <TriangleAlert className="text-red-500 w-6 h-6" />
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
                                <div className="w-32">
                                    <Label htmlFor={`type_${id}`} aria-required hidden>
                                        Type
                                    </Label>
                                    <Select
                                        value={definition.type}
                                        onValueChange={(value) => {
                                            let updatedDefinitions = getDefinitions().map(
                                                (item: PolicyDefinition, index: number) => {
                                                    if (index === id) {
                                                        return { ...item, subject: '', type: value };
                                                    }
                                                    return item;
                                                },
                                            );
                                            return updatePolicyDefinitions(
                                                updatedDefinitions as PolicyDefinitionCollection,
                                            );
                                        }}
                                    >
                                        <SelectTrigger
                                            id={`type_${id}`}
                                            data-dusk={`select-policies-definition-type-${id}`}
                                        >
                                            <SelectValue placeholder="Select a type…" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem
                                                    value={PolicyDefinitionType.EXPRESSION}
                                                    data-dusk={`select-option-policies-definition-type-${id}-expression`}
                                                >
                                                    Expression
                                                </SelectItem>
                                                <SelectItem
                                                    value={PolicyDefinitionType.POLICY}
                                                    data-dusk={`select-option-policies-definition-type-${id}-policy`}
                                                >
                                                    Policy
                                                </SelectItem>
                                                <SelectItem
                                                    value={PolicyDefinitionType.DATETIME}
                                                    data-dusk={`select-option-policies-definition-type-${id}-datetime`}
                                                >
                                                    Date/Time
                                                </SelectItem>
                                                <SelectItem
                                                    value={PolicyDefinitionType.OPERATOR}
                                                    data-dusk={`select-option-policies-definition-type-${id}-operator`}
                                                >
                                                    Operator
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="flex-1">
                                    {definition.type === PolicyDefinitionType.EXPRESSION && (
                                        <div className="flex gap-2">
                                            <div className="flex-1">
                                                <Label htmlFor={`subject_${id}`} aria-required hidden>
                                                    Context Property
                                                </Label>
                                                <Input
                                                    id={`subject_${id}`}
                                                    type="text"
                                                    value={definition.subject}
                                                    autoComplete="off"
                                                    placeholder="Context property"
                                                    onChange={function (e) {
                                                        let updatedDefinitions = getDefinitions().map(
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
                                                        return updatePolicyDefinitions(
                                                            updatedDefinitions as PolicyDefinitionCollection,
                                                        );
                                                    }}
                                                    data-dusk={`input-policies-definition-subject-${id}`}
                                                />
                                            </div>
                                            <div className="flex-1">
                                                <Label htmlFor={`operator_${id}`} aria-required hidden>
                                                    Operator
                                                </Label>
                                                <Select
                                                    name={`operator_${id}`}
                                                    value={definition.operator as string}
                                                    onValueChange={(value) => {
                                                        let updatedDefinitions = getDefinitions().map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, operator: value };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        return updatePolicyDefinitions(
                                                            updatedDefinitions as PolicyDefinitionCollection,
                                                        );
                                                    }}
                                                >
                                                    <SelectTrigger
                                                        id={`operator_${id}`}
                                                        data-dusk={`select-policies-definition-operator-${id}`}
                                                    >
                                                        <SelectValue placeholder="Select an operator…" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {Object.entries(PolicyDefinitionMatchOperator).map(
                                                                ([, operator]) => {
                                                                    return (
                                                                        <SelectItem
                                                                            key={operator}
                                                                            value={operator}
                                                                            data-dusk={`select-option-policies-definition-operator-${id}-${operator}`}
                                                                        >
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
                                                    let updatedDefinitions = getDefinitions().map(
                                                        (item: PolicyDefinition, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }
                                                            return item;
                                                        },
                                                    );
                                                    return updatePolicyDefinitions(updatedDefinitions);
                                                }}
                                            >
                                                <SelectTrigger data-dusk={`select-policies-definition-policy-${id}`}>
                                                    <SelectValue placeholder="Select a policy…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {policies
                                                            ?.filter(function (p: Policy) {
                                                                return (
                                                                    policy === null ||
                                                                    ('id' in policy && p.id != policy.id)
                                                                );
                                                            })
                                                            .map(function (p: Policy, idx) {
                                                                const disabled =
                                                                    policy !== null && 'id' in policy
                                                                        ? containsPolicy(p, policy as Policy, policies)
                                                                        : false;
                                                                return (
                                                                    <SelectItem
                                                                        key={`${id}-${p.id}`}
                                                                        value={p.id as string}
                                                                        disabled={disabled}
                                                                        data-dusk={`select-option-policies-definition-policy-${id}-${idx}`}
                                                                    >
                                                                        <div className="w-full flex flex-row items-center justify-between">
                                                                            {disabled ? (
                                                                                <Infinity className="inline-block text-red-700 mr-2" />
                                                                            ) : null}
                                                                            {p.name}
                                                                        </div>
                                                                    </SelectItem>
                                                                );
                                                            })}
                                                        {policies?.filter(
                                                            (p: Policy) =>
                                                                policy === null ||
                                                                ('id' in policy && p.id != policy.id),
                                                        ).length === 0 && (
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
                                        <div className="flex gap-2 items-center">
                                            <div className="w-32">
                                                <Label htmlFor={`operator_${id}`} aria-required hidden>
                                                    Operator
                                                </Label>
                                                <Select
                                                    name={`operator_${id}`}
                                                    value={definition.operator as string}
                                                    onValueChange={(value) => {
                                                        let updatedDefinitions = getDefinitions().map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    return { ...item, operator: value };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        return updatePolicyDefinitions(
                                                            updatedDefinitions as PolicyDefinitionCollection,
                                                        );
                                                    }}
                                                >
                                                    <SelectTrigger
                                                        id={`operator_${id}`}
                                                        data-dusk={`select-policies-definition-datetime-${id}`}
                                                    >
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
                                                                        <SelectItem
                                                                            key={operator}
                                                                            value={operator}
                                                                            data-dusk={`select-option-policies-definition-datetime-${id}-${operator === PolicyDefinitionMatchOperator.LESS_THAN_EQUAL ? 'before' : 'after'}`}
                                                                        >
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
                                            <div className="flex-1 flex items-center">
                                                <DateTimePicker
                                                    definition={definition}
                                                    onSelect={(date: Date | undefined) => {
                                                        let updatedDefinitions = getDefinitions().map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    const parsed = parseDateTimeValue(item.subject);
                                                                    const dateStr = date?.toISOString().split('T')[0];
                                                                    const newSubject = combineDateTimeValue(
                                                                        dateStr,
                                                                        parsed.time,
                                                                        parsed.utcOffset,
                                                                    );
                                                                    return {
                                                                        ...item,
                                                                        subject: newSubject,
                                                                    };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        updatePolicyDefinitions(updatedDefinitions);
                                                    }}
                                                    onClear={function () {
                                                        let updatedDefinitions = getDefinitions().map(
                                                            (item: PolicyDefinition, index: number) => {
                                                                if (index === id) {
                                                                    const parsed = parseDateTimeValue(item.subject);
                                                                    const newSubject = parsed.time
                                                                        ? combineDateTimeValue(
                                                                              undefined,
                                                                              parsed.time,
                                                                              parsed.utcOffset,
                                                                          )
                                                                        : '';
                                                                    return {
                                                                        ...item,
                                                                        subject: newSubject,
                                                                    };
                                                                }
                                                                return item;
                                                            },
                                                        );
                                                        updatePolicyDefinitions(updatedDefinitions);
                                                    }}
                                                    data-dusk={`datepicker-policies-definition-datetime-${id}`}
                                                />
                                                <div className="flex flex-row gap-2 ml-4">
                                                    <TimePicker
                                                        date={(() => {
                                                            const parsed = parseDateTimeValue(definition.subject);
                                                            return parsed.time
                                                                ? formatTimeForDisplay(parsed.time)
                                                                : null;
                                                        })()}
                                                        onChange={(date) => {
                                                            if (!date) return;

                                                            const timeStr = date.toTimeString().split(' ')[0];
                                                            let updatedDefinitions = getDefinitions().map(
                                                                (item: PolicyDefinition, index: number) => {
                                                                    if (index === id) {
                                                                        const parsed = parseDateTimeValue(item.subject);
                                                                        const newSubject = combineDateTimeValue(
                                                                            parsed.date,
                                                                            timeStr,
                                                                            parsed.utcOffset,
                                                                        );
                                                                        return {
                                                                            ...item,
                                                                            subject: newSubject,
                                                                        };
                                                                    }
                                                                    return item;
                                                                },
                                                            );
                                                            updatePolicyDefinitions(updatedDefinitions);
                                                        }}
                                                        hourCycle={24}
                                                        granularity="second"
                                                        data-dusk={`timepicker-policies-definition-datetime-${id}`}
                                                    />
                                                    <div className="flex items-center">
                                                        <UtcOffsetSelector
                                                            value={(() => {
                                                                const parsed = parseDateTimeValue(definition.subject);
                                                                return parsed.utcOffset || '';
                                                            })()}
                                                            disabled={(() => {
                                                                const parsed = parseDateTimeValue(definition.subject);
                                                                return !parsed.time;
                                                            })()}
                                                            onChange={(utcOffset) => {
                                                                let updatedDefinitions = getDefinitions().map(
                                                                    (item: PolicyDefinition, index: number) => {
                                                                        if (index === id) {
                                                                            const parsed = parseDateTimeValue(
                                                                                item.subject,
                                                                            );
                                                                            const newSubject = combineDateTimeValue(
                                                                                parsed.date,
                                                                                parsed.time,
                                                                                utcOffset,
                                                                            );
                                                                            return {
                                                                                ...item,
                                                                                subject: newSubject,
                                                                            };
                                                                        }
                                                                        return item;
                                                                    },
                                                                );
                                                                updatePolicyDefinitions(updatedDefinitions);
                                                            }}
                                                            data-dusk={`select-policies-definition-timezone-${id}`}
                                                        />
                                                        <Tooltip>
                                                            <TooltipTrigger>
                                                                <CircleX
                                                                    className="h-6 w-6 cursor-pointer text-muted-foreground hover:text-foreground ml-2"
                                                                    onClick={function () {
                                                                        let updatedDefinitions = getDefinitions().map(
                                                                            (item: PolicyDefinition, index: number) => {
                                                                                if (index === id) {
                                                                                    const parsed = parseDateTimeValue(
                                                                                        item.subject,
                                                                                    );
                                                                                    const newSubject =
                                                                                        parsed.date || '';
                                                                                    return {
                                                                                        ...item,
                                                                                        subject: newSubject,
                                                                                    };
                                                                                }
                                                                                return item;
                                                                            },
                                                                        );
                                                                        updatePolicyDefinitions(updatedDefinitions);
                                                                    }}
                                                                    data-dusk={`button-policies-definition-clear-time-${id}`}
                                                                />
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                <Info className="inline-block" /> Clear Time
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </div>
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
                                                    let updatedDefinitions = getDefinitions().map(
                                                        (item: PolicyDefinition, index: number) => {
                                                            if (index === id) {
                                                                return { ...item, subject: value };
                                                            }
                                                            return item;
                                                        },
                                                    );
                                                    return updatePolicyDefinitions(
                                                        updatedDefinitions as PolicyDefinitionCollection,
                                                    );
                                                }}
                                            >
                                                <SelectTrigger data-dusk={`select-policies-definition-operator-${id}`}>
                                                    <SelectValue placeholder="Select an operator…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {Object.entries(Boolean).map(([, operator]) => {
                                                            return (
                                                                <SelectItem
                                                                    key={operator}
                                                                    value={operator}
                                                                    data-dusk={`select-option-policies-definition-operator-${id}-${operator}`}
                                                                >
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
                                    <div className="w-48">
                                        <PolicyValueEditor
                                            id={id}
                                            key={`policy_value_editor_${id}`}
                                            value={definition.values ?? null}
                                            setValue={(values) => {
                                                let updatedDefinitions = getDefinitions().map(
                                                    (item: PolicyDefinition, index: number) => {
                                                        if (index === id) {
                                                            return { ...item, values };
                                                        }
                                                        return item;
                                                    },
                                                );
                                                return updatePolicyDefinitions(
                                                    updatedDefinitions as PolicyDefinitionCollection,
                                                );
                                            }}
                                            allowMultiple={
                                                !definition.operator
                                                    ? false
                                                    : !singleValueOperators.includes(definition.operator)
                                            }
                                            disabled={!definition.operator}
                                            data-dusk={`input-policies-definition-value-${id}`}
                                        />
                                    </div>
                                )}
                                <div className="flex pt-2">
                                    <SortableKnob>
                                        <SortableThumb
                                            className={cn({
                                                'text-primary/20 cursor-not-allowed': getDefinitions().length === 1,
                                                'cursor-move': getDefinitions().length > 1,
                                            })}
                                            data-dusk={`button-policies-definition-drag-${id}`}
                                        />
                                    </SortableKnob>
                                    <Trash
                                        className={cn({
                                            'text-primary/20 cursor-not-allowed': getDefinitions().length === 1,
                                            'cursor-pointer': getDefinitions().length > 1,
                                        })}
                                        onClick={() => {
                                            if (getDefinitions().length === 1) {
                                                return;
                                            }

                                            let updatedDefinitions = getDefinitions().filter(
                                                (item: PolicyDefinition, index: number) => index !== id,
                                            );
                                            return updatePolicyDefinitions(
                                                updatedDefinitions as PolicyDefinitionCollection,
                                            );
                                        }}
                                        data-dusk={`button-policies-definition-delete-${id}`}
                                    />
                                </div>
                            </div>
                            {id < getDefinitions().length - 1 && <Separator className="mt-2" />}
                        </div>
                    </SortableItem>
                ))}
            </SortableList>
            <div className="flex justify-center">
                <Button
                    type="button"
                    onClick={addNewPolicyDefinition}
                    variant="outline"
                    data-dusk="button-policies-add"
                >
                    <PlusCircle /> Add
                </Button>
            </div>
        </div>
    );
}

const SortableThumb = forwardRef<HTMLDivElement, { className?: string; 'data-dusk'?: string }>((props, ref) => {
    return (
        <div ref={ref} {...props} className={cn(props.className ?? 'cursor-move')}>
            <GripVertical />
        </div>
    );
});
