<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Bag;

readonly class AppContext extends Bag
{
    public function __construct(
        public ?Tenant $tenant = null,
    ) {
    }

    public static function empty(): array
    {
        return [
            'tenant' => null,
        ];
    }
}
