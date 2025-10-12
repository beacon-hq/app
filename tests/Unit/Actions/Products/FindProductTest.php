<?php

declare(strict_types=1);

use App\Actions\Products\FindProduct;
use App\Models\StripeProduct;
use Database\Seeders\StripeSeeder;
use function Pest\Laravel\seed;

it('can find products', function () {
    seed(StripeSeeder::class);

    $expected = StripeProduct::whereNotNull('stripe_base_price_id')->whereNotNull('stripe_metered_price_id')->first();
    $product = FindProduct::run($expected->id);

    expect($product->id)
        ->toBe($expected->id)
        ->and($product->name)
        ->toBe($expected->name)
        ->and($product->active)
        ->toBe($expected->active)
        ->and($product->metadata['icon'])
        ->toBe($expected->metadata['icon'])
        ->and((int) $product->metadata['evaluation_tier_size'])
        ->toBe((int) $expected->metadata['evaluation_tier_size'])
        ->and($product->basePrice->getMinorAmount()->toInt())
        ->toBe($expected->basePrice->unit_amount)
        ->and($product->meteredPrice->multipliedBy(1000)->dividedBy($product->metadata['evaluation_tier_size'])->getMinorAmount()->toInt())
        ->toBe($expected->meteredPrice->unit_amount)
    ;
});
