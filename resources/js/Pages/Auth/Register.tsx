import { Invite, Product } from '@/Application';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout';
import InviteAlert from '@/Pages/Auth/Components/InviteAlert';
import PlanAlert from '@/Pages/Auth/Components/PlanAlert';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Register({ invite, plan }: { invite: Invite | false | null; plan: Product | null }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        plan: plan?.id ?? null,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />
            <Card className="mt-12 p-4 pt-8" data-dusk="card-register">
                <CardContent>
                    <InviteAlert invite={invite} variant="register" />
                    <PlanAlert plan={plan} />
                    <form onSubmit={submit}>
                        <div className="grid grid-cols-2 space-x-2">
                            <div>
                                <Label htmlFor="first_name" className="text-primary">
                                    First Name
                                </Label>

                                <Input
                                    id="first_name"
                                    name="first_name"
                                    value={data.first_name}
                                    className="mt-1 block w-full"
                                    autoComplete="given-name"
                                    autoFocus
                                    onChange={(e) => setData('first_name', e.target.value)}
                                    aria-required
                                    data-dusk="input-register-first-name"
                                />

                                <InputError message={errors.first_name} className="mt-2" />
                            </div>

                            <div>
                                <Label htmlFor="last_name" className="text-primary">
                                    Last Name
                                </Label>

                                <Input
                                    id="last_name"
                                    name="last_name"
                                    value={data.last_name}
                                    className="mt-1 block w-full"
                                    autoComplete="family-name"
                                    onChange={(e) => setData('last_name', e.target.value)}
                                    aria-required
                                    data-dusk="input-register-last-name"
                                />

                                <InputError message={errors.last_name} className="mt-2" />
                            </div>
                        </div>

                        <div className="mt-4">
                            <Label htmlFor="email" className="text-primary">
                                Email
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="mt-1 block w-full"
                                autoComplete="work email"
                                onChange={(e) => setData('email', e.target.value)}
                                aria-required
                                data-dusk="input-register-email"
                            />

                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label htmlFor="password" className="text-primary">
                                Password
                            </Label>

                            <Input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) => setData('password', e.target.value)}
                                aria-required
                                data-dusk="input-register-password"
                            />

                            <InputError message={errors.password} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label htmlFor="password_confirmation" className="text-primary">
                                Confirm Password
                            </Label>

                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                aria-required
                                data-dusk="input-register-password-confirmation"
                            />

                            <InputError message={errors.password_confirmation} className="mt-2" />
                        </div>

                        <div className="mt-4 flex items-center justify-end">
                            <Link
                                href={route('login')}
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                                data-dusk="link-already-registered"
                            >
                                Already registered?
                            </Link>

                            <PrimaryButton className="ms-4" disabled={processing} data-dusk="button-register-submit">
                                Register
                            </PrimaryButton>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
