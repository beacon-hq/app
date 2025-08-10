import { Role, Team, User, UserStatus } from '@/Application';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from '@/Components/ui/command';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Switch } from '@/Components/ui/switch';
import { cn } from '@/lib/utils';
import { Auth } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, X } from 'lucide-react';
import React, { useEffect, useState } from 'react';

const ManageUser = ({
    user,
    teams,
    open,
    onOpenChange,
}: {
    user: User;
    teams: Team[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) => {
    const currentUser = (usePage().props.auth as Auth).user;

    const { data, setData, patch, post, processing, errors, reset } = useForm({
        role: user.roles?.[0] ?? Role.DEVELOPER,
        status: user.status ?? UserStatus.ACTIVE,
        teams: user.teams ?? [],
    });

    const [teamsOpen, setTeamsOpen] = useState(false);

    useEffect(() => {
        if (open) {
            setData({
                role: user.roles?.[0] ?? Role.DEVELOPER,
                status: user.status ?? UserStatus.ACTIVE,
                teams: user.teams ?? [],
            });
        }
    }, [user, open]);

    const handleSubmit = () => {
        patch(route('users.update', { user: user.id as number }), {
            onSuccess: () => {
                onOpenChange(false);
                reset();
            },
        });
    };

    const handleResetPassword = () => {
        post(route('password.email', { email: user.email }), {
            onSuccess: () => {
                onOpenChange(false);
                reset();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent data-dusk="dialog-user-manage">
                <DialogHeader>
                    <DialogTitle>
                        Manage User: {user.first_name} {user.last_name}
                    </DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <div className="text-sm text-muted-foreground">{user.email}</div>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="role">Role</Label>
                        <Select
                            value={data.role}
                            onValueChange={(value: Role) => setData('role', value)}
                            disabled={user.id === currentUser.id}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={Role.DEVELOPER}>Developer</SelectItem>
                                <SelectItem value={Role.ADMIN}>Admin</SelectItem>
                                <SelectItem value={Role.BILLER}>Biller</SelectItem>
                                <SelectItem value={Role.OWNER}>Owner</SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.role && <span className="text-sm text-destructive">{errors.role}</span>}
                    </div>
                    <div className="grid gap-2">
                        <div className="flex items-center justify-between">
                            <Label htmlFor="status">Active Status</Label>
                            <Switch
                                id="status"
                                checked={data.status === UserStatus.ACTIVE}
                                onCheckedChange={(checked) =>
                                    setData('status', checked ? UserStatus.ACTIVE : UserStatus.INACTIVE)
                                }
                                disabled={user.id === currentUser.id}
                            />
                        </div>
                        {errors.status && <span className="text-sm text-destructive">{errors.status}</span>}
                    </div>
                    <div className="grid gap-2">
                        <Label>Teams</Label>
                        <Popover open={teamsOpen} onOpenChange={setTeamsOpen}>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    aria-expanded={teamsOpen}
                                    className="w-full justify-between"
                                >
                                    {data.teams.length > 0 ? `${data.teams.length} teams selected` : 'Select teams...'}
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-full p-0">
                                <Command>
                                    <CommandInput placeholder="Search teams..." />
                                    <CommandEmpty>No teams found.</CommandEmpty>
                                    <CommandGroup>
                                        {teams.map((team) => {
                                            const isSelected = data.teams.filter((t) => t.id === team.id).length > 0;
                                            return (
                                                <CommandItem
                                                    key={team.id}
                                                    onSelect={() => {
                                                        const newTeams = isSelected
                                                            ? data.teams.filter((t) => t.id !== team.id)
                                                            : [...data.teams, team];
                                                        setData('teams', newTeams);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn(
                                                            'mr-2 h-4 w-4',
                                                            isSelected ? 'opacity-100' : 'opacity-0',
                                                        )}
                                                    />
                                                    {team.name}
                                                </CommandItem>
                                            );
                                        })}
                                    </CommandGroup>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <div className="flex flex-wrap gap-1 mt-1">
                            {data.teams.map((team) => {
                                if (!team) return null;
                                return (
                                    <div
                                        key={team.id}
                                        className="flex items-center gap-1 rounded-md bg-secondary px-2 py-1 text-sm"
                                    >
                                        {team.name}
                                        <X
                                            className="h-4 w-4 cursor-pointer"
                                            onClick={() => {
                                                setData(
                                                    'teams',
                                                    data.teams.filter((t) => t.id !== team.id),
                                                );
                                            }}
                                        />
                                    </div>
                                );
                            })}
                        </div>
                        <InputError message={errors?.teams} />
                    </div>
                </div>
                <DialogFooter className="flex-col gap-2 sm:flex-row sm:justify-between sm:gap-0">
                    <Button variant="destructive" onClick={handleResetPassword} disabled={processing}>
                        Reset Password & 2FA
                    </Button>
                    <div className="flex gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>
                        <Button onClick={handleSubmit} disabled={processing}>
                            Save Changes
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default ManageUser;
