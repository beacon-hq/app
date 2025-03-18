import MultiValueInput from '@/Components/MultiValueInput';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import React, { useState } from 'react';

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
    const [currentValue, setCurrentValue] = useState<string>('');

    if (allowMultiple) {
        return (
            <>
                <Label htmlFor={`value_${id}`} aria-required hidden>
                    Values
                </Label>
                <MultiValueInput
                    id={`value_${id}`}
                    values={(value ?? []) as string[]}
                    setValues={setValue as any}
                    disabled={disabled}
                />
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
