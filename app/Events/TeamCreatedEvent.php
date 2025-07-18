<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class TeamCreatedEvent implements ShouldQueue
{
    use Dispatchable;

    public function __construct(public Team $team)
    {
    }
}
