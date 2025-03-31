import { Team } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import IconPicker from '@/Components/IconPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { FormErrors } from '@/types/global';
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
    data: any;
    setData: (key: keyof Team, value: any) => void;
    errors: FormErrors;
    processing: any;
    onCancel: any;
}) {
    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name" aria-required>
                    Team Name
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
                <InputError message={errors?.name} />
            </div>
            <div>
                <Label>Color</Label>
                <ColorPicker onColorChange={(color) => setData('color', color)} color={data.color} />
                <InputError message={errors?.color} />
            </div>
            <div>
                <IconPicker onIconSelect={(icon) => setData('icon', icon)} icon={data.icon} errors={errors} />
                <InputError message={errors?.icon} />
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
