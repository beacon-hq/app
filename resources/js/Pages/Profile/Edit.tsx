import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { Card, CardContent } from '@/components/ui/card';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import ManageTwoFactorForm from '@/Pages/Profile/Partials/ManageTwoFactorForm';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

export default function Edit({ mustVerifyEmail, status }: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <Authenticated breadcrumbs={[{ name: 'My Account', icon: 'CircleUser' }]}>
            <Head title="My Account" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <Card data-test="section-profile-information">
                        <CardContent>
                            <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                        </CardContent>
                    </Card>

                    <Card data-test="section-update-password">
                        <CardContent>
                            <UpdatePasswordForm />
                        </CardContent>
                    </Card>

                    <Card data-test="section-two-factor">
                        <CardContent>
                            <ManageTwoFactorForm />
                        </CardContent>
                    </Card>

                    <Card data-test="section-delete-user">
                        <CardContent>
                            <DeleteUserForm />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </Authenticated>
    );
}
