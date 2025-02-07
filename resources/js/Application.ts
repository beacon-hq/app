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
    color: Color | string;
    environments: EnvironmentCollection | null;
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
    slug: string | null;
    name: string | null;
    description: string | null;
    color: Color | string;
};
export type EnvironmentCollection = Environment[];
export type FeatureFlag = {
    name: string | null;
    slug: string | null;
    description: string | null;
    last_seen_at: string | null;
    feature_type: FeatureType | null;
    tags: TagCollection | null;
    statuses: FeatureFlagStatusCollection | null;
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
export type FeatureFlagResponse = {
    feature_flag: string;
    value: any;
    active: boolean;
};
export type FeatureFlagStatus = {
    application: Application | null;
    environment: Environment | null;
    feature_flag: FeatureFlag | null;
    policies: PolicyCollection | null;
    status: boolean;
    id: string | null;
};
export type FeatureFlagStatusCollection = FeatureFlagStatus[];
export type FeatureType = {
    slug: string | null;
    name: string | null;
    description: string | null;
    temporary: boolean;
    color: Color | string;
    icon: string | null;
    created_at: string | null;
    updated_at: string | null;
    id: string | null;
};
export type FeatureTypeCollection = FeatureType[];
export type Policy = {
    slug: string | null;
    name: string | null;
    description: string | null;
    definition: PolicyDefinitionCollection | null;
    created_at: string | null;
    updated_at: string | null;
};
export type PolicyCollection = Policy[];
export type PolicyDefinition = {
    type: PolicyDefinitionType;
    subject: string;
    operator?: PolicyDefinitionMatchOperator;
};
export type PolicyDefinitionCollection = PolicyDefinition[];
export enum PolicyDefinitionMatchOperator {
    'EQUAL' = '=',
    'NOT_EQUAL' = '!=',
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
    'LESS_THAN' = '<',
    'LESS_THAN_EQUAL' = '<=',
    'GREATER_THAN' = '>',
    'GREATER_THAN_EQUAL' = '>=',
}
export enum PolicyDefinitionType {
    'EXPRESSION' = 'expression',
    'OPERATOR' = 'operator',
    'POLICY' = 'policy',
}
export type Tag = {
    slug: string | null;
    name: string | null;
    description: string | null;
    color: Color | string;
    created_at: string | null;
    updated_at: string | null;
    id: string | null;
};
export type TagCollection = Tag[];
