import { ColorPicker } from '@/Components/ColorPicker';
import Icon from '@/Components/Icon';
import InputError from '@/Components/InputError';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Switch } from '@/Components/ui/switch';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { InfoCircledIcon } from '@radix-ui/react-icons';
import { PopoverClose } from '@radix-ui/react-popover';
import { ChevronLeft, ChevronRight, icons, Info } from 'lucide-react';
import React, { useMemo, useState } from 'react';

export function Form({
    submit,
    data,
    setData,
    errors,
    processing,
    onCancel,
}: {
    submit: (e: React.FormEvent) => void;
    data: any;
    setData: (key: string, value: any) => void;
    errors: any;
    processing: any;
    onCancel: any;
}) {
    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name" aria-required>
                    Tag Name
                </Label>
                <div className="flex">
                    <Input
                        id="name"
                        type="text"
                        value={data.name}
                        autoComplete="off"
                        onChange={(e) => setData('name', e.target.value)}
                    />
                </div>
                {errors.name && <InputError message={errors.name} />}
            </div>
            <div>
                <Label>Color</Label>
                <ColorPicker onColorChange={(color) => setData('color', color)} color={data.color} />
                {errors.color && <InputError message={errors.color} />}
            </div>
            <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={data.description}
                    rows={8}
                    onChange={(e) => setData('description', e.target.value)}
                />
            </div>
            <div className="flex justify-end">
                <Button variant="link" className="mr-2" type="button" onClick={onCancel}>
                    Cancel
                </Button>
                <Button type="submit" className="w-24" disabled={processing}>
                    {data.slug ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
}
