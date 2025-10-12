<?php

declare(strict_types=1);

namespace App\Actions\Policies;

use App\Actions\Traits\AsAction;
use App\Models\Policy as PolicyModel;
use App\Values\Policy;

class FindPolicy
{
    use AsAction;

    public function handle(string $id): Policy
    {
        $policy = PolicyModel::findOrFail($id);

        return Policy::from($policy);
    }
}
