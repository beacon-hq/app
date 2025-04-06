<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $featureFlag, mixed|null $value, bool $active)
 */
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlagResponse extends Bag
{
    public function __construct(
        public string $featureFlag,
        public mixed $value,
        public bool $active,
    ) {
    }
}
