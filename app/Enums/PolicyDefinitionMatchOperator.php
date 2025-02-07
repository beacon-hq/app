<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum PolicyDefinitionMatchOperator: string
{
    use AsValue;

    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case CONTAINS_ALL = 'contains exactly';
    case NOT_CONTAINS_ALL = 'does not contains exactly';
    case CONTAINS_ANY = 'contains any';
    case NOT_CONTAINS_ANY = 'does not contains any';
    case MATCHES_ANY = 'regex match any';
    case NOT_MATCHES_ANY = 'does not regex match any';
    case MATCHES_ALL = 'regex match exactly';
    case NOT_MATCHES_ALL = 'does not regex match exactly';
    case ONE_OF = 'is one of';
    case NOT_ONE_OF = 'is not one of';
    case LESS_THAN = '<';
    case LESS_THAN_EQUAL = '<=';
    case GREATER_THAN = '>';
    case GREATER_THAN_EQUAL = '>=';
}
