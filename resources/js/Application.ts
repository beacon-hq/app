export type AccessToken = {
    id?: number;
    name?: string;
    token?: string;
    last_used_at: string | null;
    created_at: string | null;
};
export type AccessTokenCollection = AccessToken[];
export type Application = {
    id?: string;
    name?: string;
    display_name?: string;
    description: string | null;
    environments?: EnvironmentCollection;
    color: Color | string;
    last_seen_at: string | null;
};
export type ApplicationCollection = Application[];
export enum Boolean {
    'AND' = 'AND',
    'OR' = 'OR',
    'NOT' = 'AND NOT',
    'XOR' = 'XOR',
}
export enum Color {
    'RED' = 'red',
    'ORANGE' = 'orange',
    'YELLOW' = 'yellow',
    'LIME' = 'lime',
    'GREEN' = 'green',
    'EMERALD' = 'emerald',
    'CYAN' = 'cyan',
    'SKY' = 'sky',
    'BLUE' = 'blue',
    'INDIGO' = 'indigo',
    'PURPLE' = 'purple',
    'FUCHSIA' = 'fuchsia',
}
export type Environment = {
    id?: string;
    name?: string;
    description?: string | null;
    color: Color | string;
    last_seen_at: string | null;
};
export type EnvironmentCollection = Environment[];
export type FeatureFlag = {
    id?: string;
    name?: string;
    description?: string;
    last_seen_at: string | null;
    feature_type?: FeatureType;
    tags?: TagCollection;
    statuses?: FeatureFlagStatusCollection;
    created_at: string | null;
    updated_at: string | null;
    status: boolean;
};
export type FeatureFlagCollection = FeatureFlag[];
export type FeatureFlagContext = {
    scope_type: string | null;
    scope?: Array<any>;
    app_name: string;
    environment: string;
    session_id?: string;
    ip?: string;
    user_agent?: string;
    referrer?: string;
    url?: string;
    method?: string;
};
export type FeatureFlagResponse = {
    feature_flag: string;
    value: any;
    active: boolean;
};
export type FeatureFlagStatus = {
    id?: string;
    application: Application | null;
    environment: Environment | null;
    feature_flag: FeatureFlag | null;
    status: boolean;
    definition?: PolicyDefinitionCollection;
};
export type FeatureFlagStatusCollection = FeatureFlagStatus[];
export type FeatureType = {
    id?: string;
    name?: string;
    description?: string;
    icon?: string;
    color: Color | string;
    temporary: boolean;
    created_at: string | null;
    updated_at: string | null;
};
export type FeatureTypeCollection = FeatureType[];
export type Invite = {
    avatar?: string;
    id?: string;
    email: string;
    role: Role;
    team: Team;
    organization: Organization;
    user: User;
    expires_at: string;
};
export type InviteCollection = Invite[];
export type Organization = {
    id: string | null;
    owner: User | null;
    name: string | null;
};
export type OrganizationCollection = Organization[];
export enum Permission {
    'BILLING' = 'billing',
    'BILLING_CREATE' = 'billing.create',
    'BILLING_UPDATE' = 'billing.update',
    'BILLING_VIEW' = 'billing.view',
    'BILLING_DELETE' = 'billing.delete',
    'FEATURE_FLAGS' = 'feature-flags',
    'FEATURE_FLAGS_CREATE' = 'feature-flags.create',
    'FEATURE_FLAGS_UPDATE' = 'feature-flags.update',
    'FEATURE_FLAGS_VIEW' = 'feature-flags.view',
    'FEATURE_FLAGS_DELETE' = 'feature-flags.delete',
    'FEATURE_FLAG_STATUS' = 'feature-flag-status',
    'FEATURE_FLAG_STATUS_CREATE' = 'feature-flag-status.create',
    'FEATURE_FLAG_STATUS_UPDATE' = 'feature-flag-status.update',
    'FEATURE_FLAG_STATUS_VIEW' = 'feature-flag-status.view',
    'FEATURE_FLAG_STATUS_DELETE' = 'feature-flag-status.delete',
    'FEATURE_TYPES' = 'feature-types',
    'FEATURE_TYPES_CREATE' = 'feature-types.create',
    'FEATURE_TYPES_UPDATE' = 'feature-types.update',
    'FEATURE_TYPES_VIEW' = 'feature-types.view',
    'FEATURE_TYPES_DELETE' = 'feature-types.delete',
    'APPLICATIONS' = 'applications',
    'APPLICATIONS_CREATE' = 'applications.create',
    'APPLICATIONS_UPDATE' = 'applications.update',
    'APPLICATIONS_VIEW' = 'applications.view',
    'APPLICATIONS_DELETE' = 'applications.delete',
    'ENVIRONMENTS' = 'environments',
    'ENVIRONMENTS_CREATE' = 'environments.create',
    'ENVIRONMENTS_UPDATE' = 'environments.update',
    'ENVIRONMENTS_VIEW' = 'environments.view',
    'ENVIRONMENTS_DELETE' = 'environments.delete',
    'POLICIES' = 'policies',
    'POLICIES_CREATE' = 'policies.create',
    'POLICIES_UPDATE' = 'policies.update',
    'POLICIES_VIEW' = 'policies.view',
    'POLICIES_DELETE' = 'policies.delete',
    'TAGS' = 'tags',
    'TAGS_CREATE' = 'tags.create',
    'TAGS_UPDATE' = 'tags.update',
    'TAGS_VIEW' = 'tags.view',
    'TAGS_DELETE' = 'tags.delete',
    'USERS' = 'users',
    'USERS_CREATE' = 'users.create',
    'USERS_UPDATE' = 'users.update',
    'USERS_VIEW' = 'users.view',
    'USERS_DELETE' = 'users.delete',
    'TEAMS' = 'teams',
    'TEAMS_CREATE' = 'teams.create',
    'TEAMS_UPDATE' = 'teams.update',
    'TEAMS_VIEW' = 'teams.view',
    'TEAMS_DELETE' = 'teams.delete',
    'ACCESS_TOKENS' = 'access-tokens',
    'ACCESS_TOKENS_CREATE' = 'access-tokens.create',
    'ACCESS_TOKENS_UPDATE' = 'access-tokens.update',
    'ACCESS_TOKENS_VIEW' = 'access-tokens.view',
    'ACCESS_TOKENS_DELETE' = 'access-tokens.delete',
}
export type Policy = {
    id?: string;
    name?: string;
    definition?: PolicyDefinitionCollection;
    description: string | null;
    created_at: string | null;
    updated_at: string | null;
};
export type PolicyCollection = Policy[];
export type PolicyDefinition = {
    type: PolicyDefinitionType;
    subject: string;
    operator: PolicyDefinitionMatchOperator | null;
    values: string[];
};
export type PolicyDefinitionCollection = PolicyDefinition[];
export enum PolicyDefinitionMatchOperator {
    'EQUAL' = 'equals',
    'NOT_EQUAL' = 'does not equal',
    'CONTAINS_ALL' = 'contains exactly',
    'NOT_CONTAINS_ALL' = 'does not contains exactly',
    'CONTAINS_ANY' = 'contains any',
    'NOT_CONTAINS_ANY' = 'does not contains any',
    'MATCHES_ANY' = 'regex match any',
    'NOT_MATCHES_ANY' = 'does not regex match any',
    'MATCHES_ALL' = 'regex match exactly',
    'NOT_MATCHES_ALL' = 'does not regex match exactly',
    'ONE_OF' = 'is one of',
    'NOT_ONE_OF' = 'is not one of',
    'LESS_THAN' = 'less than',
    'LESS_THAN_EQUAL' = 'less than or equal',
    'GREATER_THAN' = 'greater than',
    'GREATER_THAN_EQUAL' = 'greater than or equal',
}
export enum PolicyDefinitionType {
    'EXPRESSION' = 'expression',
    'OPERATOR' = 'operator',
    'POLICY' = 'policy',
}
export enum Role {
    'OWNER' = 'Owner',
    'ADMIN' = 'Admin',
    'DEVELOPER' = 'Developer',
    'BILLER' = 'Biller',
}
export type Tag = {
    id?: string;
    name?: string;
    description?: string;
    color: Color | string;
    created_at: string | null;
    updated_at: string | null;
};
export type TagCollection = Tag[];
export type Team = {
    id?: string;
    organization?: Organization;
    name?: string;
    icon?: string;
    members?: UserCollection;
    color: Color | string | null;
};
export type TeamCollection = Team[];
export type User = {
    id?: number;
    team?: Team;
    name?: string;
    first_name?: string;
    last_name?: string;
    email?: string;
    avatar?: string;
    gravatar?: string;
    teams?: TeamCollection;
    roles?: Role[];
    status?: UserStatus;
    theme: string;
    email_verified_at: string | null;
};
export type UserCollection = User[];
export enum UserStatus {
    'ACTIVE' = 'active',
    'INACTIVE' = 'inactive',
    'PENDING' = 'pending',
}
