import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Info, PlusCircle } from 'lucide-react';
import React, { useEffect, useState } from 'react';

const MultiValueInput = ({
    disabled = false,
    id,
    setValues,
    values,
    placeholder = 'Enter a new value',
    type = 'textarea',
    className = '',
}: {
    id: string;
    values: string[];
    setValues: (value: ((prevState: string[]) => string[]) | string[]) => void;
    placeholder?: string;
    disabled?: boolean;
    type?: string;
    className?: string;
}) => {
    const [currentValue, setCurrentValue] = useState<string>('');
    const [currentValues, setCurrentValues] = useState<string[]>(values);

    // Sync local state when the values prop changes from parent
    useEffect(() => {
        if (JSON.stringify(values) !== JSON.stringify(currentValues)) {
            setCurrentValues(values);
        }
    }, [values]);

    useEffect(() => {
        // Only call setValues if the values have actually changed
        if (JSON.stringify(values) !== JSON.stringify(currentValues)) {
            setValues(currentValues);
        }
    }, [currentValues, setValues, values]);

    const handleInput = (e: React.FormEvent<HTMLTextAreaElement | HTMLInputElement>) => {
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

    const handleChange = function (e: React.ChangeEvent<HTMLTextAreaElement | HTMLInputElement>) {
        e.preventDefault();
        setCurrentValue(e.target.value);
    };

    const handleKeyUp = (e: React.KeyboardEvent<HTMLTextAreaElement | HTMLInputElement>) => {
        e.preventDefault();
        if (e.key === 'Enter' && (e.shiftKey || type !== 'textarea')) {
            let value = type === 'textarea' ? currentValue.substring(0, currentValue.length - 1) : currentValue;
            if (!currentValues.includes(value) && value !== '') {
                setCurrentValues([...currentValues, value]);
                setCurrentValue('');
            }
        }
    };

    const addValue = () => {
        let value = currentValue;
        if (!currentValues.includes(value) && value !== '') {
            setCurrentValues([...currentValues, value]);
            setCurrentValue('');
        }
    };

    const removeValue = (valueToRemove: string) => {
        setCurrentValues(currentValues.filter((v) => v !== valueToRemove));
    };

    return (
        <div className="flex flex-row gap-1 flex-wrap">
            <div className="flex flex-row items-center w-full">
                {type === 'textarea' && (
                    <Textarea
                        id={`value_${id}`}
                        placeholder={placeholder}
                        autoComplete="off"
                        className={cn('pr-10 min-h-0 h-9', className)}
                        onInput={handleInput}
                        value={currentValue}
                        onChange={handleChange}
                        onKeyUp={handleKeyUp}
                        disabled={disabled}
                        data-dusk="textarea-multi-input-value"
                    />
                )}
                {type !== 'textarea' && (
                    <Input
                        id={`value_${id}`}
                        type={type}
                        placeholder={placeholder}
                        className={cn('pr-10 min-h-0 h-9', className)}
                        onInput={handleInput}
                        value={currentValue}
                        onChange={handleChange}
                        onKeyUp={handleKeyUp}
                        disabled={disabled}
                        data-dusk="input-multi-input-value"
                    />
                )}
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <PlusCircle className="-ml-8" onClick={addValue} data-dusk="button-multi-input-add-value" />
                        </TooltipTrigger>
                        <TooltipContent>
                            <Info className="inline-block" /> Press <kbd>Shift</kbd> + <kbd>Enter</kbd> to add a new
                            value
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
            {currentValues.map((value) => (
                <div key={value}>
                    {value.length <= 25 && (
                        <Badge variant="default" className="cursor-pointer" onClick={() => removeValue(value)}>
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
                                        onClick={() => removeValue(value)}
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
