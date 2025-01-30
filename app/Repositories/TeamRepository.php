<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team;
use App\Values\Team as TeamValue;

class TeamRepository
{
    public function getById(string $id): TeamValue
    {
        return TeamValue::fromModel(Team::findOrFail($id));
    }
}
