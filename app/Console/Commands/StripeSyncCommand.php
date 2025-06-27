<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StripePrice;
use App\Models\StripeProduct;
use Exception;
use Illuminate\Console\Command;
use Laravel\Cashier\Cashier;
use Stripe\StripeObject;

class StripeSyncCommand extends Command
{
    protected $signature = 'stripe:sync';

    protected $description = 'Synchronize products, prices, and subscriptions from Stripe using Cashier';

    public function handle(): void
    {
        try {
            $this->syncProducts();
            $this->syncPrices();
            $this->syncOrder();
            $this->info('Stripe sync completed successfully.');
        } catch (Exception $e) {
            $this->error('Stripe sync failed: ' . $e->getMessage());
            echo $e->getTraceAsString();
        }
    }

    protected function syncProducts(): void
    {
        $this->info('Syncing products...');
        $stripeProducts = Cashier::stripe()->products->all();

        foreach ($stripeProducts->data as $key => $stripeProduct) {
            /** @var StripeObject $entitlements */
            $entitlements = collect(data_get(Cashier::stripe()->products->allFeatures($stripeProduct->id), 'data.*.entitlement_feature.metadata'))->flatMap(fn (StripeObject $entitlement) => $entitlement->toArray())->toArray();

            StripeProduct::updateOrCreate(
                ['stripe_id' => $stripeProduct->id],
                [
                    'name' => $stripeProduct->name,
                    'description' => $stripeProduct->description ?? '',
                    'active' => $stripeProduct->active,
                    'entitlements' => $entitlements,
                    'metadata' => $stripeProduct->metadata->toArray(),
                    'order' => $key,
                ]
            );
        }

        $this->info(count($stripeProducts->data) . ' products synchronized.');
    }

    protected function syncPrices(): void
    {
        $this->info('Syncing prices...');
        $stripePrices = Cashier::stripe()->prices->all(['limit' => 100]);
        $count = 0;

        foreach ($stripePrices->data as $stripePrice) {
            $product = StripeProduct::where('stripe_id', $stripePrice->product)->first();

            if ($product) {
                $price = StripePrice::updateOrCreate(
                    ['stripe_id' => $stripePrice->id],
                    [
                        'stripe_product_id' => $product->id,
                        'name' => $stripePrice->nickname ?? '',
                        'unit_amount' => $stripePrice->unit_amount,
                        'active' => $stripePrice->active,
                    ]
                );

                if (!isset($product->metadata['type']) || $product->metadata['type'] !== 'meter') {
                    if ($stripePrice->active) {
                        if (($stripePrice->recurring->usage_type ?? null) !== 'metered') {
                            $product->stripe_base_price_id = $price->id;
                        } else {
                            $product->stripe_metered_price_id = $price->id;
                        }
                    }
                }
                $product->save();
                $count++;
            } else {
                $this->warn("No matching product found for price: {$stripePrice->id}");
            }
        }

        $metered = StripeProduct::query()
            ->whereJsonContains('metadata', ['type' => 'meter'])
            ->first();

        StripeProduct::whereJsonDoesntContain('metadata', ['type' => 'meter'])->get()->each(function (StripeProduct $product) use ($metered) {
            $product->stripe_metered_price_id = $metered->prices()->where('unit_amount', $product->meteredPrice?->unit_amount)->first()?->id;
            $product->save();
        });

        $this->info($count . ' prices synchronized.');
    }

    protected function syncOrder()
    {
        $this->info('Syncing order...');
        $products = StripeProduct::select('stripe_products.*')->join('stripe_prices', 'stripe_products.stripe_base_price_id', 'stripe_prices.id')->orderBy('stripe_prices.unit_amount')->get();

        foreach ($products as $key => $product) {
            $product->order = $key;
            $product->save();
        }

        $this->info('Order synchronized.');
    }
}
