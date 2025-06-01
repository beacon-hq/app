import { OrganizationCollection, Permission, Team, TeamCollection, User } from '@/Application';

export interface AuthProp {
    auth?: {
        user: User;
        currentTeam: Team;
        permissions: Permission[];
    };
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T &
    AuthProp & {
        teams: TeamCollection;
        organizations: OrganizationCollection;
        ziggy: Config & { location: string };
        features: {
            'pricing.enabled': boolean;
        };
    };
