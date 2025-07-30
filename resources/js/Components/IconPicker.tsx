import Icon from '@/Components/Icon';
import InputError from '@/Components/InputError';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { PopoverClose } from '@radix-ui/react-popover';
import { ChevronLeft, ChevronRight, icons, Info } from 'lucide-react';
import React, { useMemo, useState } from 'react';

const IconPicker = ({
    icon,
    required = false,
    onIconSelect,
    errors,
}: {
    icon: string;
    required?: boolean;
    onIconSelect: (icon: string) => void;
    errors: any;
}) => {
    const [selectedIcon, setSelectedIcon] = React.useState<string | null>(icon);
    const [searchTerm, setSearchTerm] = useState('');
    const [page, setPage] = useState(0);
    const filteredIcons = useMemo(() => {
        if (searchTerm == '') {
            return [];
        }

        return Object.entries(icons).filter(([name]) => name.toLowerCase().includes(searchTerm.toLowerCase()));
    }, [searchTerm]);

    const handleSelectedIcon = (icon: string) => {
        setSelectedIcon(icon);
        onIconSelect(icon);
    };

    return (
        <Popover>
            <PopoverTrigger asChild>
                <div data-dusk="icon-picker">
                    <Label aria-required={required}>Icon</Label>
                    <div className="flex flex-row items-center gap-2 icon-picker">
                        <Icon
                            name={selectedIcon == '' ? 'Image' : (selectedIcon as string)}
                            className={cn('h-8 w-8', {
                                'text-neutral-400': selectedIcon == '',
                            })}
                        />
                        <span className="text-sm">Pick an icon</span>
                    </div>
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
                        data-dusk="input-icon-picker-search"
                    />
                </div>
                {filteredIcons.length == 0 && (
                    <p className="text-neutral-400 w-full text-center mt-4">Search for an icon…</p>
                )}
                {filteredIcons.length > 0 && (
                    <>
                        <div className="grid grid-cols-4 gap-2 mt-4 min-h-64 items-baseline">
                            {filteredIcons.slice(page * 24, (page + 1) * 24).map(function ([name, Icon], idx) {
                                return (
                                    <PopoverClose key={name}>
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Button
                                                    variant={name != selectedIcon ? 'outline' : 'default'}
                                                    type="button"
                                                    key={name}
                                                    className={cn({
                                                        'border-black': name == selectedIcon,
                                                    })}
                                                    onClick={() => handleSelectedIcon(name)}
                                                    data-dusk={`icon-picker-icon-${idx}`}
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
    );
};

export default IconPicker;
