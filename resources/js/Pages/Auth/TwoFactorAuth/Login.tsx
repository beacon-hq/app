import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@/Components/ui/input-otp';
import { Label } from '@/Components/ui/label';
import Guest from '@/Layouts/GuestLayout';
import { FormErrors } from '@/types/global';
import { Head, useForm } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import React, { useState } from 'react';

export default function Index({ errors }: { errors: FormErrors }) {
    const { data, setData, post } = useForm<{ code: string | null; recovery_code: string | null }>({
        code: null,
        recovery_code: null,
    });

    const [useRecoveryCode, setUseRecoveryCode] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route('two-factor.login.store'));
    };

    return (
        <Guest>
            <Head title="Enable Two-Factor Authentication" />
            <form onSubmit={handleSubmit}>
                <div className="py-6">
                    <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                        <div className="prose px-6 lg:px-0">
                            <h1 className="text-center">Verification</h1>
                            <Card className="w-96 mx-auto p-4">
                                <CardContent className="flex flex-col justify-center">
                                    <div className="not-prose flex flex-col gap-4 mt-4 w-full">
                                        <div>
                                            <div className="flex flex-col gap-4">
                                                {!useRecoveryCode && (
                                                    <>
                                                        <Label htmlFor="otp">
                                                            Enter Authentication Code to Continue
                                                        </Label>
                                                        <InputOTP
                                                            maxLength={6}
                                                            id="otp"
                                                            pattern={REGEXP_ONLY_DIGITS}
                                                            value={data.code ?? ''}
                                                            onChange={(code) => setData('code', code)}
                                                            error={errors.code ?? undefined}
                                                        >
                                                            <InputOTPGroup className="flex flex-row w-full">
                                                                <InputOTPSlot className="w-1/6" index={0} />
                                                                <InputOTPSlot className="w-1/6" index={1} />
                                                                <InputOTPSlot className="w-1/6" index={2} />
                                                                <InputOTPSlot className="w-1/6" index={3} />
                                                                <InputOTPSlot className="w-1/6" index={4} />
                                                                <InputOTPSlot className="w-1/6" index={5} />
                                                            </InputOTPGroup>
                                                        </InputOTP>
                                                    </>
                                                )}
                                                {useRecoveryCode && (
                                                    <>
                                                        <Label htmlFor="recovery_code" className="">
                                                            Enter Recovery Code
                                                        </Label>
                                                        <Input
                                                            id="recovery_code"
                                                            value={data.recovery_code ?? ''}
                                                            onChange={(e) => setData('recovery_code', e.target.value)}
                                                        />
                                                        <p className="text-xs">Recovery codes can only be used once.</p>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                        <Button type="submit">Verify</Button>
                                        <Button
                                            variant="link"
                                            type="button"
                                            className="text-sm cursor-pointer"
                                            onClick={() => setUseRecoveryCode(!useRecoveryCode)}
                                        >
                                            {!useRecoveryCode
                                                ? 'Need to use a recovery code?'
                                                : 'Use authentication code.'}
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </form>
        </Guest>
    );
}
