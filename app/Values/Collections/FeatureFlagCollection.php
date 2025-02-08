<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\FeatureFlag;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('FeatureFlag[]')]
#[TypeScript]
/**
 * @implements Collection<FeatureFlag>
 */
class FeatureFlagCollection extends Collection
{
    public function jsonSerialize(): array
    {
        return ['features' => $this->pluck('name')->toArray()];
    }
}
