import { Tag } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
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
    setData: (key: keyof Tag, value: any) => void;
    errors: FormErrors;
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
                        data-dusk="input-tags-name"
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
                    data-dusk="input-tags-description"
                />
            </div>
            <div className="flex justify-end">
                <Button variant="link" className="mr-2" type="button" onClick={onCancel}>
                    Cancel
                </Button>
                <Button type="submit" className="w-24" disabled={processing} data-dusk="button-tag-submit">
                    {data.id ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
}
