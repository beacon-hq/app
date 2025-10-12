<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Actions\Traits\AsAction;
use App\Models\Application as ApplicationModel;
use App\Values\Application;

class CreateApplication
{
    use AsAction;

    public function handle(Application $application): Application
    {
        $model = new ApplicationModel();
        $model->name = $application->name;
        $model->display_name = $application->displayName;
        $model->description = $application->description;
        $model->color = $application->color;
        $model->save();

        return Application::from($model);
    }
}
