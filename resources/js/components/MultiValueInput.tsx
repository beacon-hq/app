import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Kbd, KbdGroup } from '@/components/ui/kbd';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Info, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

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

    const addValueFromInput = () => {
        const value = type === 'textarea' ? currentValue : currentValue;
        if (!values.includes(value) && value !== '') {
            setValues([...values, value]);
            setCurrentValue('');
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement | HTMLInputElement>) => {
        if (e.key === 'Enter' && (e.shiftKey || type !== 'textarea')) {
            e.preventDefault();
            addValueFromInput();
        }
    };

    const addValue = () => {
        const value = currentValue;
        if (!values.includes(value) && value !== '') {
            setValues([...values, value]);
            setCurrentValue('');
        }
    };

    const removeValue = (valueToRemove: string) => {
        setValues(values.filter((v) => v !== valueToRemove));
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
                        onKeyDown={handleKeyDown}
                        disabled={disabled}
                        data-test="textarea-multi-input-value"
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
                        onKeyDown={handleKeyDown}
                        disabled={disabled}
                        data-test="input-multi-input-value"
                    />
                )}
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <PlusCircle className="-ml-8" onClick={addValue} data-test="button-multi-input-add-value" />
                        </TooltipTrigger>
                        <TooltipContent>
                            <Info className="inline-block" /> Press{' '}
                            <KbdGroup>
                                <Kbd>Shift</Kbd> + <Kbd>Enter</Kbd>
                            </KbdGroup>{' '}
                            to add a new value
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
            {values.map((value) => (
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
