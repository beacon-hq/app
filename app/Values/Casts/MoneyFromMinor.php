<?php

declare(strict_types=1);

namespace App\Values\Casts;

use Brick\Money\Money as BrickMoney;
use Illuminate\Support\Collection as LaravelCollection;
use Override;

class MoneyFromMinor extends \Bag\Casts\MoneyFromMinor
{
    #[Override]
    public function get(string $propertyName, LaravelCollection $properties): mixed
    {
        /** @var BrickMoney $money */
        $money = $properties->get($propertyName);

        return $money->formatTo($this->locale, true);
    }
}
