<?php

declare(strict_types=1);

namespace App\Values\Collections;

use App\Values\Tag;
use Bag\Collection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[LiteralTypeScriptType('Tag[]')]
#[TypeScript]
/**
 * @implements Collection<Tag>
 */
class TagCollection extends Collection
{
}
