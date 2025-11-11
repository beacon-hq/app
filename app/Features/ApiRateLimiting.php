<?php

declare(strict_types=1);

namespace App\Features;

class ApiRateLimiting
{
    public function resolve(): mixed
    {
        return config('beacon.api.rate_limiting.enabled', false) === true;
    }
}
