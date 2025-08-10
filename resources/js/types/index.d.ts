import { OrganizationCollection, Permission, Team, TeamCollection, User } from '@/Application';

type Auth = {
    user: User;
    currentTeam: Team;
    permissions: Permission[];
};

export interface AuthProp {
    auth?: Auth;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T &
    AuthProp & {
        teams: TeamCollection;
        organizations: OrganizationCollection;
        ziggy: Config & { location: string };
        features: {
            'pricing.enabled': boolean;
        };
        alert?: {
            message: string;
            status?: 'success' | 'error' | 'info' | 'warning';
        } | null;
        docsUrl: string;
        status?: {
            data: {
                status: string;
                message: string;
            };
            url: string;
        } | null;
    };
