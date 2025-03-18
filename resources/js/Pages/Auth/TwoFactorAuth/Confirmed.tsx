import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import Guest from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';
import React from 'react';

export default function Confirmed({ recoveryCodes }: { recoveryCodes: string[] }) {
    return (
        <Guest>
            <Head title="Two-Factor Setup Completed" />
            <div className="py-6">
                <div className="mx-auto w-full space-y-6 sm:px-6 lg:px-8">
                    <div className="prose px-6 lg:px-0">
                        <h1 className="text-center">Two Factor Enabled</h1>
                        <Card className="w-96 mx-auto">
                            <CardContent className="flex flex-col justify-center">
                                <h2 className="mt-4">Save Your Recovery Codes</h2>
                                <p className="mb-4">
                                    If you lose access to your authenticator app, you can login using one of your
                                    recovery codes. Each code can only be used once.{' '}
                                    <strong>Store them somewhere safe!</strong>
                                </p>
                                <div className="not-prose flex flex-col gap-4 w-full">
                                    <p className="bg-secondary text-center p-4">
                                        {recoveryCodes.map((code, index) => (
                                            <span className="inline-block text-nowrap" key={code}>
                                                {code}
                                            </span>
                                        ))}
                                    </p>
                                </div>
                                <div className="text-center mt-4">
                                    <Link href={route('dashboard')}>
                                        <Button type="button" className="cursor-pointer">
                                            Continue
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Guest>
    );
}
