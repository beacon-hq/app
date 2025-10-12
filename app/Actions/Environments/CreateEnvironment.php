<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;

class CreateEnvironment
{
    use AsAction;

    public function handle(Environment $environment): Environment
    {
        $data = $environment
            ->with(color: $environment->color ?? '')
            ->toArray();

        // Create a new model instance
        $createdEnvironment = EnvironmentModel::create($data);

        return Environment::from($createdEnvironment);
    }
}
