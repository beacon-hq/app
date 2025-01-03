<?php

declare(strict_types=1);

namespace App\Values\Collections;

use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('FeatureFlag[]')]
#[TypeScript]
class FeatureFlagCollection extends Collection
{
    public function jsonSerialize(): array
    {
        return ['features' => $this->pluck('name')->toArray()];
    }
}
