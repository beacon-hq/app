import Icon from '@/Components/Icon';
import { IconColor } from '@/Components/IconColor';
import InputError from '@/Components/InputError';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { cn } from '@/lib/utils';
import { ChevronsUpDown, X } from 'lucide-react';
import React from 'react';

export function Form({
    submit,
    data,
    tags,
    setData,
    errors,
    processing,
    onCancel,
    featureTypes,
}: {
    submit: (e: React.FormEvent) => void;
    data: any;
    tags: any[];
    setData: (key: string, value: any) => void;
    errors: any;
    processing: any;
    onCancel: any;
    featureTypes: { id: string; name: string; icon: string | null; color: string }[];
}) {
    const [tagsOpen, setTagsOpen] = React.useState(false);
    const [tagFilter, setTagFilter] = React.useState('');

    return (
        <form onSubmit={submit} className="flex flex-col space-y-4">
            <div>
                <Label htmlFor="name">Feature Name</Label>
                <Input
                    id="name"
                    type="text"
                    value={data.name}
                    autoComplete="off"
                    disabled={data.slug !== undefined}
                    onChange={(e) => setData('name', e.target.value)}
                />
                {errors.name && <InputError message={errors.name} />}
            </div>
            <div>
                <Select value={data.feature_type} onValueChange={(value) => setData('feature_type', value)}>
                    <SelectTrigger className="">
                        <SelectValue placeholder="Select a flag type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            {featureTypes.map(function (type) {
                                return (
                                    <SelectItem key={type.id} value={type.id}>
                                        <div className="flex items-center">
                                            {type.icon != null && (
                                                <Icon
                                                    name={type.icon}
                                                    className={`h-5 w-5 stroke-${type.color}-400 mr-2 inline-block`}
                                                />
                                            )}
                                            {type.icon == null && (
                                                <div
                                                    className={cn(
                                                        'h-5 w-5 rounded-full inline-block mr-2',
                                                        type.color.charAt(0) !== '#' ? `bg-${type.color}-400` : '',
                                                    )}
                                                    style={
                                                        type.color.charAt(0) === '#'
                                                            ? { backgroundColor: type.color }
                                                            : {}
                                                    }
                                                ></div>
                                            )}
                                            {type.name}
                                        </div>
                                    </SelectItem>
                                );
                            })}
                        </SelectGroup>
                    </SelectContent>
                </Select>
                {errors.feature_type && <InputError message={errors.feature_type} />}
            </div>
            <div>
                <Popover open={tagsOpen} onOpenChange={setTagsOpen}>
                    <PopoverTrigger asChild>
                        <Button
                            variant="outline"
                            role="combobox"
                            aria-expanded={tagsOpen}
                            className="w-full justify-between h-fit"
                        >
                            {data.tags.length === 0 && <span>Select tags…</span>}
                            {data.tags.length > 0 && (
                                <span className="flex flex-wrap gap-1">
                                    {data.tags.map((tag: any) => (
                                        <Badge
                                            key={tag.id}
                                            className={cn(
                                                'bg-background border-2 hover:bg-background flex items-center',
                                                tag.color?.charAt(0) != '#' ? `border-${tag.color}-400` : '',
                                            )}
                                            onClick={(e) => {
                                                e.preventDefault();
                                                setData(
                                                    'tags',
                                                    data.tags.filter((existingTag: any) => existingTag.id !== tag.id),
                                                );
                                                const oldFilter = tagFilter;
                                                setTagFilter('');
                                                setTagFilter(oldFilter);
                                            }}
                                            style={tag.color?.charAt(0) === '#' ? { borderColor: tag.color } : {}}
                                        >
                                            <span className="text-primary">{tag.name}</span>
                                            <X className="h-4 w-4 ml-1 text-primary/40 relative -right-2" />
                                        </Badge>
                                    ))}
                                </span>
                            )}
                            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-full p-0" side="bottom">
                        <Command>
                            <CommandInput
                                value={tagFilter}
                                onValueChange={setTagFilter}
                                placeholder="Search tags..."
                                className="w-64"
                            />
                            <CommandList>
                                <CommandEmpty>No tags found.</CommandEmpty>
                                <CommandGroup>
                                    {tags.map(function (tag) {
                                        if (
                                            tagFilter == '' &&
                                            data.tags.filter((existingTag: any) => existingTag.name === tag.name)
                                                .length > 0
                                        ) {
                                            return;
                                        }
                                        return (
                                            <CommandItem
                                                key={tag.id}
                                                value={tag}
                                                onSelect={() => {
                                                    if (
                                                        data.tags.filter(
                                                            (existingTag: any) => existingTag.name === tag.name,
                                                        ).length > 0
                                                    ) {
                                                        setData(
                                                            'tags',
                                                            data.tags.filter(
                                                                (existingTag: any) => existingTag.name !== tag.name,
                                                            ),
                                                        );
                                                        return;
                                                    } else {
                                                        setData('tags', [...data.tags, tag]);
                                                    }
                                                }}
                                            >
                                                <IconColor color={tag.color}></IconColor>
                                                {tag.name}
                                            </CommandItem>
                                        );
                                    })}
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
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
