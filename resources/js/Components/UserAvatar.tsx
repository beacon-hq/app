import { User } from '@/Application';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { cn } from '@/lib/utils';
import React from 'react';

export default function UserAvatar({ user, className }: { user: User; className?: string }) {
    return (
        <Avatar className={cn('h-10 w-10 rounded-full shadow-md', className)}>
            <AvatarImage src={user.avatar ?? user.gravatar ?? ''} alt={`${user.first_name} ${user.last_name}`} />
            <AvatarFallback>{`${user.first_name?.charAt(1)} ${user.last_name?.charAt(1)}`}</AvatarFallback>
        </Avatar>
    );
}
