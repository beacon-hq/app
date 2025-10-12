<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Actions\Traits\AsAction;
use App\Values\Application;

class FindApplicationByName
{
    use AsAction;

    public function handle(string $name): ?Application
    {
        $application = \App\Models\Application::query()->where('name', $name)->first();

        if (!$application) {
            return null;
        }

        return Application::from($application);
    }
}
