<?php

declare(strict_types=1);

namespace App\Actions\AppContext;

use App\Actions\Traits\AsAction;
use App\Values\Organization;
use App\Values\Team;
use Illuminate\Support\Facades\Context;

/**
 * @method static array{organization: ?Organization, team: ?Team} run()
 * @method static ?Team team()
 * @method static ?Organization organization()
 */
class GetAppContext
{
    use AsAction;

    public static function team(): ?Team
    {
        return self::make()->handle()['team'];
    }

    public static function organization(): ?Organization
    {
        return self::make()->handle()['organization'];
    }

    /**
     * @return array{organization: ?Organization, team: ?Team}
     */
    public function handle(): array
    {
        return [
            'organization' => Context::has('organization') ? Context::get('organization') : null,
            'team' => Context::has('team') ? Context::get('team') : null,
        ];
    }
}
