import InputError from '@/Components/InputError';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { cn } from '@/lib/utils';
import { Theme, useTheme } from '@/theme-provider';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';
import { FormEventHandler, useRef } from 'react';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}) {
    const user = usePage().props.auth.user;

    const file = useRef<HTMLInputElement>(null);

    const { theme, setTheme } = useTheme();

    const { data, setData, errors, processing } = useForm<{
        first_name: string;
        last_name: string;
        email: string;
        avatar: File | string | null;
        theme: string;
    }>({
        first_name: user.first_name ?? '',
        last_name: user.last_name ?? '',
        email: user.email ?? '',
        avatar: user.avatar ?? null,
        theme: theme,
    });

    const useGravatar = () => {
        setData('avatar', null);
        if (file.current) {
            file.current.value = '';
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        router.post(route('user-profile-information.update'), {
            ...data,
            _method: 'put',
        });

        if (file.current) {
            file.current.value = '';
        }
    };

    // @ts-ignore
    // @ts-ignore
    // @ts-ignore
    return (
        <section className={cn('flex flex-row gap-8', className)}>
            <header className="w-1/4">
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Settings</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your account's profile settings.</p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6 w-3/4 grow">
                <div className="flex gap-8 items-center">
                    <Avatar className="rounded-lg w-1/4 h-1/4">
                        <AvatarImage
                            src={
                                typeof data.avatar === 'string'
                                    ? data.avatar
                                    : data.avatar
                                      ? URL.createObjectURL(data.avatar)
                                      : (user.gravatar ?? undefined)
                            }
                            alt={user.name ?? ''}
                        />
                        {!data.avatar && (
                            <div className="rounded-b-lg bg-secondary text-primary absolute bottom-0 w-full text-center text-xs">
                                Gravatar
                            </div>
                        )}
                        <AvatarFallback className="aspect-square text-8xl rounded-lg">
                            {user.first_name?.charAt(0)}
                            {user.last_name?.charAt(0)}
                        </AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col gap-2">
                        <Input
                            type="file"
                            ref={file}
                            className="block w-full h-12 border-0 p-0 shadow-none text-lg text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-secondary file:disabled:opacity-50 file:disabled:pointer-events-none text-primary"
                            onChange={(e) =>
                                setData(
                                    'avatar',
                                    // @ts-ignore
                                    (e.target?.files ?? []).length > 0 ? (e.target.files[0] as File) : null,
                                )
                            }
                        />
                        <p className="text-primary/50 mb-2 mx-auto">or</p>
                        <Button onClick={() => useGravatar()} disabled={data.avatar === null}>
                            Use Gravatar
                        </Button>
                    </div>
                </div>
                <div className="flex justify-between gap-4">
                    <div className="w-1/2">
                        <Label htmlFor="first_name">First Name</Label>

                        <Input
                            id="first_name"
                            className="mt-1 block w-full"
                            value={data.first_name}
                            onChange={(e) => setData('first_name', e.target.value)}
                            aria-required
                            autoFocus
                            autoComplete="given-name"
                        />

                        <InputError message={errors?.first_name} />
                    </div>

                    <div className="w-1/2">
                        <Label htmlFor="last_name">Last Name</Label>

                        <Input
                            id="last_name"
                            className="mt-1 block w-full"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                            aria-required
                            autoComplete="family-name"
                        />

                        <InputError message={errors?.last_name} />
                    </div>
                </div>

                <div>
                    <Label htmlFor="email">Email</Label>

                    <Input
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        aria-required
                        autoComplete="username"
                    />

                    <InputError message={errors?.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="mt-2 text-sm text-gray-800 dark:text-gray-200">
                            Your email address is unverified.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                A new verification link has been sent to your email address.
                            </div>
                        )}
                    </div>
                )}

                <div>
                    <Label>Theme Preference</Label>
                    <Tabs
                        value={data.theme}
                        onValueChange={function (value) {
                            setData('theme', value);
                            setTheme(value as Theme);
                        }}
                    >
                        <TabsList>
                            <TabsTrigger value="light">
                                <Sun
                                    className={cn('mr-2', {
                                        'stroke-yellow-400 transition-color duration-1000': theme === 'light',
                                    })}
                                />
                                Light
                            </TabsTrigger>
                            <TabsTrigger value="dark">
                                <Moon
                                    className={cn('mr-2', {
                                        'stroke-sky-400 transition-color duration-1000': theme === 'dark',
                                    })}
                                />
                                Dark
                            </TabsTrigger>
                            <TabsTrigger value="system">
                                <Monitor
                                    className={cn('mr-2', {
                                        'stroke-purple-400 transition-color duration-1000': theme === 'system',
                                    })}
                                />
                                System
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>

                <div className="flex items-center gap-4">
                    <Button disabled={processing}>Save</Button>
                </div>
            </form>
        </section>
    );
}
