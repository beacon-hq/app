<?php

declare(strict_types=1);

namespace App\Features;

class BillingEnabled
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return config('beacon.billing.enabled');
    }
}
