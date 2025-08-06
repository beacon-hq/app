import { useOnboardingStatus } from './hooks/useOnboardingStatus';
import { useOnboardingSteps } from './hooks/useOnboardingSteps';
import ConfigStep from './steps/ConfigStep';
import InstallStep from './steps/InstallStep';
import IntegrationStep from './steps/IntegrationStep';
import { Accordion } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

interface OnboardingDialogProps {
    isOpen: boolean;
    accessToken: string | null;
}

async function completeOnboarding() {
    await axios.post(route('api.onboarding.complete'));
}

export default function OnboardingDialog({ isOpen, accessToken }: OnboardingDialogProps) {
    const [showOnboarding, setShowOnboarding] = useState(isOpen);
    const { apiOnboardingStatus } = useOnboardingStatus(showOnboarding);
    const { currentStep, setCurrentStep, isStepComplete, goToNextStep } = useOnboardingSteps();

    const handleClose = async (completed: boolean = false) => {
        setShowOnboarding(false);
        if (completed) {
            await completeOnboarding();
            router.reload();
        }
    };

    const handleSkip = async () => {
        await handleClose(true);
    };

    const handleFinish = async () => {
        await handleClose(true);
    };

    return (
        <Dialog defaultOpen open={showOnboarding} onOpenChange={setShowOnboarding}>
            <DialogContent className="w-full" data-dusk="onboarding-dialog">
                <DialogHeader>
                    <DialogTitle>
                        <h2 className="text-primary">Get Started</h2>
                    </DialogTitle>
                    <DialogDescription className="w-full">
                        <Accordion type="single" value={currentStep} onValueChange={setCurrentStep}>
                            <InstallStep
                                isComplete={isStepComplete('install')}
                                onComplete={() => {}}
                                onNext={() => goToNextStep('install', 'config')}
                            />
                            <ConfigStep
                                isComplete={isStepComplete('config')}
                                onComplete={() => {}}
                                onNext={() => goToNextStep('config', 'integration')}
                                onBack={() => setCurrentStep('install')}
                                accessToken={accessToken}
                            />
                            <IntegrationStep
                                isComplete={isStepComplete('integration')}
                                onFinish={handleFinish}
                                onBack={() => setCurrentStep('config')}
                                accessToken={accessToken}
                                apiOnboardingStatus={apiOnboardingStatus}
                            />
                        </Accordion>
                        <Separator className="my-4" />
                        <div className="w-full text-right">
                            <Button
                                variant="link"
                                onClick={handleSkip}
                                className="text-xs text-primary/60"
                                data-dusk="skip-onboarding"
                            >
                                Skip
                            </Button>
                        </div>
                    </DialogDescription>
                </DialogHeader>
            </DialogContent>
        </Dialog>
    );
}
