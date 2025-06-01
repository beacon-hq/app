import { Product } from '@/Application';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { CircleCheck } from 'lucide-react';

const PlanAlert = ({ plan }: { plan: Product | null }) => {
    return (
        <>
            {plan !== null && (
                <Alert className="mb-6 px-4">
                    <AlertTitle className="flex flex-row items-center gap-2 justify-start text-lg mb-0">
                        <CircleCheck />
                        Selected Plan: {plan.name}
                    </AlertTitle>
                    <AlertDescription className="text-xs pl-8">
                        <p>{plan.base_price}/month. You can cancel or change your plan at any time.</p>
                    </AlertDescription>
                </Alert>
            )}
        </>
    );
};

export default PlanAlert;
