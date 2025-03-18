import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import ManageTwoFactorForm from '@/Pages/Profile/Partials/ManageTwoFactorForm';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

export default function Edit({ mustVerifyEmail, status }: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <Authenticated header="Profile">
            <Head title="Profile" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <UpdatePasswordForm />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <ManageTwoFactorForm />
                    </div>

                    <div className="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8 dark:bg-gray-800 w-full">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
