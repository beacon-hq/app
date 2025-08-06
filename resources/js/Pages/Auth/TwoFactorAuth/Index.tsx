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
                        <div className="px-6 lg:px-0">
                            <h1 className="text-center text-primary text-3xl mb-4">Enable Two-Factor Authentication</h1>
                            <Card className="w-full md:w-3/4 xl:w-1/2 mx-auto p-4">
                                <CardHeader className="pb-0">
                                    <Alert variant="warning" className="">
                                        <AlertCircle className="-translate-y-1.5" />
                                        <AlertDescription className="mt-1">
                                            Two-factor authentication is required.
                                        </AlertDescription>
                                    </Alert>
                                    <p className="mb-0 mt-4">
                                        Scan the following QR code using your phone's authenticator application (e.g.
                                        1Password, Google Authenticator, Bitwarden, Apple Passwords, etc.).
                                    </p>
                                </CardHeader>
                                <CardContent className="flex flex-col items-center pt-4">
                                    <img
                                        className="w-1/2 mb-0 mx-auto aspect-square"
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
                                                <InputOTPGroup className="border-primary">
                                                    <InputOTPSlot className="border-primary/60" index={0} />
                                                    <InputOTPSlot className="border-primary/60" index={1} />
                                                    <InputOTPSlot className="border-primary/60" index={2} />
                                                    <InputOTPSlot className="border-primary/60" index={3} />
                                                    <InputOTPSlot className="border-primary/60" index={4} />
                                                    <InputOTPSlot className="border-primary/60" index={5} />
                                                </InputOTPGroup>
                                            </InputOTP>
                                        </div>
                                        <Button type="submit">Confirm</Button>
                                    </div>
                                    <Accordion type="single" className="w-full mt-12" collapsible>
                                        <AccordionItem value="item-1">
                                            <AccordionTrigger className="mt-0 text-primary">
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
