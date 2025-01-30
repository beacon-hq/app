<?php

declare(strict_types=1);

namespace App\Values\Casts;

use App\Enums\Color as ColorEnum;
use Bag\Casts\CastsPropertyGet;
use Bag\Casts\CastsPropertySet;
use Bag\Collection as BagCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Color implements CastsPropertySet, CastsPropertyGet
{
    /**
     * @inheritDoc
     */
    public function set(BagCollection $propertyTypes, string $propertyName, Collection $properties): mixed
    {
        $property = $properties->get($propertyName);

        if (!is_string($property)) {
            return $property;
        }

        try {
            return ColorEnum::from($property);
        } catch (\ValueError) {
            if (!preg_match('/^#?[0-9a-fA-F]{6}$/', $property)) {
                throw ValidationException::withMessages([
                    $propertyName => [
                        'The color value must be a valid hex color code or color name.'
                    ]
                ]);
            }

            return $property;
        }
    }

    public function get(string $propertyName, Collection $properties): mixed
    {
        $property = $properties->get($propertyName);

        if ($property instanceof ColorEnum) {
            return $property->value;
        }

        return $property;
    }
}
