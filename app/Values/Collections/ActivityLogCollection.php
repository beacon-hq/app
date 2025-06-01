<?php

declare(strict_types=1);

namespace App\Values\Collections;

use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('ActivityLog[]')]
#[TypeScript]
class ActivityLogCollection extends Collection
{
}
