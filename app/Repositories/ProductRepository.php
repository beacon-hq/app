<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StripeProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function all(bool $onlyActive = true): Collection
    {
        return StripeProduct::query()
            ->when($onlyActive, fn (Builder $query) => $query->whereActive(true))
            ->whereJsonDoesntContain('metadata', ['type' => 'meter'])
            ->with(['basePrice', 'meteredPrice'])
            ->orderBy('order')
            ->get();
    }

    public function find(string $id): StripeProduct
    {
        return StripeProduct::query()
            ->with(['basePrice', 'meteredPrice'])
            ->findOrFail($id);
    }
}
