import { icons } from 'lucide-react';

interface IconProps {
    name: string;
    className?: string;
}

export default function Icon({ name, className }: IconProps): any {
    // @ts-ignore
    const LucideIcon = icons[name];

    return <LucideIcon className={className} />;
}
