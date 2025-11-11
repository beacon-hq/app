<?php

declare(strict_types=1);

namespace App\Features;

class BeaconEnabled
{
    public function resolve(): mixed
    {
        return config('beacon.enabled', false) === true;
    }
}
