import CopyToClipboard from '@/Components/CopyToClipboard';
import { AccordionContent, AccordionItem } from '@/Components/ui/accordion';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Light as SyntaxHighlighter } from 'react-syntax-highlighter';
import docco from 'react-syntax-highlighter/dist/esm/styles/hljs/docco';

interface ConfigStepProps {
    isComplete: boolean;
    onComplete: () => void;
    onNext: () => void;
    onBack: () => void;
    accessToken: string | null;
}

export default function ConfigStep({ isComplete, onComplete, onNext, onBack, accessToken }: ConfigStepProps) {
    const handleNext = () => {
        onComplete();
        onNext();
    };

    return (
        <AccordionItem value="config" className="border-0 mt-4">
            <div onClick={() => {}}>
                <div className="flex flex-row justify-between w-full">
                    <span className="text-md font-bold">Edit Configuration</span>
                    {isComplete && (
                        <Badge
                            variant="default"
                            className="bg-success hover:bg-success"
                            data-dusk="done-config"
                        >
                            Done
                        </Badge>
                    )}
                </div>
            </div>
            <AccordionContent className="py-4 flex flex-col gap-4">
                <p>Add your Beacon access token to your applications `.env` file:</p>
                <div className="flex flex-row justify-between border-1 border-muted-foreground rounded-md p-2 w-full">
                    <SyntaxHighlighter
                        language="dotenv"
                        style={docco}
                        customStyle={{
                            margin: 0,
                            padding: '0.5rem',
                            background: 'transparent',
                            fontSize: '0.875rem',
                            width: '400px',
                            overflow: 'auto',
                        }}
                    >
                        {`PENNANT_STORE=beacon\nBEACON_ACCESS_TOKEN="${accessToken}"`}
                    </SyntaxHighlighter>
                    <CopyToClipboard
                        text={`PENNANT_STORE=beacon\nBEACON_ACCESS_TOKEN="${accessToken}"`}
                    />
                </div>
                <div className="flex flex-row justify-between">
                    <Button
                        variant="secondary"
                        onClick={onBack}
                        type="button"
                    >
                        Back
                    </Button>
                    <div className="flex flex-row justify-end">
                        <Button
                            onClick={handleNext}
                            data-dusk="next-integration"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </AccordionContent>
        </AccordionItem>
    );
}
