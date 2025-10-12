<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Actions\Traits\AsAction;
use App\Models\StripeProduct;
use App\Values\Collections\ProductCollection;
use App\Values\Product;
use Illuminate\Database\Eloquent\Builder;

class ListProducts
{
    use AsAction;

    public function handle(bool $onlyActive = true): ProductCollection
    {
        $products = StripeProduct::query()
            ->when($onlyActive, fn (Builder $query) => $query->whereActive(true))
            ->whereJsonDoesntContain('metadata', ['type' => 'meter'])
            ->with(['basePrice', 'meteredPrice'])
            ->orderBy('order')
            ->get();

        return Product::collect($products);
    }
}
