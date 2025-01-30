<?php

declare(strict_types=1);

namespace App\Values\Casts;

use Bag\Casts\CastsPropertySet;
use Bag\Collection as BagCollection;
use Illuminate\Support\Collection;

class FeatureScopeSerializeable implements CastsPropertySet
{
    public function set(BagCollection $propertyTypes, string $propertyName, Collection $properties): mixed
    {
        $property = $properties->get($propertyName);

        if (!is_string($property)) {
            return $property;
        }

        return json_decode($property, true);
    }
}
