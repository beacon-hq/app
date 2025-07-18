import { FeatureFlagStatus, PolicyCollection, PolicyDefinitionCollection } from '@/Application';
import { Button } from '@/Components/ui/button';
import { Sheet, SheetClose, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/Components/ui/sheet';
import { Code2 } from 'lucide-react';
import React, { useState } from 'react';
import HttpRequestBuilderForm from './HttpRequestBuilderForm';

interface HttpRequestBuilderProps {
    status?: FeatureFlagStatus;
    featureFlagName?: string;
    policies: PolicyCollection;
    definition: PolicyDefinitionCollection;
}


const HttpRequestBuilder = ({ status, featureFlagName, policies, definition }: HttpRequestBuilderProps) => {
    const [isOpen, setIsOpen] = useState<boolean>(false);

    const handleSheetOpenChange = (open: boolean) => {
        setIsOpen(open);
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
                <HttpRequestBuilderForm
                    status={status}
                    featureFlagName={featureFlagName}
                    policies={policies}
                    definition={definition}
                />
            </SheetContent>
        </Sheet>
    );
};

export default HttpRequestBuilder;
