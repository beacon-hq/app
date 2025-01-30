<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum PolicyDefinitionMatchOperator: string
{
    use AsValue;

    case EQUALS = '=';
    case NOT_EQUAL = '!=';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'does not contains';
    case MATCHES = 'matches';
    case NOT_MATCHES = 'does not match';
    case ONE_OF = 'is one of';
    case NOT_ONE_OF = 'is not one of';
    case LESS_THAN = '<';
    case LESS_THAN_EQUALS = '<=';
    case GREATED_THAN = '>';
    case GREATER_THAN_EQUALS = '>=';
}
