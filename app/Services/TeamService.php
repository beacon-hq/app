<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Values\AppContext;
use App\Values\Team as TeamValue;

class TeamService
{
    public function __construct(protected TeamRepository $teamRepository, protected AppContext $appContext)
    {
    }

    public function getTeam(): TeamValue
    {
        // return TeamValue::from(Team::findOrFail(\App::context()->team->id));
        return $this->teamRepository->getById($this->appContext->team->id);
    }
}
