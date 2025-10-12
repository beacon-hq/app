<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Traits\AsAction;
use App\Values\Collections\UserCollection;
use App\Values\Team;
use Illuminate\Support\Arr;

class GetTeamMembers
{
    use AsAction;

    public function handle(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        $filters['teams'][] = $team->id;

        $users = ListUsers::run(orderBy: Arr::wrap($orderBy), filters: $filters, page: $page, perPage: $perPage);

        return $users;
    }
}
