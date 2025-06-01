import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { format } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-react';
import * as React from 'react';
import { DateRange } from 'react-day-picker';

interface DateRangePickerProps {
    value: DateRange | undefined;
    onChange: (value: DateRange | undefined) => void;
}

export function DateRangePicker({ value, onChange }: DateRangePickerProps) {
    const [open, setOpen] = React.useState(false);
    const [dates, setDates] = React.useState<DateRange | undefined>(value);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button variant="outline" className="w-[280px] justify-start text-left font-normal">
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {value?.from && value?.to
                        ? `${format(value.from, 'PPP')} - ${format(value.to, 'PPP')}`
                        : 'Pick a date range'}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0 flex flex-col items-end">
                <Calendar
                    mode="range"
                    defaultMonth={value?.from}
                    numberOfMonths={2}
                    selected={dates}
                    onSelect={setDates}
                    initialFocus
                />
                <Button
                    type="button"
                    className="mb-2 mr-3"
                    onClick={function () {
                        onChange(dates);
                        setOpen(false);
                    }}
                >
                    Apply
                </Button>
            </PopoverContent>
        </Popover>
    );
}
