import { icons } from 'lucide-react';

interface IconProps {
    name: string;
    className?: string;
}

const Icon = ({ name, className }: IconProps): any => {
    // @ts-ignore
    const LucideIcon = icons[name];

    if (LucideIcon === undefined) {
        return null;
    }

    return <LucideIcon className={className} />;
};

export default Icon;
