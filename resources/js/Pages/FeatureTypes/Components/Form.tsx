import { FeatureType } from '@/Application';
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
import { FormErrors } from '@/types/global';
import { InfoCircledIcon } from '@radix-ui/react-icons';
import { PopoverClose } from '@radix-ui/react-popover';
import { ChevronLeft, ChevronRight, icons, Info } from 'lucide-react';
import React, { FormEvent, useMemo, useState } from 'react';

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
    const [searchTerm, setSearchTerm] = useState('');
    const [page, setPage] = useState(0);
    const filteredIcons = useMemo(() => {
        if (searchTerm == '') {
            return [];
        }

        return Object.entries(icons).filter(([name]) => name.toLowerCase().includes(searchTerm.toLowerCase()));
    }, [searchTerm]);

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
                <Popover>
                    <PopoverTrigger>
                        <div>
                            <Label>Icon</Label>
                            <Icon
                                name={data.icon == '' ? 'Image' : data.icon}
                                className={cn('h-8 w-8', {
                                    'text-neutral-400': data.icon == '',
                                })}
                            />
                            {errors.icon && <InputError message={errors.icon} />}
                        </div>
                    </PopoverTrigger>
                    <PopoverContent>
                        <div>
                            <Input
                                value={searchTerm}
                                onChange={function (e) {
                                    setPage(0);
                                    setSearchTerm(e.target.value);
                                }}
                                placeholder="Search icons"
                                className="mt-4"
                            />
                        </div>
                        {filteredIcons.length == 0 && (
                            <p className="text-neutral-400 w-full text-center mt-4">Search for an icon…</p>
                        )}
                        {filteredIcons.length > 0 && (
                            <>
                                <div className="grid grid-cols-4 gap-2 mt-4 min-h-64 items-baseline">
                                    {filteredIcons.slice(page * 24, (page + 1) * 24).map(function ([name, Icon]) {
                                        return (
                                            <PopoverClose>
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        <Button
                                                            variant={name != data.icon ? 'outline' : 'default'}
                                                            type="button"
                                                            key={name}
                                                            className={cn({
                                                                'border-black': name == data.icon,
                                                            })}
                                                            onClick={() => setData('icon', name)}
                                                        >
                                                            <Icon />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>{name}</TooltipContent>
                                                </Tooltip>
                                            </PopoverClose>
                                        );
                                    })}
                                </div>
                                {filteredIcons.length > 24 && (
                                    <div className="w-full flex justify-between mt-2">
                                        <p
                                            onClick={() => {
                                                if (page > 0) {
                                                    setPage(page - 1);
                                                }
                                            }}
                                            className={cn({
                                                'text-neutral-400': page == 0,
                                                'cursor-pointer': page > 0,
                                            })}
                                        >
                                            <ChevronLeft className="w-6 h-6 inline-block" /> Previous
                                        </p>
                                        <p
                                            onClick={() => {
                                                if (Math.floor(filteredIcons.length / 24) > page) {
                                                    setPage(page + 1);
                                                }
                                            }}
                                            className={cn({
                                                'text-neutral-400': Math.floor(filteredIcons.length / 24) == page,
                                                'cursor-pointer': Math.floor(filteredIcons.length / 24) != page,
                                            })}
                                        >
                                            Next <ChevronRight className="w-6 h-6 inline-block" />
                                        </p>
                                    </div>
                                )}
                            </>
                        )}
                        <Alert className="mt-2">
                            <Info className="h-4 w-4 mr-2" />
                            <AlertTitle>Note</AlertTitle>
                            <AlertDescription>
                                Beacon uses the{' '}
                                <a className="underline" href="https://lucide.dev/icons/" target="_blank">
                                    Lucide
                                </a>{' '}
                                icon set
                            </AlertDescription>
                        </Alert>
                    </PopoverContent>
                </Popover>
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
