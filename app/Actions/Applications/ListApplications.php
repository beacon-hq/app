<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Actions\Traits\AsAction;
use App\Models\Application as ApplicationModel;
use App\Values\Application;
use App\Values\Collections\ApplicationCollection;

class ListApplications
{
    use AsAction;

    public function handle(): ApplicationCollection
    {
        $applications = ApplicationModel::query()
            ->orderBy('display_name')
            ->orderBy('name')
            ->get()
            ->append('environments');

        return Application::collect($applications);
    }
}
