import { FeatureFlagStatus, PolicyCollection, PolicyDefinitionCollection, PolicyDefinitionType } from '@/Application';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import React, { useEffect, useState } from 'react';
import HttpRequest from './HttpRequest';

interface HttpRequestBuilderFormProps {
    status?: FeatureFlagStatus;
    featureFlagName?: string;
    policies: PolicyCollection;
    definition: PolicyDefinitionCollection;
}


const processDefinitionsRecursively = (
    definitions: PolicyDefinitionCollection,
    policies: PolicyCollection,
    processedPolicyIds: Set<string> = new Set(),
    seenSubjects: Set<string> = new Set(),
): PolicyDefinitionCollection => {
    if (!definitions || !policies) return [];

    const result: PolicyDefinitionCollection = [];

    for (const definition of definitions) {
        if (definition.type === PolicyDefinitionType.OPERATOR || definition.type === PolicyDefinitionType.DATETIME) {
            continue;
        }

        if (definition.type === PolicyDefinitionType.POLICY) {
            const policyId = definition.subject;

            if (processedPolicyIds.has(policyId)) {
                continue;
            }

            const referencedPolicy = policies.find((policy) => policy.id === policyId);

            if (referencedPolicy && referencedPolicy.definition) {
                processedPolicyIds.add(policyId);

                const nestedDefinitions = processDefinitionsRecursively(
                    referencedPolicy.definition,
                    policies,
                    processedPolicyIds,
                    seenSubjects,
                );

                result.push(...nestedDefinitions);

                processedPolicyIds.delete(policyId);
            }
        } else {
            if (!seenSubjects.has(definition.subject)) {
                seenSubjects.add(definition.subject);
                result.push(definition);
            }
        }
    }

    return result;
};

const HttpRequestBuilderForm = ({ status, featureFlagName, policies, definition }: HttpRequestBuilderFormProps) => {
    const loadFromLocalStorage = () => {
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
            const savedApiKey = localStorage.getItem('httpRequestBuilder_apiKey') || '';
            const savedTab = localStorage.getItem('httpRequestBuilder_selectedTab') || 'curl';

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

            const instanceKey = `httpRequestBuilder_contextValues_${featureFlagName || 'default'}`;
            const savedContextValues = localStorage.getItem(instanceKey);
            const parsedContextValues = savedContextValues ? JSON.parse(savedContextValues) : {};

            return {
                apiKey: savedApiKey,
                selectedTab: savedTab,
                contextValues: {
                    ...defaultContextValues,
                    ...parsedContextValues,
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

    const saveToLocalStorage = (key: string, value: any) => {
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
        const instanceKey = `httpRequestBuilder_contextValues_${featureFlagName || 'default'}`;
        saveToLocalStorage(instanceKey, contextValues);
    }, [contextValues, featureFlagName]);


    const handleContextChange = (key: string, value: string) => {
        setContextValues((prev) => ({
            ...prev,
            [key]: value,
        }));
    };



    return (
        <div className="grid gap-4 py-4">
            <HttpRequest
                featureFlagName={featureFlagName}
                apiKey={apiKey}
                contextValues={contextValues}
                selectedTab={selectedTab}
                onSelectedTabChange={setSelectedTab}
            />

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
    );
};

export default HttpRequestBuilderForm;
