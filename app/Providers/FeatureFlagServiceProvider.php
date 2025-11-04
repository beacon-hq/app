<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class FeatureFlagServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Feature::define('beacon-enabled');
        Feature::define('waitlist-enabled');
        Feature::define('billing-enabled');
        Feature::define('api-rate-limiting-enabled');
        Feature::define('request-metrics-enabled');
        Feature::define('request-metrics-reporting');
        Feature::define('status-enabled');
    }
}
