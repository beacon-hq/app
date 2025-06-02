<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum PolicyDefinitionType: string
{
    use AsValue;

    case EXPRESSION = 'expression';
    case OPERATOR = 'operator';
    case POLICY = 'policy';
    case DATE_RANGE = 'date_range';
}
