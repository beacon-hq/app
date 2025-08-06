import CopyToClipboard from '@/Components/CopyToClipboard';
import { AccordionContent, AccordionItem } from '@/Components/ui/accordion';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';

interface InstallStepProps {
    isComplete: boolean;
    onComplete: () => void;
    onNext: () => void;
}

export default function InstallStep({ isComplete, onComplete, onNext }: InstallStepProps) {
    const handleNext = () => {
        onComplete();
        onNext();
    };

    return (
        <AccordionItem value="install" className="border-0 mt-4">
            <div onClick={() => {}}>
                <div className="flex flex-row justify-between w-full">
                    <span className="text-md font-bold">Install Beacon</span>
                    {isComplete && (
                        <Badge
                            variant="default"
                            className="bg-success hover:bg-success"
                            data-dusk="done-install"
                        >
                            Done
                        </Badge>
                    )}
                </div>
            </div>
            <AccordionContent className="py-4 flex flex-col gap-4 w-full">
                <p>Install the Beacon Pennant Driver package to get started:</p>
                <div className="flex flex-row justify-between border-1 border-muted-foreground rounded-md p-2">
                    <code>
                        <pre className="whitespace-pre-wrap font-mono">
                            composer require beacon-hq/pennant-driver
                        </pre>
                    </code>
                    <CopyToClipboard text="composer require beacon-hq/pennant-driver" />
                </div>
                <div className="flex flex-row justify-end">
                    <Button onClick={handleNext} data-dusk="next-config">
                        Next
                    </Button>
                </div>
            </AccordionContent>
        </AccordionItem>
    );
}
