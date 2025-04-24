<?php

declare(strict_types=1);

namespace App\Events;

use App\Values\Team;
use Illuminate\Foundation\Events\Dispatchable;

class TeamChangedEvent
{
    use Dispatchable;

    public function __construct(public Team $team)
    {
    }
}
