<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Actions\Traits\AsAction;
use App\Models\StripeProduct;
use App\Values\Product;

/**
 * @method static Product run(string $id)
 */
class FindProduct
{
    use AsAction;

    public function handle(string $id): Product
    {
        $product = StripeProduct::query()
            ->with(['basePrice', 'meteredPrice'])
            ->findOrFail($id);

        return Product::from($product);
    }
}
