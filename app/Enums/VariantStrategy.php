<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum VariantStrategy: string
{
    use AsValue;

    case RANDOM = 'random';
    case CONTEXT = 'context';
}
