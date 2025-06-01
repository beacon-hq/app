<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StripeProduct;
use App\Values\Collections\ProductCollection;
use App\Values\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function all(bool $onlyActive = true): ProductCollection
    {
        return Product::collect(StripeProduct::query()
            ->when($onlyActive, fn (Builder $query) => $query->whereActive(true))
            ->whereJsonDoesntContain('metadata', ['type' => 'meter'])
            ->with(['basePrice', 'meteredPrice'])
            ->orderBy('order')
            ->get());
    }

    public function find(string $id): Product
    {
        return Product::from(StripeProduct::query()
            ->with(['basePrice', 'meteredPrice'])
            ->findOrFail($id));
    }
}
