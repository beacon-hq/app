<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Values\Optional;

/**
 * @method static static from(Optional|Organization $organization, Optional|Team $team)
 */
#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class AppContext extends Bag
{
    public function __construct(
        public Optional|Organization $organization,
        public Optional|Team $team,
    ) {
    }

    public static function empty(): array
    {
        return [
            'organization' => null,
            'team' => null,
        ];
    }
}
