<?php

declare(strict_types=1);

namespace App\Features;

class BillingEnabled
{
    public function resolve(): mixed
    {
        return config('beacon.billing.enabled', false) === true;
    }
}
