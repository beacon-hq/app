<?php

declare(strict_types=1);

namespace App\Values\Collections;

use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('Policy[]')]
#[TypeScript]
class PolicyCollection extends Collection
{
}
