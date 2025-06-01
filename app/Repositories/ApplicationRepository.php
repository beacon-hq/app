<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use App\Values\Application as ApplicationValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ApplicationRepository
{
    public function all(array|string $orderBy = ['display_name', 'name']): Collection
    {
        $query = Application::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return $query->get()->append('environments');
    }

    public function create(ApplicationValue $application): Application
    {
        return Application::create(
            $application
                ->with(color: $application->color ?? '')
                ->toCollection()
                ->except('last_seen_at')
                ->toArray()
        );
    }

    public function update(ApplicationValue $application): Application
    {
        $app = Application::findOrFail($application->id);

        $app->update(
            $application
                ->with(color: $application->color ?? '')
                ->toCollection()
                ->except('name', 'id', 'last_seen_at')
                ->toArray()
        );

        return $app->fresh();
    }

    public function find(string $id): Application
    {
        return Application::findOrFail($id);
    }
}
