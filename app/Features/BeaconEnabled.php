<?php

declare(strict_types=1);

namespace App\Features;

class BeaconEnabled
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return config('beacon.enabled', false);
    }
}
