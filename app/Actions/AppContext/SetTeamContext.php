<?php

declare(strict_types=1);

namespace App\Actions\AppContext;

use App\Actions\Traits\AsAction;
use App\Events\TeamChangedEvent;
use App\Models\Team;
use App\Values\Team as TeamValue;
use Illuminate\Support\Facades\Context;

/**
 * @method static SetTeamContext run(Team|TeamValue|null $team)
 */
class SetTeamContext
{
    use AsAction;

    public function handle(Team|TeamValue|null $team): ?TeamValue
    {
        if ($team instanceof Team) {
            $team = TeamValue::from($team);
        }

        $previousTeamId = Context::has('team') ? Context::get('team')?->id : null;

        Context::add('team', $team);

        if ($team !== null && ($previousTeamId === null || $previousTeamId !== $team->id)) {
            TeamChangedEvent::dispatch($team);
        }

        return $team;
    }
}
