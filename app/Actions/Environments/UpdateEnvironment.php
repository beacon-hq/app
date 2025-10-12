<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;

class UpdateEnvironment
{
    use AsAction;

    public function handle(Environment $environment): Environment
    {
        $env = EnvironmentModel::findOrFail($environment->id);
        $env->fill($environment->toArray());
        $env->save();

        return Environment::from($env->fresh());
    }
}
