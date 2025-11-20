<?php

declare(strict_types=1);

namespace App\Features;

class AnalyticsEnabled
{
    public function resolve(): mixed
    {
        return config('beacon.metrics.analytics.enabled', false) === true;
    }
}
