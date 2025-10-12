<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\AppContext;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\Traits\AsAction;
use App\Models\Organization;
use App\Models\Team;
use App\Values\Organization as OrganizationValue;
use App\Values\Team as TeamValue;

/**
 * @method static array run(Organization|OrganizationValue|null $organization, Team|TeamValue|null $team)
 */
class SetAppContext
{
    use AsAction;

    public static function clear(): void
    {
        static::make()->handle(null, null);
    }

    /**
     * @return array{organization: ?OrganizationValue, team: ?TeamValue}
     */
    public function handle(Organization|OrganizationValue|null $organization, Team|TeamValue|null $team): array
    {
        return [
            'organization' => SetOrganizationContext::run($organization),
            'team' => SetTeamContext::run($team),
        ];
    }
}
