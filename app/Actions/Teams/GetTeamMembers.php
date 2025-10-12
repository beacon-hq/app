<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Traits\AsAction;
use App\Models\User;
use App\Values\Collections\UserCollection;
use App\Values\Team;
use App\Values\User as UserValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GetTeamMembers
{
    use AsAction;

    public function handle(Team $team, array|string $orderBy = ['name'], ?int $page = null, int $perPage = 20, array $filters = []): UserCollection
    {
        // Add team ID to filters
        $filters['teams'][] = $team->id;

        // Build the query with ordering, filtering, and pagination
        $query = $this->buildQuery(orderBy: $orderBy, filters: $filters, page: $page, perPage: $perPage);

        // Execute the query and transform results to value objects
        $users = $query->get();

        return UserValue::collect($users);
    }

    protected function buildQuery(array|string|null $orderBy = null, array $filters = [], ?int $page = null, int $perPage = 20): Builder
    {
        $query = User::query();

        foreach (Arr::wrap($orderBy) as $column) {
            if ($column === 'name') {
                $query = $query->orderBy('first_name')->orderBy('last_name');

                continue;
            }
            $query = $query->orderBy($column);
        }

        if (!empty($filters)) {
            $query = $this->filter($query, $filters);
        }

        if ($page !== null) {
            $query = $query->forPage($page, $perPage);
        }

        return $query;
    }

    /**
     * @param Builder<User> $query
     */
    protected function filter(Builder $query, array $filters = []): Builder
    {
        $query
            ->when(isset($filters['teams'][0]), fn (Builder $query) => $query->whereHas(
                'teams',
                fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query
                        ->whereIn('id', $filters['teams'])
                        ->orWhereIn(
                            'name',
                            $filters['teams']
                        )
                )
            ))
            ->when(isset($filters['name'][0]), fn (Builder $query) => $query->where(fn (Builder $query) => $query->where(DB::raw('CONCAT(first_name, \' \', last_name)'), 'ILIKE', '%' . $filters['name'][0] . '%')->orWhere('email', 'ILIKE', '%' . $filters['name'][0] . '%')))
            ->when(isset($filters['roles']), fn (Builder $query) => $query->role($filters['roles']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->whereIn('status', $filters['status']));

        return $query;
    }
}
