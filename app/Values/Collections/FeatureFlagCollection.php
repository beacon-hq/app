<?php

declare(strict_types=1);

namespace App\Values\Collections;

use Bag\Collection;

class FeatureFlagCollection extends Collection
{
    public function jsonSerialize(): array
    {
        return ['features' => $this->pluck('name')->toArray()];
    }
}
