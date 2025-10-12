<?php

declare(strict_types=1);

use App\Actions\Products\ListProducts;
use App\Values\Collections\ProductCollection;
use Database\Seeders\StripeSeeder;
use function Pest\Laravel\seed;

it('lists products', function () {
    seed(StripeSeeder::class);

    $products = ListProducts::run();

    expect($products)
        ->toBeInstanceOf(ProductCollection::class)
        ->and($products->count())
        ->toBe(4);
});
