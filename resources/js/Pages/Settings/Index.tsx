import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, KeyRound, Network, UserCircle, Users } from 'lucide-react';
import React from 'react';

export default function Index() {
    return (
        <AuthenticatedLayout breadcrumbs={[{ name: 'Settings', icon: 'Settings' }]}>
            <Head title="Settings" />
            <div className="py-10">
                <div className="flex flex-row flex-wrap gap-2 w-full items-center mx-auto">
                    <Card className="relative grow flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <Network className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('organizations.index')}>
                                        <span className="absolute inset-0"></span>
                                        Manage Organizations
                                    </Link>
                                </CardTitle>
                                <CardDescription>
                                    <p>Manage Organization settings.</p>
                                </CardDescription>
                            </CardContent>
                        </div>
                        <div>
                            <ChevronRight className="mr-4" />
                        </div>
                    </Card>
                    <Card className="relative grow flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <UserCircle className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('users.index')}>
                                        <span className="absolute inset-0"></span>
                                        Users
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
                    <Card className="relative grow flex flex-row items-center justify-between w-1/3 cursor-pointer">
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
                    <Card className="relative grow flex flex-row items-center justify-between w-1/3 cursor-pointer">
                        <div>
                            <CardHeader>
                                <KeyRound className="size-12" />
                            </CardHeader>
                            <CardContent>
                                <CardTitle>
                                    <Link href={route('access-tokens.index')}>
                                        <span className="absolute inset-0"></span>Access Tokens
                                    </Link>
                                </CardTitle>
                                <CardDescription>
                                    <p>Create and manage Access Tokens for Laravel Pennant.</p>
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
