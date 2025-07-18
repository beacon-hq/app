<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Attributes\MapName;
use Bag\Mappers\SnakeCase;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class TrendMetric extends \Beacon\Metrics\Values\TrendMetric
{
}
