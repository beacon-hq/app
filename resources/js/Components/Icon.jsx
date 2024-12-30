import { icons } from 'lucide-react';

const Icon = ({ name, className }) => {
    const LucideIcon = icons[name];

    return <LucideIcon className={className} />;
};

export default Icon;
