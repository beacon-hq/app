<?php

declare(strict_types=1);

namespace App\Features;

class StatusEnabled
{
    public function resolve(): mixed
    {
        return config('beacon.status.enabled', false) === true;
    }
}
