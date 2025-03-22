import { Application } from '@/Application';
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
    transform,
    onCancel,
}: {
    submit: (e: FormEvent) => void;
    data: Application;
    setData: (key: keyof Application, value: any) => void;
    errors: FormErrors;
    processing: any;
    transform: any;
    onCancel: any;
}) {
    const onSubmit = (e: FormEvent) => {
        e.preventDefault();

        transform(function (data: Application) {
            return {
                ...data,
                display_name: data.display_name != '' ? (data.display_name ?? data.name) : data.name,
            };
        });

        submit(e);
    };

    return (
        <form onSubmit={onSubmit} className="flex flex-col space-y-4">
            <div>
                <div>
                    <Label htmlFor="name" aria-required>
                        Application Name
                    </Label>
                    <TooltipProvider delayDuration={0}>
                        <Tooltip>
                            <TooltipTrigger>
                                <InfoCircledIcon className="ml-2 h-4 w-4" />
                            </TooltipTrigger>
                            <TooltipContent>
                                The name of the application. This name maps to your application{' '}
                                <code className="font-mono font-bold">APP_NAME</code> and <em>cannot be changed</em>.
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
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
                <Label htmlFor="display_name">Display Name</Label>
                <Input
                    id="display_name"
                    autoComplete="off"
                    type="text"
                    value={data.display_name ?? ''}
                    onChange={(e) => setData('display_name', e.target.value)}
                />
                <InputError message={errors?.display_name} />
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
                    value={data.description ?? ''}
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
