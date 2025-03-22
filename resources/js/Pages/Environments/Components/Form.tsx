import { Environment } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { FormErrors } from '@/types/global';
import { InfoCircledIcon } from '@radix-ui/react-icons';
import React, { FormEvent } from 'react';

export function Form({
    submit,
    data,
    setData,
    errors,
    processing,
    onCancel,
}: {
    submit: (e: FormEvent) => void;
    data: Environment;
    setData: (key: keyof Environment, value: any) => void;
    errors: FormErrors;
    processing: any;
    onCancel: any;
}) {
    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name">
                    Environment Name
                    <TooltipProvider delayDuration={0}>
                        <Tooltip>
                            <TooltipTrigger>
                                <InfoCircledIcon className="ml-2 h-4 w-4" />
                            </TooltipTrigger>
                            <TooltipContent>
                                The name of the environment. This name maps to your application{' '}
                                <code className="font-mono font-bold">APP_ENV</code> and <em>cannot be changed</em>.
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </Label>
                <Input
                    id="name"
                    type="text"
                    value={data.name ?? ''}
                    autoComplete="off"
                    disabled={data.slug !== null && data.slug !== ''}
                    onChange={(e) => setData('name', e.target.value)}
                />
                <InputError message={errors?.name} />
            </div>
            <div>
                <Label>Color</Label>
                <ColorPicker onColorChange={(color) => setData('color', color)} color={data.color} />
                <InputError message={errors?.color} />
            </div>
            <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={data.description ?? ''}
                    rows={8}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError message={errors?.description} />
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
