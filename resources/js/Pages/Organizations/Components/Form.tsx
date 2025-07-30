import { Organization } from '@/Application';
import { ColorPicker } from '@/Components/ColorPicker';
import IconPicker from '@/Components/IconPicker';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useForm } from '@inertiajs/react';
import React from 'react';

const Form = ({ organization, onCancel }: { organization?: Organization; onCancel?: () => void }) => {
    const { data, setData, patch, post, errors, reset, processing } = useForm({
        id: organization?.id ?? '',
        owner: organization?.owner ?? null,
        name: organization?.name ?? '',
        team: {
            name: '',
            organization: '',
            members: [],
            color: '',
            icon: '',
            id: '',
        },
    });

    const handleSubmit = (e: any) => {
        e.preventDefault();

        if (organization?.id) {
            patch(route('organizations.update', { organization: organization.id as string }));
            return;
        }

        post(route('organizations.store'));
    };

    const handleCancel = () => {
        reset();
        if (onCancel) {
            onCancel();
        }
    };

    // @ts-ignore
    // @ts-ignore
    // @ts-ignore
    return (
        <form onSubmit={handleSubmit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    type="text"
                    value={data.name as string}
                    onChange={(e) => setData('name', e.target.value)}
                    data-dusk="input-organization-name"
                />
                <InputError message={errors?.name} />
            </div>
            {(organization?.id === null || organization?.id === undefined) && (
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle>Default Team</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <div>
                            <Label htmlFor="team_name">Team Name</Label>
                            <Input
                                id="team_name"
                                name="team_name"
                                type="text"
                                value={data.team.name ?? ''}
                                onChange={(e) => setData('team', { ...data.team, name: e.target.value })}
                                data-dusk="input-team-name"
                            />
                            {/*
                            // @ts-ignore */}
                            <InputError message={errors?.['team.name'] as string} />
                        </div>
                        <div>
                            <Label>Color</Label>
                            <ColorPicker
                                onColorChange={(color) => setData('team', { ...data.team, color: color as string })}
                                color={data.team.color}
                            />
                            {/*
                            // @ts-ignore */}
                            <InputError message={errors?.['team.color'] as string} />
                        </div>
                        <div>
                            <IconPicker
                                icon={data.team?.icon ?? ''}
                                onIconSelect={(icon) => setData('team', { ...data.team, icon })}
                                errors={errors}
                            />
                            {/*
                            // @ts-ignore */}
                            <InputError message={errors?.['team.icon'] as string} />
                        </div>
                    </CardContent>
                </Card>
            )}
            <div className="flex justify-end">
                {onCancel && (
                    <Button variant="link" className="mr-2" type="button" onClick={handleCancel}>
                        Cancel
                    </Button>
                )}
                <Button type="submit" className="w-24" disabled={processing} data-dusk="button-organization-submit">
                    {organization?.id ? 'Update' : 'Create'}
                </Button>
            </div>
        </form>
    );
};

export default Form;
