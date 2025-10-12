<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Environment;

class FindEnvironmentByName
{
    use AsAction;

    public function handle(string $name): ?Environment
    {
        $environment = EnvironmentModel::where('name', $name)->first();

        if (!$environment) {
            return null;
        }

        return Environment::from($environment);
    }
}
