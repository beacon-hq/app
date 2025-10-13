import MultiValueInput from '@/components/MultiValueInput';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    const [currentValue, setCurrentValue] = useState<string>(() => {
        if (typeof value === 'string') {
            return value;
        }

        if (value?.[0]) {
            return value[0];
        }

        return '';
    });

    if (allowMultiple) {
        return (
            <>
                <Label htmlFor={`value_${id}`} aria-required hidden>
                    Values
                </Label>
                <MultiValueInput
                    id={`value_${id}`}
                    values={typeof value === 'string' ? [value] : (value ?? [])}
                    setValues={(vals) => {
                        if (typeof vals === 'function') {
                            const current = typeof value === 'string' ? [value] : (value ?? []);
                            setValue(vals(current));
                        } else {
                            setValue(vals);
                        }
                    }}
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
                    setValue([e.target.value]);
                }}
                onBlur={(e) => {
                    setCurrentValue(e.target.value);
                    setValues([e.target.value]);
                    setValue([e.target.value]);
                }}
                disabled={disabled}
                data-test={`input-policy-value-${id}`}
            />
        </>
    );
};
