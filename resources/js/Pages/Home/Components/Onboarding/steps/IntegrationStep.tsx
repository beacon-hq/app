import HttpRequest from '@/Components/HttpRequest';
import { AccordionContent, AccordionItem } from '@/Components/ui/accordion';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { HeartPulse } from 'lucide-react';

interface IntegrationStepProps {
    isComplete: boolean;
    onFinish: () => void;
    onBack: () => void;
    accessToken: string | null;
    apiOnboardingStatus: boolean | null;
}

export default function IntegrationStep({
    isComplete,
    onFinish,
    onBack,
    accessToken,
    apiOnboardingStatus
}: IntegrationStepProps) {
    return (
        <AccordionItem value="integration" className="border-0 mt-4">
            <div onClick={() => {}}>
                <div className="flex flex-row justify-between w-full">
                    <span className="text-md font-bold">Activate Beacon</span>
                    {isComplete && (
                        <Badge variant="default" className="bg-success hover:bg-success">
                            Complete
                        </Badge>
                    )}
                </div>
            </div>
            <AccordionContent className="py-4 flex flex-col gap-4">
                <p>Confirm your integration by sending a feature flag request:</p>
                <HttpRequest
                    apiKey={accessToken as string}
                    contextValues={{
                        app_name: 'Beacon Onboarding',
                        environment: 'production',
                    }}
                    featureFlagName="test-flag"
                    fullWidth={true}
                />
                <div className="flex flex-row justify-between">
                    <Button
                        variant="secondary"
                        onClick={onBack}
                        type="button"
                    >
                        Back
                    </Button>
                    <Button
                        variant="default"
                        disabled={apiOnboardingStatus !== false}
                        className="bg-success hover:bg-success/80"
                        onClick={onFinish}
                        data-dusk="finish-onboarding"
                    >
                        {apiOnboardingStatus && <HeartPulse className="animate-pulse" />}
                        {apiOnboardingStatus === false ? 'Finish' : 'Waiting…'}
                    </Button>
                </div>
            </AccordionContent>
        </AccordionItem>
    );
}
