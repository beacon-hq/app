<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\Environment;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('Environment[]')]
#[TypeScript]
/**
 * @implements Collection<Environment>
 */
class EnvironmentCollection extends Collection
{
}
