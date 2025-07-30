import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
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
                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full" data-dusk="section-profile-information">
                        <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full" data-dusk="section-update-password">
                        <UpdatePasswordForm />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full" data-dusk="section-two-factor">
                        <ManageTwoFactorForm />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full" data-dusk="section-delete-user">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
