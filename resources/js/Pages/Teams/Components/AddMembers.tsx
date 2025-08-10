import { Team, User, UserCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown, MinusCircle, PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

const AddMembers = ({ team, users }: { team: Team; users: UserCollection }) => {
    const [open, setOpen] = useState(false);
    const { data, setData, post, transform } = useForm<{ users: { email: string }[] }>({ users: [] });
    const [userDropdownOpen, setUserDropdownOpen] = useState(false);
    const [currentUser, setCurrentUser] = useState<User | undefined>(undefined);

    transform((data) => {
        if (currentUser) {
            return { users: [...data.users, { email: currentUser.email }] };
        }

        return data;
    });

    const handleSubmit = () => {
        post(route('team-members.invite', { team: team.id as string }), {
            onSuccess: () => {
                setOpen(false);
                setData('users', []);
            },
        });

        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button type="submit" data-dusk="button-add-members">
                    <PlusCircle /> Add Members
                </Button>
            </DialogTrigger>
            <DialogContent
                className="max-w-fit"
                aria-description="Add members to the team"
                data-dusk="dialog-add-members"
            >
                <DialogHeader>
                    <DialogTitle>Add Members to {team.name}</DialogTitle>
                </DialogHeader>
                {data.users.map((user, index) => (
                    <div key={user.email} className="flex flex-row gap-2 items-center">
                        <Label htmlFor={`add_member_${user.email}`} hidden>
                            Team Member
                        </Label>
                        <Button
                            variant="outline"
                            role="combobox"
                            disabled
                            aria-expanded={userDropdownOpen}
                            className="w-[300px] justify-between truncate"
                        >
                            {users.find((u) => u.email === user.email)?.name} ({user.email})
                        </Button>
                        <MinusCircle
                            size="40"
                            onClick={() =>
                                setData(
                                    'users',
                                    data.users.filter((currentUser) => user.email !== currentUser.email),
                                )
                            }
                        />
                    </div>
                ))}
                <div className="flex flew-row gap-2 items-center">
                    <Popover open={userDropdownOpen} onOpenChange={setUserDropdownOpen}>
                        <PopoverTrigger asChild>
                            <Button
                                variant="outline"
                                role="combobox"
                                aria-expanded={userDropdownOpen}
                                className="w-[300px] justify-between truncate"
                                data-dusk="select-teams-team-member"
                            >
                                {currentUser
                                    ? `${users.find((user) => user.email === currentUser.email)?.name} (${currentUser.email})`
                                    : 'Select a team member...'}
                                <ChevronsUpDown className="opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className="w-[300px] p-0">
                            <Command>
                                <CommandInput placeholder="Search team members..." className="h-9" />
                                <CommandList>
                                    <CommandEmpty>No team members found.</CommandEmpty>
                                    <CommandGroup>
                                        {users &&
                                            users
                                                .filter(function (user) {
                                                    return data.users.find((u) => u.email === user.email) === undefined;
                                                })
                                                .map((user, idx) => (
                                                    <CommandItem
                                                        key={user.id}
                                                        value={user.email as string}
                                                        onSelect={(currentValue) => {
                                                            setCurrentUser(users.find((u) => u.email === currentValue));
                                                            setUserDropdownOpen(false);
                                                        }}
                                                        data-dusk={`select-option-teams-team-member-${idx}`}
                                                    >
                                                        {user.name} ({user.email})
                                                        {currentUser?.email === user.email && (
                                                            <Check className={cn('ml-auto')} />
                                                        )}
                                                    </CommandItem>
                                                ))}
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <PlusCircle
                        className={cn('cursor-pointer', {
                            'cursor-not-allowed text-primary/30': !currentUser,
                        })}
                        size="40"
                        onClick={function () {
                            if (!currentUser) {
                                return;
                            }

                            setData('users', [
                                ...data.users,
                                {
                                    email: users.find((user) => (user.email = currentUser.email))?.email as string,
                                },
                            ]);

                            setCurrentUser(undefined);
                        }}
                        data-dusk="button-teams-add-team-member"
                    />
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="button"
                        onClick={handleSubmit}
                        disabled={data.users.length === 0 && !currentUser}
                        data-dusk="button-teams-add-members-submit"
                    >
                        Add
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default AddMembers;
