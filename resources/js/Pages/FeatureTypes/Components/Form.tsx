import { FeatureType } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import IconPicker from '@/Components/IconPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Switch } from '@/Components/ui/switch';
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
    data: FeatureType;
    setData: (key: keyof FeatureType, value: any) => void;
    errors: FormErrors;
    processing: any;
    onCancel: any;
}) {
    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name" aria-required>
                    Feature Type Name
                </Label>
                <div className="flex">
                    <Input
                        id="name"
                        type="text"
                        value={data.name as string}
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
                <IconPicker icon={data.icon ?? ''} onIconSelect={(icon) => setData('icon', icon)} errors={errors} />
            </div>
            <div>
                <Label htmlFor="description" aria-required>
                    Description
                </Label>
                <Textarea
                    id="description"
                    value={data.description ?? ''}
                    rows={8}
                    onChange={(e) => setData('description', e.target.value)}
                />
            </div>
            <div className="flex items-center">
                <Switch
                    id="temporary"
                    checked={data.temporary ?? false}
                    onCheckedChange={(checked) => setData('temporary', checked)}
                />
                <Label htmlFor="temporary" className="ml-4 flex items-center">
                    Temporary?
                    <TooltipProvider delayDuration={0}>
                        <Tooltip>
                            <TooltipTrigger>
                                <InfoCircledIcon className="ml-2 h-4 w-4" />
                            </TooltipTrigger>
                            <TooltipContent>
                                Temporary flags are used for short-lived features or experiments. They are automatically
                                marked as stale over time.
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </Label>
            </div>
            <div></div>
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
