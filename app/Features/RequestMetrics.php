<?php

declare(strict_types=1);

namespace App\Features;

class RequestMetrics
{
    public function resolve(): mixed
    {
        return config('beacon.metrics.request.enabled', false) === true;
    }
}
