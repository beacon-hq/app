import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@/Components/ui/input-otp';
import { Label } from '@/Components/ui/label';
import Guest from '@/Layouts/GuestLayout';
import { PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { AlertCircle } from 'lucide-react';
import React from 'react';

export default function Index({ auth, qrCode, secret }: PageProps & { qrCode: string; secret: string }) {
    const { data, setData, post } = useForm<{ code: string }>({
        code: '',
    });

    const handleSubmit = (e: any) => {
        e.preventDefault();
        post(route('two-factor.confirm'));
    };

    return (
        <Guest>
            <Head title="Enable Two-Factor Authentication" />
            <form onSubmit={handleSubmit}>
                <div className="py-6">
                    <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                        <div className="prose px-6 lg:px-0">
                            <h1 className="text-center">Enable Two-Factor Authentication</h1>
                            <Card className="w-96 mx-auto p-4">
                                <CardHeader className="pb-0">
                                    <Alert variant="warning">
                                        <AlertCircle />
                                        <AlertDescription>Two-factor authentication is required.</AlertDescription>
                                    </Alert>
                                    <p className="mb-0">
                                        Scan the following QR code using your phone's authenticator application (e.g.
                                        1Password, Google Authenticator, Bitwarden, Apple Passwords, etc.).
                                    </p>
                                </CardHeader>
                                <CardContent className="flex flex-col items-center">
                                    <img
                                        className="w-2/3 mb-0 mx-auto aspect-square"
                                        src={`data:image/svg+xml;utf8,${encodeURIComponent(qrCode)}`}
                                        alt="Two-Factor Auth QR Code"
                                    />
                                    <div className="not-prose flex flex-col gap-4 mt-4">
                                        <div>
                                            <Label htmlFor="otp">Enter Code to Confirm</Label>
                                            <InputOTP
                                                maxLength={6}
                                                id="otp"
                                                pattern={REGEXP_ONLY_DIGITS}
                                                value={data.code}
                                                onChange={(code) => setData('code', code)}
                                                autoFocus={true}
                                            >
                                                <InputOTPGroup>
                                                    <InputOTPSlot index={0} />
                                                    <InputOTPSlot index={1} />
                                                    <InputOTPSlot index={2} />
                                                    <InputOTPSlot index={3} />
                                                    <InputOTPSlot index={4} />
                                                    <InputOTPSlot index={5} />
                                                </InputOTPGroup>
                                            </InputOTP>
                                        </div>
                                        <Button type="submit">Confirm</Button>
                                    </div>
                                    <Accordion type="single" className="w-full" collapsible>
                                        <AccordionItem value="item-1">
                                            <AccordionTrigger className="mt-0">
                                                Can't scan the QR code?
                                            </AccordionTrigger>
                                            <AccordionContent>
                                                Enter this code instead:
                                                <p className="text-xl text-center bg-secondary p-12">{secret}</p>
                                            </AccordionContent>
                                        </AccordionItem>
                                    </Accordion>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </form>
        </Guest>
    );
}
