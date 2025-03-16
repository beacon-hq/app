<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum Role: string
{
    use AsValue;

    case OWNER = 'Owner';
    case ADMIN = 'Admin';
    case DEVELOPER = 'Developer';
    case BILLER = 'Biller';
}
