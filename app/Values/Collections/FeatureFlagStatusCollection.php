<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\FeatureFlagStatus;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('FeatureFlagStatus[]')]
#[TypeScript]
/**
 * @implements Collection<FeatureFlagStatus>
 */
class FeatureFlagStatusCollection extends Collection
{
}
