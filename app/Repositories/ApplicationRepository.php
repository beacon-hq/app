<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use App\Values\Application as ApplicationValue;
use App\Values\Collections\ApplicationCollection;
use Illuminate\Support\Arr;

class ApplicationRepository
{
    public function all(array|string $orderBy = ['display_name', 'name']): ApplicationCollection
    {
        $query = Application::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return ApplicationValue::collect($query->get()->append('environments'));
    }

    public function create(ApplicationValue $application): ApplicationValue
    {
        return ApplicationValue::from(Application::create(
            $application
                ->with(color: $application->color ?? '')
                ->toCollection()
                ->except('last_seen_at')
                ->toArray()
        ));
    }

    public function update(ApplicationValue $application): ApplicationValue
    {
        Application::where('slug', $application->slug)->firstOrFail()->update(
            $application
                ->with(color: $application->color ?? '')
                ->toCollection()
                ->except('name', 'slug', 'last_seen_at')
                ->toArray()
        );

        return $application;
    }

    public function findBySlug(?string $slug): ApplicationValue
    {
        return ApplicationValue::from(Application::where('slug', $slug)->firstOrFail());
    }
}
