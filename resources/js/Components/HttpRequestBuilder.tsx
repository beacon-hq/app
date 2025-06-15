import { FeatureFlagStatus, PolicyCollection, PolicyDefinitionCollection, PolicyDefinitionType } from '@/Application';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Sheet, SheetClose, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/Components/ui/sheet';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Textarea } from '@/Components/ui/textarea';
import { Clipboard, Code2 } from 'lucide-react';
import React, { useEffect, useState } from 'react';

interface HttpRequestBuilderProps {
    status?: FeatureFlagStatus;
    featureFlagName?: string;
    policies: PolicyCollection;
    definition: PolicyDefinitionCollection;
}

// Helper function to create nested objects from dot-notation strings
const createNestedObject = (subject: string, value: string): any => {
    const parts = subject.split('.');
    if (parts.length === 1) {
        return { [subject]: value };
    }

    const nestedObj: any = {};
    let current = nestedObj;

    // Create nested structure for all parts except the last one
    for (let i = 0; i < parts.length - 1; i++) {
        current[parts[i]] = {};
        current = current[parts[i]];
    }

    // Set the value at the deepest level
    current[parts[parts.length - 1]] = value;

    return nestedObj;
};

// Helper function to recursively process policy definitions
const processDefinitionsRecursively = (
    definitions: PolicyDefinitionCollection,
    policies: PolicyCollection,
    processedPolicyIds: Set<string> = new Set(),
    seenSubjects: Set<string> = new Set(),
): PolicyDefinitionCollection => {
    if (!definitions || !policies) return [];

    const result: PolicyDefinitionCollection = [];

    for (const definition of definitions) {
        // Skip operator type definitions
        if (definition.type === PolicyDefinitionType.OPERATOR || definition.type === PolicyDefinitionType.DATETIME) {
            continue;
        }

        // If it's a policy type, find the referenced policy and process its definitions
        if (definition.type === PolicyDefinitionType.POLICY) {
            const policyId = definition.subject;

            // Skip if we've already processed this policy to prevent infinite recursion
            if (processedPolicyIds.has(policyId)) {
                continue;
            }

            // Find the referenced policy
            const referencedPolicy = policies.find((policy) => policy.id === policyId);

            if (referencedPolicy && referencedPolicy.definition) {
                // Mark this policy as processed
                processedPolicyIds.add(policyId);

                // Recursively process the referenced policy's definitions
                const nestedDefinitions = processDefinitionsRecursively(
                    referencedPolicy.definition,
                    policies,
                    processedPolicyIds,
                    seenSubjects,
                );

                // Add the nested definitions to the result
                result.push(...nestedDefinitions);

                // Remove this policy from processed set after we're done with it
                processedPolicyIds.delete(policyId);
            }
        } else {
            // For non-policy definitions, check if we've already seen this subject
            if (!seenSubjects.has(definition.subject)) {
                // Add the subject to the set of seen subjects
                seenSubjects.add(definition.subject);
                // Add the definition to the result
                result.push(definition);
            }
        }
    }

    return result;
};

const HttpRequestBuilder = ({ status, featureFlagName, policies, definition }: HttpRequestBuilderProps) => {
    const [isOpen, setIsOpen] = useState<boolean>(false);

    // Load saved values from localStorage
    const loadFromLocalStorage = () => {
        // Check if localStorage is available (prevents SSR issues)
        if (typeof window === 'undefined' || !window.localStorage) {
            return {
                apiKey: '',
                selectedTab: 'curl',
                contextValues: {
                    app_name: status?.application?.name || '',
                    environment: status?.environment?.name || '',
                    scope_type: 'array',
                    scope: '',
                    session_id: '',
                    ip: '',
                    user_agent: '',
                    referrer: '',
                    url: '',
                    method: '',
                },
            };
        }

        try {
            // Load global settings (API key and output format)
            const savedApiKey = localStorage.getItem('httpRequestBuilder_apiKey') || '';
            const savedTab = localStorage.getItem('httpRequestBuilder_selectedTab') || 'curl';

            // Default context values
            const defaultContextValues = {
                app_name: status?.application?.name || '',
                environment: status?.environment?.name || '',
                scope_type: 'array',
                scope: '',
                session_id: '',
                ip: '',
                user_agent: '',
                referrer: '',
                url: '',
                method: '',
            };

            // Create an instance-specific key for context values
            // This ensures each feature flag has its own saved context
            const instanceKey = `httpRequestBuilder_contextValues_${featureFlagName || 'default'}`;
            const savedContextValues = localStorage.getItem(instanceKey);
            const parsedContextValues = savedContextValues ? JSON.parse(savedContextValues) : {};

            // Merge saved values with defaults
            return {
                apiKey: savedApiKey,
                selectedTab: savedTab,
                contextValues: {
                    ...defaultContextValues,
                    ...parsedContextValues,
                    // Always ensure app_name and environment from status take precedence
                    app_name: status?.application?.name || parsedContextValues.app_name || '',
                    environment: status?.environment?.name || parsedContextValues.environment || '',
                },
            };
        } catch (error) {
            console.error('Error loading from localStorage:', error);
            return {
                apiKey: '',
                selectedTab: 'curl',
                contextValues: {
                    app_name: status?.application?.name || '',
                    environment: status?.environment?.name || '',
                    scope_type: 'array',
                    scope: '',
                    session_id: '',
                    ip: '',
                    user_agent: '',
                    referrer: '',
                    url: '',
                    method: '',
                },
            };
        }
    };

    const savedValues = loadFromLocalStorage();

    const [apiKey, setApiKey] = useState<string>(savedValues.apiKey);
    const [selectedTab, setSelectedTab] = useState<string>(savedValues.selectedTab);
    const [contextValues, setContextValues] = useState<Record<string, string>>(savedValues.contextValues);

    const [curlCommand, setCurlCommand] = useState<string>('');
    const [httpieCommand, setHttpieCommand] = useState<string>('');
    const [pennantCommand] = useState<string>(`Feature::active('${featureFlagName}')`);
    const [copied, setCopied] = useState<boolean>(false);

    useEffect(() => {
        if (status?.application) {
            setContextValues((prev) => ({
                ...prev,
                app_name: status.application?.name || '',
            }));
        }

        if (status?.environment) {
            setContextValues((prev) => ({
                ...prev,
                environment: status.environment?.name || '',
            }));
        }
    }, [status]);

    // Save values to localStorage when they change
    // Note: API key and output format are saved globally (shared across all instances)
    // while context values are saved per feature flag (instance-specific)
    const saveToLocalStorage = (key: string, value: any) => {
        // Check if localStorage is available (prevents SSR issues)
        if (typeof window === 'undefined' || !window.localStorage) {
            return;
        }

        try {
            if (typeof value === 'object') {
                localStorage.setItem(key, JSON.stringify(value));
            } else {
                localStorage.setItem(key, value);
            }
        } catch (error) {
            console.error(`Error saving ${key} to localStorage:`, error);
        }
    };

    useEffect(() => {
        saveToLocalStorage('httpRequestBuilder_apiKey', apiKey);
    }, [apiKey]);

    useEffect(() => {
        saveToLocalStorage('httpRequestBuilder_selectedTab', selectedTab);
    }, [selectedTab]);

    useEffect(() => {
        // Create an instance-specific key for context values
        const instanceKey = `httpRequestBuilder_contextValues_${featureFlagName || 'default'}`;
        saveToLocalStorage(instanceKey, contextValues);
    }, [contextValues, featureFlagName]);

    useEffect(() => {
        generateCommands();
    }, [apiKey, contextValues, featureFlagName]);

    const handleContextChange = (key: string, value: string) => {
        setContextValues((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    const generateCommands = () => {
        if (!featureFlagName) return;

        // Prepare context values
        const preparedContext = { ...contextValues };

        // Always set scope_type to "array"
        preparedContext.scope_type = 'array';

        // Filter out empty values except for scope
        const filteredContext = Object.entries(preparedContext)
            .filter(([key, value]) => value !== '' || key === 'scope')
            .reduce(
                (acc, [key, value]) => {
                    // Handle scope specially to ensure it's either an empty array or an object
                    if (key === 'scope') {
                        if (!value) {
                            acc[key] = [];
                        } else {
                            const scopeItems = value.split(',').filter(Boolean);
                            const scopeObj: Record<string, any> = {};

                            for (const item of scopeItems) {
                                const [subject, itemValue] = item.split(':');

                                if (subject.includes('.')) {
                                    // Handle nested object for subjects with periods
                                    const nestedObj = createNestedObject(subject, itemValue);
                                    // Merge the nested object with the scope object
                                    Object.assign(scopeObj, nestedObj);
                                } else {
                                    // Handle regular subjects
                                    scopeObj[subject] = itemValue as any;
                                }
                            }

                            acc[key] = scopeObj;
                        }
                    } else {
                        acc[key] = value;
                    }
                    return acc;
                },
                {} as Record<string, any>,
            );

        // Generate curl command
        let curlCmd = `curl -X POST "${window.location.origin}/api/features/${featureFlagName}"`;

        curlCmd += ` -H "Authorization: Bearer ${apiKey !== '' ? apiKey : '<api key>'}"`;

        curlCmd += ` -H "Content-Type: application/json"`;

        if (Object.keys(filteredContext).length > 0) {
            curlCmd += ` -d '${JSON.stringify(filteredContext)}'`;
        }

        setCurlCommand(curlCmd);

        // Generate httpie command
        // Format the JSON exactly as shown in the example
        // The example shows: {"scope_type":"array","scope":{"email": "test@example.net", "user": {"id": 7}},...}
        // Note that property names don't have quotes but values do
        let jsonPayload = JSON.stringify(filteredContext);

        // Don't modify the JSON structure for httpie - it expects valid JSON
        let httpieCmd = `echo '${jsonPayload}' | http POST "${window.location.origin}/api/features/${featureFlagName}"`;

        httpieCmd += ` Authorization:"Bearer ${apiKey ?? '<api key>'}"`;

        httpieCmd += ` Accept:application/json`;

        setHttpieCommand(httpieCmd);
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    // Function to handle sheet open/close
    const handleSheetOpenChange = (open: boolean) => {
        setIsOpen(open);

        // If the sheet is being opened, reload values from localStorage
        if (open) {
            const savedValues = loadFromLocalStorage();
            setApiKey(savedValues.apiKey);
            setSelectedTab(savedValues.selectedTab);
            setContextValues(savedValues.contextValues);
        }
    };

    return (
        <Sheet open={isOpen} onOpenChange={handleSheetOpenChange}>
            <SheetTrigger asChild>
                <Button variant="outline" size="sm" className="gap-2">
                    <Code2 className="h-4 w-4" />
                    API Request
                </Button>
            </SheetTrigger>
            <SheetContent side="bottom" className="w-full sm:max-w-none max-h-[80vh] overflow-y-auto">
                <SheetHeader className="flex flex-row justify-between">
                    <SheetTitle>HTTP Request Builder</SheetTitle>
                    <SheetClose asChild>
                        <Button variant="outline">Close</Button>
                    </SheetClose>
                </SheetHeader>
                <div className="grid gap-4 py-4">
                    <div className="mt-4">
                        <Tabs value={selectedTab} onValueChange={setSelectedTab}>
                            <TabsList className="mb-2">
                                <TabsTrigger value="curl">curl</TabsTrigger>
                                <TabsTrigger value="httpie">httpie</TabsTrigger>
                                <TabsTrigger value="pennant">Pennant</TabsTrigger>
                            </TabsList>
                            <TabsContent value="curl">
                                <Card>
                                    <CardContent className="pt-6">
                                        <div className="relative">
                                            <Textarea value={curlCommand} readOnly className="font-mono text-sm h-24" />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="absolute top-2 right-2"
                                                onClick={() => copyToClipboard(curlCommand)}
                                            >
                                                <Clipboard className="h-4 w-4" />
                                                {copied ? 'Copied!' : 'Copy'}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                            <TabsContent value="httpie">
                                <Card>
                                    <CardContent className="pt-6">
                                        <div className="relative">
                                            <Textarea
                                                value={httpieCommand}
                                                readOnly
                                                className="font-mono text-sm h-24"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="absolute top-2 right-2"
                                                onClick={() => copyToClipboard(httpieCommand)}
                                            >
                                                <Clipboard className="h-4 w-4" />
                                                {copied ? 'Copied!' : 'Copy'}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                            <TabsContent value="pennant">
                                <Card>
                                    <CardContent className="pt-6">
                                        <div className="relative">
                                            <Textarea
                                                value={pennantCommand}
                                                readOnly
                                                className="font-mono text-sm h-24 text-primary"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="absolute top-2 right-2"
                                                onClick={() => copyToClipboard(pennantCommand)}
                                            >
                                                <Clipboard className="h-4 w-4" />
                                                {copied ? 'Copied!' : 'Copy'}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="apiKey">API Key</Label>
                            <Input
                                id="apiKey"
                                value={apiKey}
                                onChange={(e) => setApiKey(e.target.value)}
                                placeholder="Enter your API key"
                                className="mt-1"
                            />
                        </div>
                    </div>

                    <div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <Label htmlFor="app_name">App Name</Label>
                                <Input
                                    id="app_name"
                                    value={contextValues.app_name}
                                    onChange={(e) => handleContextChange('app_name', e.target.value)}
                                    placeholder="App name"
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <Label htmlFor="environment">Environment</Label>
                                <Input
                                    id="environment"
                                    value={contextValues.environment}
                                    onChange={(e) => handleContextChange('environment', e.target.value)}
                                    placeholder="Environment"
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <Accordion type="multiple" className="w-full">
                            {definition && definition.length > 0 && (
                                <AccordionItem value="more-options">
                                    <AccordionTrigger>Scope</AccordionTrigger>
                                    <AccordionContent>
                                        <div className="mt-4 px-1">
                                            {processDefinitionsRecursively(
                                                definition,
                                                policies,
                                                new Set(),
                                                new Set(),
                                            ).map((definition, key) => {
                                                return (
                                                    <div key={key} className="mb-2">
                                                        <Label htmlFor={`scope_${key}`}>{definition.subject}</Label>
                                                        <Input
                                                            id={`scope_${key}`}
                                                            value={
                                                                contextValues.scope
                                                                    ? contextValues.scope
                                                                          .split(',')
                                                                          .find((s) =>
                                                                              s.startsWith(definition.subject + ':'),
                                                                          )
                                                                          ?.split(':')[1] || ''
                                                                    : ''
                                                            }
                                                            onChange={(e) => {
                                                                const newScope = contextValues.scope
                                                                    ? contextValues.scope.split(',').filter(Boolean)
                                                                    : [];
                                                                const index = newScope.findIndex((s) =>
                                                                    s.startsWith(definition.subject + ':'),
                                                                );
                                                                if (index !== -1) {
                                                                    newScope[index] =
                                                                        `${definition.subject}:${e.target.value}`;
                                                                } else {
                                                                    newScope.push(
                                                                        `${definition.subject}:${e.target.value}`,
                                                                    );
                                                                }
                                                                handleContextChange('scope', newScope.join(','));
                                                            }}
                                                            placeholder={`Enter ${definition.subject}`}
                                                            className="mt-1"
                                                        />
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            )}
                            <AccordionItem value="context">
                                <AccordionTrigger>Context</AccordionTrigger>
                                <AccordionContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 px-1">
                                        <div>
                                            <Label htmlFor="session_id">Session ID</Label>
                                            <Input
                                                id="session_id"
                                                value={contextValues.session_id}
                                                onChange={(e) => handleContextChange('session_id', e.target.value)}
                                                placeholder="Session ID"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="ip">IP Address</Label>
                                            <Input
                                                id="ip"
                                                value={contextValues.ip}
                                                onChange={(e) => handleContextChange('ip', e.target.value)}
                                                placeholder="IP Address"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="user_agent">User Agent</Label>
                                            <Input
                                                id="user_agent"
                                                value={contextValues.user_agent}
                                                onChange={(e) => handleContextChange('user_agent', e.target.value)}
                                                placeholder="User Agent"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="referrer">Referrer</Label>
                                            <Input
                                                id="referrer"
                                                value={contextValues.referrer}
                                                onChange={(e) => handleContextChange('referrer', e.target.value)}
                                                placeholder="Referrer"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="url">URL</Label>
                                            <Input
                                                id="url"
                                                value={contextValues.url}
                                                onChange={(e) => handleContextChange('url', e.target.value)}
                                                placeholder="URL"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="method">Method</Label>
                                            <Input
                                                id="method"
                                                value={contextValues.method}
                                                onChange={(e) => handleContextChange('method', e.target.value)}
                                                placeholder="Method"
                                                className="mt-1"
                                            />
                                        </div>
                                    </div>
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    );
};

export default HttpRequestBuilder;
