<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Values\Product;

class PredictNextBill
{
    use AsAction;

    public function handle(array $planMetrics, Product $product): array
    {
        if (($planMetrics['evaluations']['projections']['date']['projected_total'] ?? 0) > $product->entitlements['evaluations']) {
            $overage = $product
                ->meteredPrice
                ->multipliedBy(ceil(($planMetrics['evaluations']['projections']['date']['projected_total'] - $product->entitlements['evaluations']) / 1000));

            return [
                'total' => $overage
                    ->plus($product->basePrice)
                    ->formatTo('en_US', true),
                'base' => $product->basePrice->formatTo('en_US', true),
                'metered' => $overage->formatTo('en_US', true),
            ];
        } else {
            return [
                'total' => $product->basePrice->formatTo('en_US', true),
                'base' => $product->basePrice->formatTo('en_US', true),
                'metered' => Product::formatZeroMoney(),
            ];
        }
    }
}
