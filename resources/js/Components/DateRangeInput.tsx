import React, { useEffect, useState } from 'react';
import { Calendar } from '@/Components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import { format, isAfter, isSameDay, setHours, setMinutes } from 'date-fns';
import { AlertCircle, CalendarIcon, Clock } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

interface DateRangeInputProps {
    id: string;
    values: string[];
    setValues: (value: string[]) => void;
    disabled?: boolean;
}

const DateRangeInput: React.FC<DateRangeInputProps> = ({
    id,
    values,
    setValues,
    disabled = false,
}) => {
    const [startDate, setStartDate] = useState<Date | undefined>(
        values[0] ? new Date(values[0]) : undefined
    );
    const [endDate, setEndDate] = useState<Date | undefined>(
        values[1] ? new Date(values[1]) : undefined
    );
    const [startHours, setStartHours] = useState<number>(startDate ? startDate.getHours() : 0);
    const [startMinutes, setStartMinutes] = useState<number>(startDate ? startDate.getMinutes() : 0);
    const [endHours, setEndHours] = useState<number>(endDate ? endDate.getHours() : 23);
    const [endMinutes, setEndMinutes] = useState<number>(endDate ? endDate.getMinutes() : 59);
    const [isOpen, setIsOpen] = useState(false);
    const [validationError, setValidationError] = useState<string | null>(null);

    // Validate that end date is after start date
    const validateDateRange = () => {
        if (startDate && endDate) {
            // Create dates with time for comparison
            const startWithTime = startDate ? new Date(startDate) : new Date();
            if (startWithTime && startDate) {
                startWithTime.setHours(startHours);
                startWithTime.setMinutes(startMinutes);
            }

            const endWithTime = endDate ? new Date(endDate) : new Date();
            if (endWithTime && endDate) {
                endWithTime.setHours(endHours);
                endWithTime.setMinutes(endMinutes);
            }

            if (!isAfter(endWithTime, startWithTime)) {
                setValidationError('End date/time must be after start date/time');
                return false;
            }
        }
        setValidationError(null);
        return true;
    };

    useEffect(() => {
        const newValues: string[] = [];
        if (startDate) {
            // Create a new date with time
            const startWithTime = new Date(startDate);
            startWithTime.setHours(startHours);
            startWithTime.setMinutes(startMinutes);
            newValues.push(format(startWithTime, 'yyyy-MM-dd HH:mm'));
        }
        if (endDate) {
            // Create a new date with time
            const endWithTime = new Date(endDate);
            endWithTime.setHours(endHours);
            endWithTime.setMinutes(endMinutes);
            newValues.push(format(endWithTime, 'yyyy-MM-dd HH:mm'));
        }
        if (newValues.length > 0 && validateDateRange()) {
            setValues(newValues);
        }
    }, [startDate, endDate, startHours, startMinutes, endHours, endMinutes, setValues]);

    const handleRemoveRange = () => {
        setStartDate(undefined);
        setEndDate(undefined);
        setValues([]);
    };

    return (
        <div className="flex flex-col gap-2 w-full">
            <div className="flex flex-row items-center gap-2">
                <Popover open={isOpen && !disabled} onOpenChange={setIsOpen}>
                    <PopoverTrigger asChild>
                        <Button
                            variant="outline"
                            className={cn(
                                "w-full justify-start text-left font-normal",
                                !startDate && !endDate && "text-muted-foreground"
                            )}
                            disabled={disabled}
                        >
                            <CalendarIcon className="mr-2 h-4 w-4" />
                            {startDate && endDate ? (
                                `${format(startDate, 'MMM d, yyyy')} ${startHours}:${startMinutes.toString().padStart(2, '0')} - ${format(endDate, 'MMM d, yyyy')} ${endHours}:${endMinutes.toString().padStart(2, '0')}`
                            ) : (
                                <span>Select date range</span>
                            )}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0" align="start">
                        <div className="flex flex-col gap-2 p-2">
                            <div className="flex flex-row gap-2">
                                <div className="flex flex-col">
                                    <span className="text-sm font-medium">Start Date</span>
                                    <Calendar
                                        mode="single"
                                        selected={startDate}
                                        onSelect={setStartDate}
                                        disabled={disabled}
                                        initialFocus
                                        disabledDays={{ before: new Date() }}
                                    />
                                    <div className="flex flex-row items-center gap-2 mt-2">
                                        <Clock className="h-4 w-4" />
                                        <div className="flex flex-row items-center gap-1">
                                            <Input
                                                type="number"
                                                min={0}
                                                max={23}
                                                value={startHours}
                                                onChange={(e) => setStartHours(parseInt(e.target.value) || 0)}
                                                className="w-16"
                                                disabled={disabled || !startDate}
                                            />
                                            <span>:</span>
                                            <Input
                                                type="number"
                                                min={0}
                                                max={59}
                                                value={startMinutes}
                                                onChange={(e) => setStartMinutes(parseInt(e.target.value) || 0)}
                                                className="w-16"
                                                disabled={disabled || !startDate}
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-sm font-medium">End Date</span>
                                    <Calendar
                                        mode="single"
                                        selected={endDate}
                                        onSelect={(date) => {
                                            setEndDate(date);
                                            if (date && startDate) {
                                                validateDateRange();
                                            }
                                        }}
                                        disabled={disabled || !startDate}
                                        initialFocus
                                        disabledDays={{ before: startDate || new Date() }}
                                    />
                                    <div className="flex flex-row items-center gap-2 mt-2">
                                        <Clock className="h-4 w-4" />
                                        <div className="flex flex-row items-center gap-1">
                                            <Input
                                                type="number"
                                                min={0}
                                                max={23}
                                                value={endHours}
                                                onChange={(e) => setEndHours(parseInt(e.target.value) || 0)}
                                                className="w-16"
                                                disabled={disabled || !endDate}
                                            />
                                            <span>:</span>
                                            <Input
                                                type="number"
                                                min={0}
                                                max={59}
                                                value={endMinutes}
                                                onChange={(e) => setEndMinutes(parseInt(e.target.value) || 0)}
                                                className="w-16"
                                                disabled={disabled || !endDate}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {validationError && (
                                <div className="flex items-center text-red-500 text-sm mt-1 mb-1">
                                    <AlertCircle className="h-4 w-4 mr-1" />
                                    {validationError}
                                </div>
                            )}
                            <Button
                                onClick={() => {
                                    if (validateDateRange()) {
                                        setIsOpen(false);
                                    }
                                }}
                                className="w-full"
                                disabled={!!validationError}
                            >
                                Apply
                            </Button>
                        </div>
                    </PopoverContent>
                </Popover>
            </div>
        </div>
    );
};

export default DateRangeInput;
