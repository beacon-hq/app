import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, KeyRound, UserCircle, Users } from 'lucide-react';
import React from 'react';

export default function Index() {
    return (
        <AuthenticatedLayout breadcrumbs={[{ name: 'Settings', icon: 'Settings' }]}>
            <Head title="Settings" />
            <div className="py-10">
                <div className="flex flex-row gap-2 justify-between w-full items-stretch">
                    <Card className="relative flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <UserCircle className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('settings.api.index')}>
                                        <span className="absolute inset-0"></span>
                                        Team Members
                                    </Link>
                                </CardTitle>
                                <CardDescription>
                                    <p>Invite team members to collaborate and manage permissions.</p>
                                </CardDescription>
                            </CardContent>
                        </div>
                        <div>
                            <ChevronRight className="mr-4" />
                        </div>
                    </Card>
                    <Card className="relative flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <Users className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('teams.index')}>
                                        <span className="absolute inset-0"></span>
                                        Teams
                                    </Link>
                                </CardTitle>
                                <CardDescription>
                                    <p>Manage teams and team membership.</p>
                                </CardDescription>
                            </CardContent>
                        </div>
                        <div>
                            <ChevronRight className="mr-4" />
                        </div>
                    </Card>
                    <Card className="relative flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <KeyRound className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('settings.api.index')}>
                                        <span className="absolute inset-0"></span>API Keys
                                    </Link>
                                </CardTitle>
                                <CardDescription>
                                    <p>Create and manage API keys for Laravel Pennant.</p>
                                </CardDescription>
                            </CardContent>
                        </div>
                        <div>
                            <ChevronRight className="mr-4" />
                        </div>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
