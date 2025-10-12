<?php

declare(strict_types=1);

namespace App\Actions\Environments;

use App\Actions\Traits\AsAction;
use App\Models\Environment as EnvironmentModel;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Environment;

class ListEnvironments
{
    use AsAction;

    public function handle(): EnvironmentCollection
    {
        $environments = EnvironmentModel::orderBy('name')->get();

        return Environment::collect($environments);
    }
}
