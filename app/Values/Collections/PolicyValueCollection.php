<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\PolicyValue;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('PolicyValue[]')]
#[TypeScript]
/**
 * @implements Collection<PolicyValue>
 */
class PolicyValueCollection extends Collection
{
}
