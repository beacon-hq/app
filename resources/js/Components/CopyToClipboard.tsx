import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
import { ClipboardCopy } from 'lucide-react';
import { ButtonHTMLAttributes } from 'react';

export default function CopyToClipboard({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & {
    text: string;
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center p-1 text-xs font-semibold text-gray-400 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-300 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
            onClick={() => {
                navigator.clipboard.writeText(props.text);
            }}
        >
            <Tooltip>
                <TooltipTrigger>
                    <ClipboardCopy className="h-4 w-4" />
                </TooltipTrigger>
                <TooltipContent>Copy to clipboard</TooltipContent>
            </Tooltip>
            {children}
        </button>
    );
}
