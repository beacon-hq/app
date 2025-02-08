<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\PolicyDefinition;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('PolicyDefinition[]')]
#[TypeScript]
/**
 * @implements Collection<PolicyDefinition>
 */
class PolicyDefinitionCollection extends Collection
{
}
