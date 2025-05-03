import { Invite } from '@/Application';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { AlertCircle, PartyPopper } from 'lucide-react';

const InviteAlert = ({ invite, variant }: { invite: Invite | false | null; variant: 'login' | 'register' }) => {
    return (
        <>
            {invite !== null && invite !== false && (
                <Alert variant="info" className="mb-6">
                    <PartyPopper />
                    <AlertTitle>Invitation Accepted</AlertTitle>
                    {variant === 'register' && (
                        <AlertDescription>Register below to join the "{invite.team?.name}" team.</AlertDescription>
                    )}
                    {variant === 'login' && (
                        <AlertDescription>Login below to join the "{invite.team?.name}" team.</AlertDescription>
                    )}
                </Alert>
            )}

            {invite === false && (
                <Alert variant="info" className="mb-6">
                    <AlertCircle />
                    <AlertTitle>Invalid Invitation</AlertTitle>
                    <AlertDescription>
                        The invitation link you used is invalid. Please contact a team member for a new invitation, or
                        sign up below for your own account.
                    </AlertDescription>
                </Alert>
            )}
        </>
    );
};

export default InviteAlert;
