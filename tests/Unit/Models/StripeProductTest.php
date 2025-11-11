<?php

declare(strict_types=1);

use App\Models\StripeProduct;

it('casts metadata to array on a new model when set as array', function (): void {
    $product = StripeProduct::make([
        'stripe_id' => 'prod_test',
        'name' => 'Test',
        'description' => 'D',
        'active' => true,
        'entitlements' => ['trial_length' => 'P1D'],
        'metadata' => ['evaluation_tier_size' => 1000, 'type' => 'standard'],
        'order' => 1,
    ]);

    expect($product->metadata)->toBeArray()
        ->and($product->metadata['evaluation_tier_size'])->toBe(1000)
        ->and($product->metadata['type'])->toBe('standard');
});

it('persists and retrieves metadata as array', function (): void {
    $product = StripeProduct::query()->create([
        'stripe_id' => 'prod_test_2',
        'name' => 'Persisted',
        'description' => 'PD',
        'active' => true,
        'entitlements' => ['trial_length' => 'P1D'],
        'metadata' => ['evaluation_tier_size' => 1000, 'type' => 'standard'],
        'order' => 2,
    ]);

    $fresh = StripeProduct::query()->findOrFail($product->id);

    expect($fresh->metadata)->toBeArray()
        ->and($fresh->metadata['evaluation_tier_size'])->toBe(1000)
        ->and($fresh->metadata['type'])->toBe('standard');
});
