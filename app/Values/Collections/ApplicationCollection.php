<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\Application;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('Application[]')]
#[TypeScript]
/**
 * @implements Collection<Application>
 */
class ApplicationCollection extends Collection
{
}
