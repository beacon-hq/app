<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Mappers\SnakeCase;

#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class AppContext extends Bag
{
    public function __construct(
        public ?Team $team = null,
    ) {
    }

    public static function empty(): array
    {
        return [
            'team' => null,
        ];
    }
}
