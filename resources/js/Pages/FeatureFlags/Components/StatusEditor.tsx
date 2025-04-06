import {
    Application,
    ApplicationCollection,
    Environment,
    EnvironmentCollection,
    FeatureFlagStatus,
    PolicyCollection,
} from '@/Application';
import { IconColor } from '@/Components/IconColor';
import { PolicyDefinitionForm } from '@/Components/PolicyDefinitionForm';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Switch } from '@/Components/ui/switch';
import { useForm } from '@inertiajs/react';
import { ChevronRight, ChevronsUpDown, PlusCircle, Trash } from 'lucide-react';
import React, { useEffect, useState } from 'react';

const StatusEditor = function ({
    status,
    applications,
    environments,
    policies,
    onStatusChange,
    onDelete,
}: {
    status?: FeatureFlagStatus;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
    policies: PolicyCollection;
    onStatusChange?: (status: FeatureFlagStatus) => void;
    onDelete?: (status: FeatureFlagStatus) => void;
}) {
    const [applicationsOpen, setApplicationsOpen] = useState(false);
    const [environmentsOpen, setEnvironmentsOpen] = useState(false);
    const [application, setApplication] = useState<Application | null>(status?.application ?? null);
    const [environment, setEnvironment] = useState<Environment | null>(status?.environment ?? null);
    const [showPolicy, setShowPolicy] = useState<boolean>(false);

    const { data, setData, errors, processing } = useForm<FeatureFlagStatus>({
        id: status?.id ?? undefined,
        application,
        environment,
        feature_flag: status?.feature_flag ?? null,
        status: status?.status ?? false,
        definition: status?.definition,
    });

    useEffect(() => {
        if (onStatusChange) {
            onStatusChange(data);
        }
    }, [data]);

    const handleDelete = (status: FeatureFlagStatus | undefined) => {
        if (onDelete && status) {
            onDelete(status);
        }
    };

    return (
        <>
            <Card>
                <CardHeader>
                    <CardTitle className="flex flex-row justify-between">
                        <div className="flex flex-row items-center">
                            <div className="flex items-center">
                                <Popover open={applicationsOpen} onOpenChange={setApplicationsOpen}>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            aria-expanded={applicationsOpen}
                                            className="w-fit justify-between"
                                        >
                                            {data.application ? (
                                                <div className="flex flex-row items-center">
                                                    <IconColor color={data.application.color} className="mr-2" />{' '}
                                                    {data.application.display_name}
                                                </div>
                                            ) : (
                                                'Select application...'
                                            )}
                                            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-[200px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search application..." />
                                            <CommandList>
                                                <CommandEmpty>No application found.</CommandEmpty>
                                                <CommandGroup>
                                                    {applications.map((application) => (
                                                        <CommandItem
                                                            key={application.id}
                                                            value={application.id as string}
                                                            onSelect={(currentValue) => {
                                                                setData(
                                                                    'application',
                                                                    currentValue === application.id
                                                                        ? application
                                                                        : null,
                                                                );
                                                                setApplicationsOpen(false);
                                                            }}
                                                        >
                                                            <IconColor color={application.color} className="mr-2" />
                                                            <p>{application.display_name}</p>
                                                        </CommandItem>
                                                    ))}
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                            </div>
                            <ChevronRight className="inline-block" />
                            <div className="flex items-center">
                                <Popover open={environmentsOpen} onOpenChange={setEnvironmentsOpen}>
                                    <PopoverTrigger asChild disabled={application === undefined}>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            aria-expanded={environmentsOpen}
                                            className="w-fit justify-between"
                                        >
                                            {data.environment ? (
                                                <div className="flex flex-row items-center">
                                                    <IconColor color={data.environment.color} className="mr-2" />{' '}
                                                    {data.environment.name}
                                                </div>
                                            ) : (
                                                'Select environment...'
                                            )}
                                            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-[200px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search environment..." />
                                            <CommandList>
                                                <CommandEmpty>No environment found.</CommandEmpty>
                                                <CommandGroup>
                                                    {environments.map((environment) => (
                                                        <CommandItem
                                                            key={environment.id}
                                                            value={environment.id as string}
                                                            onSelect={(currentValue) => {
                                                                setData(
                                                                    'environment',
                                                                    currentValue === environment.id
                                                                        ? environment
                                                                        : null,
                                                                );
                                                                setEnvironmentsOpen(false);
                                                            }}
                                                        >
                                                            <IconColor color={environment.color} className="mr-2" />
                                                            <p>{environment.name}</p>
                                                        </CommandItem>
                                                    ))}
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                            </div>
                        </div>
                        <div className="flex flex-row items-center gap-2">
                            <Switch
                                id="enabled"
                                checked={data.status}
                                onCheckedChange={(active) => setData('status', active)}
                            />
                            <Label htmlFor="enabled">Enabled</Label>
                        </div>
                    </CardTitle>
                </CardHeader>
                <CardContent className="w-full">
                    <div className="w-full justify-center items-center">
                        {!showPolicy && (status === undefined || (status.definition?.length ?? 0) === 0) && (
                            <Button
                                variant="outline"
                                className="mx-auto block"
                                type="button"
                                onClick={() => setShowPolicy(true)}
                            >
                                <PlusCircle className="inline-block" /> Add Conditions
                            </Button>
                        )}

                        {status !== undefined && ((status.definition?.length ?? 0) > 0 || showPolicy) && (
                            <PolicyDefinitionForm
                                data={status as FeatureFlagStatus}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                policies={policies}
                            />
                        )}
                    </div>
                    <CardFooter className="p-0">
                        <AlertDialog>
                            <AlertDialogTrigger asChild>
                                <Button type="button" variant="ghost" className="ml-auto text-primary/40">
                                    <Trash className="" /> Delete
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Delete status</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Are you sure you want to delete this status?
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                    <AlertDialogAction asChild>
                                        <Button variant="destructive" onClick={() => handleDelete(status)}>
                                            Delete
                                        </Button>
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </CardFooter>
                </CardContent>
            </Card>
        </>
    );
};

export default StatusEditor;
