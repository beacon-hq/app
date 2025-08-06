import { useState } from 'react';

export function useOnboardingSteps() {
    const [currentStep, setCurrentStep] = useState('install');
    const [completedSteps, setCompletedSteps] = useState<string[]>([]);

    const markStepComplete = (step: string) => {
        if (!completedSteps.includes(step)) {
            setCompletedSteps([...completedSteps, step]);
        }
    };

    const goToStep = (step: string) => {
        setCurrentStep(step);
    };

    const goToNextStep = (currentStep: string, nextStep: string) => {
        markStepComplete(currentStep);
        setCurrentStep(nextStep);
    };

    const isStepComplete = (step: string) => {
        return completedSteps.includes(step);
    };

    return {
        currentStep,
        completedSteps,
        setCurrentStep,
        setCompletedSteps,
        markStepComplete,
        goToStep,
        goToNextStep,
        isStepComplete,
    };
}
