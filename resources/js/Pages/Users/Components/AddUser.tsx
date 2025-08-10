import { Role, Team } from '@/Application';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { useForm } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';
import React, { useState } from 'react';

type NewUserForm = {
    email: string;
    role: Role;
    team: string;
};

const AddUser = ({ teams }: { teams: Team[] }) => {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm<NewUserForm>({
        email: '',
        role: Role.DEVELOPER,
        team: '',
    });

    const handleSubmit = () => {
        post(route('users.store'), {
            onSuccess: () => {
                setOpen(false);
                reset();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button data-dusk="button-invite-user">
                    <PlusCircle className="mr-2 h-4 w-4" />
                    Invite User
                </Button>
            </DialogTrigger>
            <DialogContent data-dusk="dialog-invite-user">
                <DialogHeader>
                    <DialogTitle>Invite New User</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="user@example.com"
                            data-dusk="input-invite-email"
                        />
                        {errors.email && <span className="text-sm text-destructive">{errors.email}</span>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="role">Role</Label>
                        <Select value={data.role} onValueChange={(value: Role) => setData('role', value)}>
                            <SelectTrigger data-dusk="select-invite-role">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={Role.DEVELOPER} data-dusk="select-option-invite-role-developer">
                                    Developer
                                </SelectItem>
                                <SelectItem value={Role.ADMIN} data-dusk="select-option-invite-role-admin">
                                    Admin
                                </SelectItem>
                                <SelectItem value={Role.BILLER} data-dusk="select-option-invite-role-biller">
                                    Biller
                                </SelectItem>
                                <SelectItem value={Role.OWNER} data-dusk="select-option-invite-role-owner">
                                    Owner
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.role && <span className="text-sm text-destructive">{errors.role}</span>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="team">
                            Team <span className="text-destructive">*</span>
                        </Label>
                        <Select value={data.team} onValueChange={(value) => setData('team', value)}>
                            <SelectTrigger data-dusk="select-invite-team">
                                <SelectValue placeholder="Select a team" />
                            </SelectTrigger>
                            <SelectContent>
                                {teams.map((team, idx) => (
                                    <SelectItem
                                        key={team.id}
                                        value={team.id?.toString() ?? ''}
                                        data-dusk={`select-option-invite-team-${idx}`}
                                    >
                                        {team.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.team && <span className="text-sm text-destructive">{errors.team}</span>}
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        onClick={handleSubmit}
                        disabled={processing || !data.email || !data.team}
                        data-dusk="button-invite-submit"
                    >
                        Send Invite
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default AddUser;
