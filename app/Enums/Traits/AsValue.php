<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use ArchTech\Enums\Comparable;
use ArchTech\Enums\From;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

trait AsValue
{
    use InvokableCases;
    use Names;
    use Values;
    use Options;
    use From;
    use Comparable;
}
