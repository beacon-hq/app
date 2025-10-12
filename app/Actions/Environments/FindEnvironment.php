<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;

class FindEnvironment
{
    use AsAction;

    public function handle(string $id): Environment
    {
        $environment = EnvironmentModel::findOrFail($id);

        return Environment::from($environment);
    }
}
