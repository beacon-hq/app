<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\FeatureType;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('FeatureType[]')]
#[TypeScript]
/**
 * @implements Collection<FeatureType>
 */
class FeatureTypeCollection extends Collection
{
}
