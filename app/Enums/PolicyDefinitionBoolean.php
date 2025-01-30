<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum PolicyDefinitionBoolean: string
{
    use AsValue;

    case AND = 'AND';
    case OR = 'OR';
    case NOT = 'AND NOT';
    case XOR = 'XOR';
}
