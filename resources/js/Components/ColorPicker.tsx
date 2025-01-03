import { Colors } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { cn } from '@/lib/utils';
import { PopoverClose } from '@radix-ui/react-popover';
import { Pipette, Slash } from 'lucide-react';
import React from 'react';
import { HexColorInput, HexColorPicker } from 'react-colorful';

export function ColorPicker(props: { onColorChange: (color: string | null) => void; color: string | null }) {
    const [selectedColor, setSelectedColor] = React.useState<string | null>(props.color);
    return (
        <div className="flex gap-1">
            <div
                className={cn(`w-6 h-6 bg-white block text-red-500 p-0 border-2 shrink`, {
                    'border-primary': selectedColor == '',
                })}
                onClick={() => {
                    setSelectedColor(null);
                    props.onColorChange(null);
                }}
            >
                <Slash className="h-6 w-6 -ml-1 relative -top-0.5 left-0.5" />
            </div>
            {Object.entries(Colors).map(([, color]) => {
                return (
                    <Tooltip key={color}>
                        <TooltipTrigger asChild>
                            <div
                                className={cn(`w-6 h-6 bg-${color}-400 block`, {
                                    'border-2 border-primary': selectedColor == color,
                                })}
                                onClick={(e) => {
                                    e.preventDefault();
                                    setSelectedColor(color);
                                    props.onColorChange(color);
                                }}
                            ></div>
                        </TooltipTrigger>
                        <TooltipContent>{color}</TooltipContent>
                    </Tooltip>
                );
            })}
            <div
                className={cn('w-6 h-6 block', {
                    'border-2 border-primary': selectedColor?.charAt(0) === '#',
                })}
                style={selectedColor?.charAt(0) === '#' ? { backgroundColor: selectedColor } : {}}
            >
                <Popover>
                    <PopoverTrigger>
                        <Pipette className="h-5 w-5" />
                    </PopoverTrigger>
                    <PopoverContent>
                        <HexColorPicker
                            color={selectedColor ?? undefined}
                            onChange={(color) => {
                                setSelectedColor(color);
                                props.onColorChange(color);
                            }}
                        />
                        <HexColorInput
                            className="bg-background text-primary w-full mt-2"
                            color={selectedColor ?? undefined}
                            onChange={(color) => {
                                setSelectedColor(color);
                                props.onColorChange(color);
                            }}
                        />
                        <PopoverClose asChild>
                            <Button type="button" className="w-full mt-2">
                                Apply
                            </Button>
                        </PopoverClose>
                    </PopoverContent>
                </Popover>
            </div>
        </div>
    );
}
