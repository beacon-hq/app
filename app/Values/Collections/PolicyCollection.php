<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\Policy;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('Policy[]')]
#[TypeScript]
/**
 * @implements Collection<Policy>
 */
class PolicyCollection extends Collection
{
}
