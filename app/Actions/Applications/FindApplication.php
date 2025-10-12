<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Actions\Traits\AsAction;
use App\Models\Application as ApplicationModel;
use App\Values\Application;

class FindApplication
{
    use AsAction;

    public function handle(string $id): Application
    {
        $application = ApplicationModel::query()->findOrFail($id);

        return Application::from($application);
    }
}
