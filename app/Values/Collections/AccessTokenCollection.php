<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\AccessToken;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('AccessToken[]')]
#[TypeScript]
/**
 * @implements Collection<AccessToken>
 */
class AccessTokenCollection extends Collection
{
}
