<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum RolloutStrategy: string
{
    use AsValue;

    case CONTEXT = 'context';
    case RANDOM = 'random';
}
