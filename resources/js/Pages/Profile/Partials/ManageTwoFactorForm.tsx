import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Skeleton } from '@/Components/ui/skeleton';
import { cn } from '@/lib/utils';
import axios from 'axios';
import React, { useState } from 'react';

export default function ManageTwoFactorForm({ className = '' }: { className?: string }) {
    const [errors, setErrors] = useState<any>({});
    const [password, setPassword] = useState<string>('');
    const [qrCode, setQrCode] = useState<string | null>(null);
    const [secretKey, setSecretKey] = useState<string | null>(null);
    const [loadingSecrets, setLoadingSecrets] = useState<boolean>(false);

    const retrieveOTP = async function () {
        setLoadingSecrets(true);
        await axios.post(route('password.confirm.store'), { password: password }).catch(function (result) {
            setErrors(result.response.data.errors);
            setPassword('');
            setQrCode(null);
            setSecretKey(null);
            setLoadingSecrets(false);
        });

        console.log(errors);
        if (errors.password === undefined) {
            await Promise.all([
                axios
                    .get(route('two-factor.qr-code'))
                    .then((result) => setQrCode(result.data.svg))
                    .catch((reason) => console.log(reason)),
                axios
                    .get(route('two-factor.secret-key'))
                    .then((result) => setSecretKey(result.data.secretKey))
                    .catch((reason) => console.log(reason)),
            ]).then(() => setLoadingSecrets(false));
        }
    };
    return (
        <section className={cn('flex flex-row gap-8', className)}>
            <header className="w-1/4">
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Authentication Code</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Need to setup a new device with two factor authentication?
                </p>
            </header>
            <div className="mt-6 space-y-6 w-3/4 grow">
                <div className="flex flex-col items-start justify-start w-full">
                    {(qrCode === null || secretKey === null) && !loadingSecrets && (
                        <div className="flex flex-col gap-4">
                            <div>
                                <Label htmlFor="2fa_password">
                                    Enter your password to reveal the QR code and secret key
                                </Label>
                                <Input
                                    id="2fa_password"
                                    name="password"
                                    type="password"
                                    value={password}
                                    onChange={(e) => {
                                        setPassword(e.target.value);
                                    }}
                                    error={errors.password}
                                />
                            </div>
                            <Button type="button" className="w-fit" onClick={retrieveOTP} disabled={loadingSecrets}>
                                Reveal
                            </Button>
                        </div>
                    )}
                    {((qrCode && secretKey) || loadingSecrets) && (
                        <div className="flex flex-row gap-12 w-full justify-items-stretch">
                            <div className="flex flex-col items-center w-1/2">
                                {loadingSecrets && (
                                    <>
                                        <Skeleton className="w-full aspect-square" />
                                        <Skeleton className="w-full h-20" />
                                    </>
                                )}
                                {!loadingSecrets && (
                                    <>
                                        <img
                                            className="w-2/3 mb-0 mx-auto aspect-square"
                                            src={`data:image/svg+xml;utf8,${encodeURIComponent(qrCode as string)}`}
                                            alt="Two-Factor Auth QR Code"
                                        />
                                        <Accordion type="single" className="w-full" collapsible>
                                            <AccordionItem value="item-1">
                                                <AccordionTrigger className="mt-0">
                                                    Can't scan the QR code?
                                                </AccordionTrigger>
                                                <AccordionContent>
                                                    Enter this code instead:
                                                    <p className="text-xl text-center bg-secondary p-12">{secretKey}</p>
                                                </AccordionContent>
                                            </AccordionItem>
                                        </Accordion>
                                    </>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
