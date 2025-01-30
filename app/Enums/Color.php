<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\AsValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum Color: string
{
    use AsValue;

    case RED = 'red';
    case ORANGE = 'orange';
    case YELLOW = 'yellow';
    case LIME = 'lime';
    case GREEN = 'green';
    case EMERALD = 'emerald';
    case CYAN = 'cyan';
    case SKY = 'sky';
    case BLUE = 'blue';
    case INDIGO = 'indigo';
    case PURPLE = 'purple';
    case FUCHSIA = 'fuchsia';
}
