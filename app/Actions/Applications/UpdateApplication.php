<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Actions\Traits\AsAction;
use App\Models\Application as ApplicationModel;
use App\Values\Application;

class UpdateApplication
{
    use AsAction;

    public function handle(Application $application): Application
    {
        $model = ApplicationModel::findOrFail($application->id);
        $model->fill($application->toArray());
        $model->save();

        return Application::from($model->fresh());
    }
}
