<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum Permission: string
{
    use AsValue;

    case BILLING = 'billing';
    case BILLING_CREATE = 'billing.create';
    case BILLING_UPDATE = 'billing.update';
    case BILLING_VIEW = 'billing.view';
    case BILLING_DELETE = 'billing.delete';

    case FEATURE_FLAGS = 'feature-flags';
    case FEATURE_FLAGS_CREATE = 'feature-flags.create';
    case FEATURE_FLAGS_UPDATE = 'feature-flags.update';
    case FEATURE_FLAGS_VIEW = 'feature-flags.view';
    case FEATURE_FLAGS_DELETE = 'feature-flags.delete';

    case FEATURE_FLAG_STATUS = 'feature-flag-status';
    case FEATURE_FLAG_STATUS_CREATE = 'feature-flag-status.create';
    case FEATURE_FLAG_STATUS_UPDATE = 'feature-flag-status.update';
    case FEATURE_FLAG_STATUS_VIEW = 'feature-flag-status.view';
    case FEATURE_FLAG_STATUS_DELETE = 'feature-flag-status.delete';

    case FEATURE_TYPES = 'feature-types';
    case FEATURE_TYPES_CREATE = 'feature-types.create';
    case FEATURE_TYPES_UPDATE = 'feature-types.update';
    case FEATURE_TYPES_VIEW = 'feature-types.view';
    case FEATURE_TYPES_DELETE = 'feature-types.delete';

    case APPLICATIONS = 'applications';
    case APPLICATIONS_CREATE = 'applications.create';
    case APPLICATIONS_UPDATE = 'applications.update';
    case APPLICATIONS_VIEW = 'applications.view';
    case APPLICATIONS_DELETE = 'applications.delete';

    case ENVIRONMENTS = 'environments';
    case ENVIRONMENTS_CREATE = 'environments.create';
    case ENVIRONMENTS_UPDATE = 'environments.update';
    case ENVIRONMENTS_VIEW = 'environments.view';
    case ENVIRONMENTS_DELETE = 'environments.delete';

    case POLICIES = 'policies';
    case POLICIES_CREATE = 'policies.create';
    case POLICIES_UPDATE = 'policies.update';
    case POLICIES_VIEW = 'policies.view';
    case POLICIES_DELETE = 'policies.delete';

    case TAGS = 'tags';
    case TAGS_CREATE = 'tags.create';
    case TAGS_UPDATE = 'tags.update';
    case TAGS_VIEW = 'tags.view';
    case TAGS_DELETE = 'tags.delete';

    case USERS = 'users';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_VIEW = 'users.view';
    case USERS_DELETE = 'users.delete';

    case TEAMS = 'teams';
    case TEAMS_CREATE = 'teams.create';
    case TEAMS_UPDATE = 'teams.update';
    case TEAMS_VIEW = 'teams.view';
    case TEAMS_DELETE = 'teams.delete';

    case API_TOKENS = 'api-tokens';
    case API_TOKENS_CREATE = 'api-tokens.create';
    case API_TOKENS_UPDATE = 'api-tokens.update';
    case API_TOKENS_VIEW = 'api-tokens.view';
    case API_TOKENS_DELETE = 'api-tokens.delete';
}
