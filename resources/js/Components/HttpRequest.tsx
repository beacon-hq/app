import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Textarea } from '@/Components/ui/textarea';
import { cn } from '@/lib/utils';
import { Clipboard } from 'lucide-react';
import React, { useEffect, useState } from 'react';

interface HttpRequestProps {
    featureFlagName?: string;
    apiKey: string;
    contextValues: Record<string, string>;
    selectedTab?: string;
    onSelectedTabChange?: (tab: string) => void;
    fullWidth?: boolean;
}

const createNestedObject = (subject: string, value: string): any => {
    const parts = subject.split('.');
    if (parts.length === 1) {
        return { [subject]: value };
    }

    const nestedObj: any = {};
    let current = nestedObj;

    for (let i = 0; i < parts.length - 1; i++) {
        current[parts[i]] = {};
        current = current[parts[i]];
    }

    current[parts[parts.length - 1]] = value;

    return nestedObj;
};

const HttpRequest = ({
    featureFlagName,
    apiKey,
    contextValues,
    selectedTab,
    onSelectedTabChange,
    fullWidth = false,
}: HttpRequestProps) => {
    const [curlCommand, setCurlCommand] = useState<string>('');
    const [httpieCommand, setHttpieCommand] = useState<string>('');
    const [pennantCommand] = useState<string>(
        `Feature::define('test-flag');\n\nif (Feature::active('${featureFlagName}')) {\n    // Your code here\n}`,
    );
    const [copied, setCopied] = useState<boolean>(false);

    const [internalSelectedTab, setInternalSelectedTab] = useState<string>('curl');

    const currentSelectedTab = selectedTab ?? internalSelectedTab;
    const currentOnSelectedTabChange = onSelectedTabChange ?? setInternalSelectedTab;

    useEffect(() => {
        generateCommands();
    }, [apiKey, contextValues, featureFlagName]);

    const generateCommands = () => {
        if (!featureFlagName) return;

        const preparedContext = { ...contextValues };

        preparedContext.scope_type = 'array';

        const filteredContext = Object.entries(preparedContext)
            .filter(([key, value]) => value !== '' || key === 'scope')
            .reduce(
                (acc, [key, value]) => {
                    if (key === 'scope') {
                        if (!value) {
                            acc[key] = [];
                        } else {
                            const scopeItems = value.split(',').filter(Boolean);
                            const scopeObj: Record<string, any> = {};

                            for (const item of scopeItems) {
                                const [subject, itemValue] = item.split(':');

                                if (subject.includes('.')) {
                                    const nestedObj = createNestedObject(subject, itemValue);
                                    Object.assign(scopeObj, nestedObj);
                                } else {
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

        let curlCmd = `curl -X POST "https://beacon-hq.dev/api/features/${featureFlagName}"`;

        curlCmd += ` -H "Authorization: Bearer ${apiKey !== '' ? apiKey : '<api key>'}"`;

        curlCmd += ` -H "Content-Type: application/json"`;

        curlCmd += ` -H "Accept: application/json"`;

        if (Object.keys(filteredContext).length > 0) {
            curlCmd += ` -d '${JSON.stringify(filteredContext)}'`;
        }

        setCurlCommand(curlCmd);

        let httpieCmd = `http -pb POST "https://beacon-hq.dev/api/features/${featureFlagName}"`;

        httpieCmd += ` Authorization:"Bearer ${apiKey ?? '<api key>'}"`;

        httpieCmd += ` Accept:application/json`;

        if (Object.keys(filteredContext).length > 0) {
            for (const [key, value] of Object.entries(filteredContext)) {
                if (typeof value === 'object' && value !== null) {
                    httpieCmd += ` ${key}:='${JSON.stringify(value)}'`;
                } else {
                    httpieCmd += ` ${key}="${value}"`;
                }
            }
        }

        setHttpieCommand(httpieCmd);
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <div className="mt-4">
            <Tabs value={currentSelectedTab} onValueChange={currentOnSelectedTabChange}>
                <TabsList className="mb-2">
                    <TabsTrigger value="curl">cURL</TabsTrigger>
                    <TabsTrigger value="httpie">httpie</TabsTrigger>
                    <TabsTrigger value="pennant">Pennant</TabsTrigger>
                </TabsList>
                <TabsContent value="curl">
                    <Card>
                        <CardContent className={cn({ 'pt-6': !fullWidth, 'p-0 border-0': fullWidth })}>
                            <div className="relative">
                                <Textarea
                                    value={curlCommand}
                                    readOnly
                                    className="font-mono text-sm h-24 pr-14"
                                    cols={70}
                                />
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
                        <CardContent className={cn({ 'pt-6': !fullWidth, 'p-0 border-0': fullWidth })}>
                            <div className="relative">
                                <Textarea
                                    value={httpieCommand}
                                    readOnly
                                    className="font-mono text-sm h-24 pr-14"
                                    cols={70}
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
                        <CardContent className={cn({ 'pt-6': !fullWidth, 'p-0 border-0': fullWidth })}>
                            <div className="relative">
                                <Textarea
                                    value={pennantCommand}
                                    readOnly
                                    className="font-mono text-sm h-24 text-primary"
                                    cols={70}
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
    );
};

export default HttpRequest;
