import { Badge } from '@/Components/ui/badge';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Info, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

const MultiValueInput = ({
    disabled = false,
    id,
    setValues,
    values,
    type = 'textarea',
}: {
    id: string;
    values: string[];
    setValues: (value: ((prevState: string[]) => string[]) | string[]) => void;
    disabled?: boolean;
    type?: string;
}) => {
    const [currentValue, setCurrentValue] = useState<string>('');

    const handleInput = (e) => {
        e.preventDefault();
        e.currentTarget.style.height = '';
        if (e.currentTarget.value.length > 20) {
            if (e.currentTarget.scrollHeight < 120) {
                e.currentTarget.style.height = e.currentTarget.scrollHeight + 3 + 'px';
            } else if (e.currentTarget.scrollHeight >= 120) {
                e.currentTarget.style.height = '120px';
            }
        }
    };

    const handleChange = function (e) {
        e.preventDefault();
        setCurrentValue(e.target.value);
    };

    const handleKeyUp = (e) => {
        e.preventDefault();
        if (e.key === 'Enter' && e.shiftKey) {
            let value = currentValue;
            if (!values.includes(value) && value !== '') {
                setValues([...values, value]);
                setCurrentValue('');
            }
        }
    };

    return (
        <div className="flex flex-row gap-1 flex-wrap">
            <div className="flex flex-row items-center w-full">
                {type === 'textarea' && (
                    <Textarea
                        id={`value_${id}`}
                        placeholder="Enter a new value"
                        autoComplete="off"
                        className={cn('pr-10 min-h-0 h-9', {})}
                        onInput={handleInput}
                        value={currentValue}
                        onChange={handleChange}
                        onKeyUp={handleKeyUp}
                        disabled={disabled}
                    />
                )}
                {type !== 'textarea' && (
                    <input
                        id={`value_${id}`}
                        type={type}
                        placeholder="Enter a new value"
                        className={cn('pr-10 min-h-0 h-9', {})}
                        onInput={handleInput}
                        value={currentValue}
                        onChange={handleChange}
                        onKeyUp={handleKeyUp}
                        disabled={disabled}
                    />
                )}
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <PlusCircle
                                className="-ml-8"
                                onClick={() => {
                                    let value = currentValue;
                                    if (!values.includes(value) && value !== '') {
                                        setValues([...values, value]);
                                        setCurrentValue('');
                                    }
                                }}
                            />
                        </TooltipTrigger>
                        <TooltipContent>
                            <Info className="inline-block" /> Press <kbd>Shift</kbd> + <kbd>Enter</kbd> to add a new
                            value
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
            {values.map((value) => (
                <div key={value}>
                    {value.length <= 25 && (
                        <Badge
                            variant="default"
                            className="cursor-pointer"
                            onClick={() => setValues(values.filter((v) => v !== value))}
                        >
                            <span className="truncate max-w-40">{value}</span>
                        </Badge>
                    )}
                    {value.length > 25 && (
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <Badge
                                        variant="default"
                                        className="cursor-pointer"
                                        onClick={() => setValues(values.filter((value) => value !== value))}
                                    >
                                        <span className="truncate max-w-40">{value}</span>
                                    </Badge>
                                </TooltipTrigger>
                                <TooltipContent className="whitespace-pre-wrap">{value}</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            ))}
        </div>
    );
};

export default MultiValueInput;
