import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Info, PlusCircle } from 'lucide-react';
import React, { useEffect, useState } from 'react';

export const PolicyValueEditor = function ({
    id,
    value,
    setValue,
    allowMultiple = false,
    disabled = false,
}: {
    id: string | number;
    value: string | string[] | null;
    setValue: (value: string | string[]) => void;
    allowMultiple: boolean;
    disabled: boolean;
}) {
    const [values, setValues] = useState<string[]>(typeof value === 'string' ? [value] : (value ?? []));
    const [currentValue, setCurrentValue] = useState<string>(allowMultiple ? '' : (values[0] ?? ''));
    const [interacted, setInteracted] = useState<boolean>(false);

    useEffect(() => {
        if (interacted) {
            setValue(values);
        }
    }, [values]);

    useEffect(() => {
        if (currentValue !== '') {
            setInteracted(true);
        }
    }, [currentValue]);

    if (allowMultiple) {
        return (
            <>
                <Label htmlFor={`value_${id}`} aria-required hidden>
                    Values
                </Label>
                <div className="flex flex-row gap-1 flex-wrap">
                    <div className="flex flex-row items-center w-full">
                        <Textarea
                            id={`value_${id}`}
                            placeholder="Enter a new value"
                            autoComplete="off"
                            className={cn('pr-10 min-h-0 h-9', {})}
                            onInput={(e) => {
                                e.currentTarget.style.height = '';
                                if (e.currentTarget.value.length > 20) {
                                    if (e.currentTarget.scrollHeight < 120) {
                                        e.currentTarget.style.height = e.currentTarget.scrollHeight + 3 + 'px';
                                    } else if (e.currentTarget.scrollHeight >= 120) {
                                        e.currentTarget.style.height = '120px';
                                    }
                                }
                            }}
                            value={currentValue}
                            onChange={function (e) {
                                setCurrentValue(e.target.value);
                            }}
                            onKeyUp={(e) => {
                                if (e.key === 'Enter' && e.shiftKey) {
                                    let tagValue = currentValue;
                                    if (!values.includes(tagValue) && tagValue !== '') {
                                        setValues([...values, tagValue]);
                                        setCurrentValue('');
                                    }
                                }
                                e.preventDefault();
                            }}
                        />
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <PlusCircle
                                        className="-ml-8"
                                        onClick={() => {
                                            let tagValue = currentValue;
                                            if (!values.includes(tagValue) && tagValue !== '') {
                                                setValues([...values, tagValue]);
                                                setCurrentValue('');
                                            }
                                        }}
                                    />
                                </TooltipTrigger>
                                <TooltipContent>
                                    <Info className="inline-block" /> Press <kbd>Shift</kbd> + <kbd>Enter</kbd> to add a
                                    new tag
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                    {values.map((tag) => (
                        <div key={tag}>
                            {tag.length <= 25 && (
                                <Badge
                                    variant="default"
                                    className="cursor-pointer"
                                    onClick={() => setValues(values.filter((value) => value !== tag))}
                                >
                                    <span className="truncate max-w-40">{tag}</span>
                                </Badge>
                            )}
                            {tag.length > 25 && (
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Badge
                                                variant="default"
                                                className="cursor-pointer"
                                                onClick={() => setValues(values.filter((value) => value !== tag))}
                                            >
                                                <span className="truncate max-w-40">{tag}</span>
                                            </Badge>
                                        </TooltipTrigger>
                                        <TooltipContent className="whitespace-pre-wrap">{tag}</TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            )}
                        </div>
                    ))}
                </div>
            </>
        );
    }

    return (
        <>
            <Label htmlFor={`value_${id}`} aria-required hidden>
                Value
            </Label>
            <Input
                id={`value_${id}`}
                type="text"
                value={currentValue}
                placeholder="Enter a value"
                autoComplete="off"
                onChange={function (e) {
                    setCurrentValue(e.target.value);
                    setValues([e.target.value]);
                }}
                onBlur={(e) => {
                    setCurrentValue(e.target.value);
                    setValues([e.target.value]);
                }}
                disabled={disabled}
            />
        </>
    );
};
