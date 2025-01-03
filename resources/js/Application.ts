export type AccessToken = {
    id: number | null;
    name: string | null;
    token: string | null;
    last_used_at: string | null;
    created_at: string | null;
};
export type AccessTokenCollection = AccessToken[];
export type Application = {
    slug: string | null;
    name: string | null;
    display_name: string | null;
    description: string | null;
    last_seen_at: string | null;
    color: string | null;
    environments: EnvironmentCollection | null;
};
export type ApplicationCollection = Application[];
export enum Colors {
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
    name: string | null;
    description: string | null;
    slug: string | null;
    color: string | null;
};
export type EnvironmentCollection = Environment[];
export type FeatureFlag = {
    name: string;
    slug: string | null;
    description: string | null;
    last_seen_at: string | null;
    feature_type: FeatureType | null;
    tags: TagCollection | null;
    created_at: string | null;
    updated_at: string | null;
};
export type FeatureFlagCollection = FeatureFlag[];
export type FeatureFlagContext = {
    scope_type: string | null;
    scope: Array<any>;
    app_name: string;
    environment: string;
    session_id: string | null;
    ip: string | null;
    user_agent: string | null;
    referrer: string | null;
    url: string | null;
    method: string | null;
};
export type FeatureType = {
    name: string;
    id: string | null;
    slug: string | null;
    description: string | null;
    temporary: boolean | null;
    color: string | null;
    icon: string | null;
    created_at: string | null;
    updated_at: string | null;
};
export type FeatureTypeCollection = FeatureType[];
export type Policy = {
    slug: string | null;
    name: string | null;
    description: string | null;
    id: string | null;
    definition: PolicyDefinitionCollection | null;
    created_at: string | null;
    updated_at: string | null;
};
export type PolicyCollection = Policy[];
export type PolicyDefinition = {
    type: PolicyDefinitionType;
    subject: string;
    operator?: string;
};
export type PolicyDefinitionCollection = PolicyDefinition[];
export enum PolicyDefinitionType {
    'EXPRESSION' = 'expression',
    'OPERATOR' = 'operator',
    'POLICY' = 'policy',
}
export type Tag = {
    id: string | null;
    slug: string | null;
    name: string | null;
    description: string | null;
    color: string | null;
    created_at: string | null;
    updated_at: string | null;
};
export type TagCollection = Tag[];
