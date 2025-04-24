<?php

declare(strict_types=1);

namespace App\Events;

use App\Values\Organization;
use Illuminate\Foundation\Events\Dispatchable;

class OrganizationChangedEvent
{
    use Dispatchable;

    public function __construct(public Organization $organization)
    {
    }
}
