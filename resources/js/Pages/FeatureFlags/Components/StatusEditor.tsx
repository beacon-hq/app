import {
    ApplicationCollection,
    EnvironmentCollection,
    FeatureFlagStatus,
    PolicyCollection,
    PolicyDefinitionType,
    RolloutStrategy,
    VariantStrategy,
} from '@/Application';
import HttpRequestBuilder from '@/Components/HttpRequestBuilder';
import { IconColor } from '@/Components/IconColor';
import MultiValueInput from '@/Components/MultiValueInput';
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
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Slider } from '@/Components/ui/slider';
import { Switch } from '@/Components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { useFeatureFlagStore, Variant } from '@/stores/featureFlagStore';
import { useTheme } from '@/theme-provider';
import Editor from '@monaco-editor/react';
import { AlignHorizontalSpaceAround, ChevronRight, ChevronsUpDown, PlusCircle, Trash } from 'lucide-react';
import React, { useCallback, useRef, useState } from 'react';

const VariantValueInput: React.FC<{
    variant: Variant;
    onUpdate: (updates: Partial<Variant>) => void;
}> = ({ variant, onUpdate, ...props }) => {
    return (
        <Input
            id={`value-${variant.id}`}
            value={variant.value}
            onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                onUpdate({ value: e.target.value });
            }}
            type={variant.type === 'integer' || variant.type === 'float' ? 'number' : 'text'}
            step={variant.type === 'float' ? '0.01' : '1'}
            {...props}
        />
    );
};

const StatusEditor: React.FC<{
    status: FeatureFlagStatus;
    applications: ApplicationCollection;
    environments: EnvironmentCollection;
    policies: PolicyCollection;
}> = function ({ status, applications, environments, policies }) {
    const { theme } = useTheme();
    const editorTheme =
        theme === 'system'
            ? window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'vs-dark'
                : 'light'
            : theme === 'dark'
              ? 'vs-dark'
              : 'light';

    const [applicationsOpen, setApplicationsOpen] = useState(false);
    const [environmentsOpen, setEnvironmentsOpen] = useState(false);

    // Add state for active tab to prevent infinite loops when switching tabs
    const [activeTab, setActiveTab] = useState<string>('conditions');

    // Track if we're currently changing tabs to prevent state updates during tab changes
    const isChangingTabRef = useRef(false);

    // Custom tab change handler to prevent state updates during tab changes
    const handleTabChange = (value: string) => {
        isChangingTabRef.current = true;
        setActiveTab(value);
        // Reset the flag after a delay to allow the tab change to complete
        setTimeout(() => {
            isChangingTabRef.current = false;
        }, 200);
    };

    // Use the merged feature flag store
    const {
        featureFlag,
        updateStatusRollout,
        updateStatusVariant,
        removeStatusVariant,
        addStatusVariant,
        distributeStatusVariantsEvenly,
        updateStatusVariantStickiness,
        updateStatus,
        deleteStatus,
        addStatusPolicyDefinition,
    } = useFeatureFlagStore();

    // Get current rollout and variants from status
    const currentRollout = {
        percentage: status?.rollout_percentage ?? 100,
        strategy: status?.rollout_strategy ?? RolloutStrategy.RANDOM,
        context: status?.rollout_context ?? [],
    };

    const currentVariants = (status?.variants as Variant[]) ?? [];

    const calculateTotalPercentage = useCallback(
        (variantsList: Variant[] = currentVariants): number => {
            return variantsList.reduce((total, variant) => total + variant.percentage, 0);
        },
        [currentVariants],
    );

    return (
        <Card data-dusk="card-feature-flag-status">
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
                                        data-dusk="select-application"
                                    >
                                        {status?.application ? (
                                            <div className="flex flex-row items-center">
                                                <IconColor color={status?.application.color} className="mr-2" />{' '}
                                                {status?.application.display_name}
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
                                                {applications.map((application, idx) => (
                                                    <CommandItem
                                                        key={application.id}
                                                        value={application.id as string}
                                                        onSelect={() => {
                                                            updateStatus({
                                                                ...status,
                                                                application,
                                                            } as FeatureFlagStatus);
                                                            setApplicationsOpen(false);
                                                        }}
                                                        data-dusk={`select-option-application-${idx}`}
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
                                <PopoverTrigger asChild disabled={status?.application === null}>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        aria-expanded={environmentsOpen}
                                        className="w-fit justify-between"
                                        data-dusk="select-environment"
                                    >
                                        {status?.environment ? (
                                            <div className="flex flex-row items-center">
                                                <IconColor
                                                    color={status?.environment?.color as string}
                                                    className="mr-2"
                                                />{' '}
                                                {status?.environment?.name as string}
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
                                                {environments.map((environment, idx) => (
                                                    <CommandItem
                                                        key={environment.id}
                                                        value={environment.id as string}
                                                        onSelect={() => {
                                                            updateStatus({
                                                                ...status,
                                                                environment,
                                                            } as FeatureFlagStatus);
                                                            setEnvironmentsOpen(false);
                                                        }}
                                                        data-dusk={`select-option-environment-${idx}`}
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
                    <div className="flex flex-row items-center gap-4">
                        <div className="flex flex-row items-center gap-2">
                            <Switch
                                id="enabled"
                                checked={status?.status ?? false}
                                onCheckedChange={(active) =>
                                    updateStatus({ ...status, status: active } as FeatureFlagStatus)
                                }
                                data-dusk="switch-enabled"
                            />
                            <Label htmlFor="enabled">Enabled</Label>
                        </div>
                        {status !== null && (
                            <HttpRequestBuilder
                                status={status}
                                featureFlagName={featureFlag?.name}
                                policies={policies}
                                definition={status?.definition ?? []}
                            />
                        )}
                    </div>
                </CardTitle>
            </CardHeader>
            <CardContent className="w-full">
                {/* Use a controlled value for Tabs with custom handler to prevent infinite loops */}
                <Tabs className="w-full" value={activeTab} onValueChange={handleTabChange}>
                    <TabsList className="w-fit mx-auto block mb-6">
                        <TabsTrigger value="conditions" data-dusk="tab-conditions">
                            Conditions
                        </TabsTrigger>
                        <TabsTrigger value="rollout" data-dusk="tab-rollout">
                            Rollout
                        </TabsTrigger>
                        <TabsTrigger value="variants" data-dusk="tab-variants">
                            Variants
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="conditions">
                        <div className="w-full justify-center items-center">
                            {(status === undefined || (status?.definition?.length ?? 0) === 0) && (
                                <Button
                                    variant="outline"
                                    className="mx-auto block"
                                    type="button"
                                    onClick={() => {
                                        updateStatus({
                                            ...status,
                                            definition: [
                                                ...(status.definition || []),
                                                {
                                                    type: PolicyDefinitionType.EXPRESSION,
                                                    subject: '',
                                                    operator: null,
                                                    values: [],
                                                },
                                            ],
                                        });
                                    }}
                                    data-dusk="button-add-conditions"
                                >
                                    <PlusCircle className="inline-block mr-2" /> Add Conditions
                                </Button>
                            )}

                            {status !== undefined && (status?.definition?.length ?? 0) > 0 ? (
                                <PolicyDefinitionForm status={status} policies={policies} />
                            ) : null}
                        </div>
                    </TabsContent>
                    <TabsContent value="rollout" className="flex flex-col gap-6">
                        <div className="flex flex-col gap-4">
                            <Label htmlFor="percentage">Rollout Percentage</Label>
                            <div className="flex flex-row gap-2">
                                <Slider
                                    id="percentage"
                                    min={1}
                                    value={[currentRollout.percentage]}
                                    onValueChange={(value) => {
                                        if (status?.id) {
                                            updateStatusRollout(status.id, { percentage: value[0] });
                                        }
                                    }}
                                    data-dusk="slider-rollout-percentage"
                                />
                                <p className="w-10">{currentRollout.percentage}%</p>
                            </div>
                            <p className="text-xs">
                                Rollout percentage determines how many users will see this status. For example, if you
                                set it to 50%, then 50% of the users will see this status.
                            </p>
                        </div>
                        <div>
                            <Label htmlFor="rollout-strategy">Stickiness</Label>
                            <div className="flex flex-row gap-4 mt-4">
                                <Select
                                    value={currentRollout.strategy}
                                    onValueChange={(value) => {
                                        if (status?.id) {
                                            updateStatusRollout(status.id, { strategy: value as RolloutStrategy });
                                        }
                                    }}
                                    disabled={currentRollout.percentage === 100}
                                >
                                    <SelectTrigger className="w-1/2 h-9 py-0" data-dusk="select-rollout-strategy">
                                        <SelectValue asChild>
                                            {currentRollout.strategy === RolloutStrategy.CONTEXT ? (
                                                <div className="flex flex-col justify-start text-left">
                                                    <p className="font-bold">Sticky</p>
                                                    <p className="text-sm text-primary/80 -mt-1">
                                                        Use one or more context values to segment users, with
                                                        stickiness.
                                                    </p>
                                                </div>
                                            ) : (
                                                <div className="flex flex-col justify-start text-left gap-0">
                                                    <p className="font-bold">Random</p>
                                                    <p className="text-sm text-primary/80 -mt-1">
                                                        Random distribution, with no stickiness.
                                                    </p>
                                                </div>
                                            )}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value={RolloutStrategy.RANDOM}>
                                                <p className="font-bold">Random</p>
                                                <p className="text-sm text-primary/80">
                                                    Random distribution, with no stickiness.
                                                </p>
                                            </SelectItem>
                                            <SelectItem
                                                value={RolloutStrategy.CONTEXT}
                                                data-dusk="select-option-rollout-strategy-sticky"
                                            >
                                                <p className="f ont-bold">Sticky</p>
                                                <p className="text-sm text-primary/80">
                                                    Use one or more context values to segment users, with stickiness.
                                                </p>
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <MultiValueInput
                                    className="w-96"
                                    id="context"
                                    values={currentRollout.context}
                                    setValues={(values) => {
                                        if (status?.id) {
                                            updateStatusRollout(status.id, { context: values as string[] });
                                        }
                                    }}
                                    type="text"
                                    placeholder="enter context property name…"
                                    disabled={
                                        currentRollout.strategy === RolloutStrategy.RANDOM ||
                                        currentRollout.percentage === 100
                                    }
                                    data-dusk="input-rollout-context"
                                />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="variants" className="flex flex-col gap-6">
                        <div className="flex flex-col gap-4">
                            <div className="flex flex-row items-center justify-between">
                                <Label htmlFor="variants">Variants</Label>
                                {currentVariants.length > 1 && (
                                    <>
                                        <div className="text-sm text-primary/60">
                                            Total: {calculateTotalPercentage()}%
                                        </div>
                                        <Button
                                            variant="outline"
                                            type="button"
                                            onClick={() => {
                                                if (status?.id) {
                                                    distributeStatusVariantsEvenly(status.id);
                                                }
                                            }}
                                            data-dusk="button-distribute-evenly"
                                        >
                                            <AlignHorizontalSpaceAround /> Distribute Evenly
                                        </Button>
                                    </>
                                )}
                            </div>

                            {currentVariants.length > 0 && (
                                <>
                                    <div className="flex flex-col gap-4 mb-6">
                                        {currentVariants.map((variant, idx) => (
                                            <div
                                                key={variant.id}
                                                className={`flex ${variant.type === 'json' ? 'flex-col' : 'flex-row items-center'} gap-4 p-4 border rounded-md`}
                                            >
                                                {variant.type === 'json' ? (
                                                    <>
                                                        <div className="flex flex-row items-center gap-4 w-full">
                                                            <div className="w-32">
                                                                <Label
                                                                    htmlFor={`type-${variant.id}`}
                                                                    className="mb-2 block"
                                                                >
                                                                    Type
                                                                </Label>
                                                                <Select
                                                                    value={variant.type}
                                                                    onValueChange={(value) => {
                                                                        if (status?.id) {
                                                                            updateStatusVariant(status.id, variant.id, {
                                                                                type: value as Variant['type'],
                                                                            });
                                                                        }
                                                                    }}
                                                                >
                                                                    <SelectTrigger id={`type-${variant.id}`}>
                                                                        <SelectValue placeholder="Select type" />
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        <SelectItem value="string">String</SelectItem>
                                                                        <SelectItem value="integer">Integer</SelectItem>
                                                                        <SelectItem value="float">Float</SelectItem>
                                                                        <SelectItem value="json">JSON</SelectItem>
                                                                    </SelectContent>
                                                                </Select>
                                                            </div>
                                                            <div className="ml-auto">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    onClick={() => {
                                                                        if (status?.id) {
                                                                            removeStatusVariant(status.id, variant.id);
                                                                        }
                                                                    }}
                                                                >
                                                                    <Trash className="h-4 w-4" />
                                                                </Button>
                                                            </div>
                                                        </div>
                                                        <div className="w-full">
                                                            <Label
                                                                htmlFor={`value-${variant.id}`}
                                                                className="mb-2 block"
                                                            >
                                                                Return Value
                                                            </Label>
                                                            <Editor
                                                                height="200px"
                                                                defaultLanguage="json"
                                                                theme={editorTheme}
                                                                value={variant.value}
                                                                onChange={(value) => {
                                                                    if (status?.id) {
                                                                        updateStatusVariant(status.id, variant.id, {
                                                                            value: value || '',
                                                                        });
                                                                    }
                                                                }}
                                                                options={{
                                                                    minimap: { enabled: false },
                                                                    scrollBeyondLastLine: false,
                                                                    automaticLayout: true,
                                                                    formatOnPaste: true,
                                                                    formatOnType: true,
                                                                }}
                                                            />
                                                        </div>
                                                        <div className="w-full">
                                                            <Label
                                                                htmlFor={`percentage-${variant.id}`}
                                                                className="mb-2 block"
                                                            >
                                                                Percentage
                                                            </Label>
                                                            <div className="flex flex-row items-center gap-2">
                                                                <Slider
                                                                    id={`percentage-${variant.id}`}
                                                                    min={0}
                                                                    max={
                                                                        100 -
                                                                        calculateTotalPercentage() +
                                                                        variant.percentage
                                                                    }
                                                                    value={[variant.percentage]}
                                                                    onValueChange={(value) => {
                                                                        if (status?.id) {
                                                                            updateStatusVariant(status.id, variant.id, {
                                                                                percentage: value[0],
                                                                            });
                                                                        }
                                                                    }}
                                                                />
                                                                <div className="w-12 text-right">
                                                                    {variant.percentage}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <>
                                                        <div className="w-32">
                                                            <Label
                                                                htmlFor={`type-${variant.id}`}
                                                                className="mb-2 block"
                                                            >
                                                                Type
                                                            </Label>
                                                            <Select
                                                                value={variant.type}
                                                                onValueChange={(value) => {
                                                                    if (status?.id) {
                                                                        updateStatusVariant(status.id, variant.id, {
                                                                            type: value as Variant['type'],
                                                                        });
                                                                    }
                                                                }}
                                                            >
                                                                <SelectTrigger
                                                                    id={`type-${variant.id}`}
                                                                    data-dusk="select-variant-type"
                                                                >
                                                                    <SelectValue placeholder="Select type" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem
                                                                        value="string"
                                                                        data-dusk="select-option-variant-type-string"
                                                                    >
                                                                        String
                                                                    </SelectItem>
                                                                    <SelectItem
                                                                        value="integer"
                                                                        data-dusk="select-option-variant-type-integer"
                                                                    >
                                                                        Integer
                                                                    </SelectItem>
                                                                    <SelectItem
                                                                        value="float"
                                                                        data-dusk="select-option-variant-type-float"
                                                                    >
                                                                        Float
                                                                    </SelectItem>
                                                                    <SelectItem
                                                                        value="json"
                                                                        data-dusk="select-option-variant-type-json"
                                                                    >
                                                                        JSON
                                                                    </SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                        </div>
                                                        <div className="flex-1">
                                                            <Label
                                                                htmlFor={`value-${variant.id}`}
                                                                className="mb-2 block"
                                                            >
                                                                Return Value
                                                            </Label>
                                                            <VariantValueInput
                                                                variant={variant}
                                                                onUpdate={(updates) => {
                                                                    if (status?.id) {
                                                                        updateStatusVariant(
                                                                            status.id,
                                                                            variant.id,
                                                                            updates,
                                                                        );
                                                                    }
                                                                }}
                                                                data-dusk={`input-variant-value-${idx}`}
                                                            />
                                                        </div>
                                                        <div className="w-48">
                                                            <Label
                                                                htmlFor={`percentage-${variant.id}`}
                                                                className="mb-2 block"
                                                            >
                                                                Percentage
                                                            </Label>
                                                            <div className="flex flex-row items-center gap-2">
                                                                <Slider
                                                                    id={`percentage-${variant.id}`}
                                                                    min={0}
                                                                    max={
                                                                        100 -
                                                                        calculateTotalPercentage() +
                                                                        variant.percentage
                                                                    }
                                                                    value={[variant.percentage]}
                                                                    onValueChange={(value) => {
                                                                        if (status?.id) {
                                                                            updateStatusVariant(status.id, variant.id, {
                                                                                percentage: value[0],
                                                                            });
                                                                        }
                                                                    }}
                                                                />
                                                                <div className="w-12 text-right">
                                                                    {variant.percentage}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            className="mt-6"
                                                            onClick={() => {
                                                                if (status?.id) {
                                                                    removeStatusVariant(status.id, variant.id);
                                                                }
                                                            }}
                                                        >
                                                            <Trash className="h-4 w-4" />
                                                        </Button>
                                                    </>
                                                )}
                                            </div>
                                        ))}
                                        <div className="flex justify-center gap-2">
                                            <Button
                                                variant="outline"
                                                type="button"
                                                onClick={() => {
                                                    if (status?.id) {
                                                        addStatusVariant(status.id);
                                                    }
                                                }}
                                                data-dusk="button-feature-flags-add-variant"
                                            >
                                                <PlusCircle className="inline-block mr-2" /> Add
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-4">
                                        <Label htmlFor="variant-strategy">Variant Stickiness</Label>
                                        <div className="flex flex-row gap-4">
                                            <Select
                                                value={status?.variant_strategy ?? VariantStrategy.RANDOM}
                                                onValueChange={(value) => {
                                                    if (status?.id) {
                                                        updateStatusVariantStickiness(status.id, {
                                                            strategy: value as VariantStrategy,
                                                        });
                                                    }
                                                }}
                                                disabled={currentVariants.length === 0}
                                            >
                                                <SelectTrigger className="w-1/2 h-9 py-0">
                                                    <SelectValue asChild>
                                                        {(status?.variant_strategy ?? VariantStrategy.RANDOM) ===
                                                        VariantStrategy.CONTEXT ? (
                                                            <div className="flex flex-col justify-start text-left">
                                                                <p className="font-bold">Sticky</p>
                                                                <p className="text-sm text-primary/80 -mt-1">
                                                                    Use context values to ensure users consistently get
                                                                    the same variant.
                                                                </p>
                                                            </div>
                                                        ) : (
                                                            <div className="flex flex-col justify-start text-left gap-0">
                                                                <p className="font-bold">Random</p>
                                                                <p className="text-sm text-primary/80 -mt-1">
                                                                    Random variant selection on each evaluation.
                                                                </p>
                                                            </div>
                                                        )}
                                                    </SelectValue>
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value={VariantStrategy.RANDOM}>
                                                            <p className="font-bold">Random</p>
                                                            <p className="text-sm text-primary/80">
                                                                Random variant selection on each evaluation.
                                                            </p>
                                                        </SelectItem>
                                                        <SelectItem value={VariantStrategy.CONTEXT}>
                                                            <p className="font-bold">Sticky</p>
                                                            <p className="text-sm text-primary/80">
                                                                Use context values to ensure users consistently get the
                                                                same variant.
                                                            </p>
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <MultiValueInput
                                                className="w-96"
                                                id="variant-context"
                                                values={status?.variant_context ?? []}
                                                setValues={(values) => {
                                                    if (status?.id) {
                                                        updateStatusVariantStickiness(status.id, {
                                                            context: values as string[],
                                                        });
                                                    }
                                                }}
                                                type="text"
                                                placeholder="enter context property name…"
                                                disabled={
                                                    (status?.variant_strategy ?? VariantStrategy.RANDOM) ===
                                                        VariantStrategy.RANDOM || currentVariants.length === 0
                                                }
                                            />
                                        </div>
                                    </div>
                                </>
                            )}

                            {currentVariants.length === 0 && (
                                <Button
                                    variant="outline"
                                    className="mx-auto block"
                                    type="button"
                                    onClick={() => {
                                        if (status?.id) {
                                            addStatusVariant(status.id);
                                            addStatusVariant(status.id);
                                            distributeStatusVariantsEvenly(status.id);
                                        }
                                    }}
                                    data-dusk="button-feature-flags-add-variants"
                                >
                                    <PlusCircle className="inline-block mr-2" /> Add Variants
                                </Button>
                            )}
                        </div>
                    </TabsContent>
                </Tabs>
                <CardFooter className="p-0 mt-4">
                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <Button type="button" variant="ghost" className="ml-auto text-primary/40">
                                <Trash className="mr-2" /> Delete
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
                                    <Button
                                        variant="destructive"
                                        onClick={() => status?.id && deleteStatus(status?.id as string)}
                                    >
                                        Delete
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardFooter>
            </CardContent>
        </Card>
    );
};

export default StatusEditor;
