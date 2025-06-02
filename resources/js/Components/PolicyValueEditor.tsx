import MultiValueInput from '@/Components/MultiValueInput';
import DateRangeInput from '@/Components/DateRangeInput';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import React, { useState } from 'react';

export const PolicyValueEditor = function ({
    id,
    value,
    setValue,
    allowMultiple = false,
    disabled = false,
    isDateRange = false,
}: {
    id: string | number;
    value: string | string[] | null;
    setValue: (value: string | string[]) => void;
    allowMultiple: boolean;
    disabled: boolean;
    isDateRange?: boolean;
}) {
    const [values, setValues] = useState<string[]>(typeof value === 'string' ? [value] : (value ?? []));
    const [currentValue, setCurrentValue] = useState<string>(() => {
        if (typeof value === 'string') {
            return value;
        }

        if (value?.[0]) {
            return value[0];
        }

        return '';
    });

    if (isDateRange) {
        return (
            <>
                <Label htmlFor={`value_${id}`} aria-required hidden>
                    Date Range
                </Label>
                <DateRangeInput id={`value_${id}`} values={values} setValues={setValue as any} disabled={disabled} />
            </>
        );
    }

    if (allowMultiple) {
        return (
            <>
                <Label htmlFor={`value_${id}`} aria-required hidden>
                    Values
                </Label>
                <MultiValueInput id={`value_${id}`} values={values} setValues={setValue as any} disabled={disabled} />
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
