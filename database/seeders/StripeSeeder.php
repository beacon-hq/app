<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StripePrice;
use App\Models\StripeProduct;
use Illuminate\Database\Seeder;

class StripeSeeder extends Seeder
{
    public function run(): void
    {
        StripeProduct::create([
            'id' => '01k6d4h90346esqma5ek241czt',
            'stripe_id' => 'prod_1',
            'stripe_base_price_id' => null,
            'stripe_metered_price_id' => null,
            'name' => 'Additional Feature Flag Evaluations',
            'description' => 'Once your monthly quota has been exhausted, you will be charged to the nearest (rounding up) 1,000 evaluations.',
            'active' => true,
            'entitlements' => [],
            'metadata' => ['type' => 'meter'],
            'order' => 0,
        ]);

        StripeProduct::create([
            'id' => '01k6d4h9czydkgvgcwvf5czrv8',
            'stripe_id' => 'prod_2',
            'stripe_base_price_id' => null,
            'stripe_metered_price_id' => null,
            'name' => 'Solo Plan',
            'description' => 'Get started with 10,000 feature flag evaluations per month. Ideal for side projects, internal tools, and MVPs. Includes unlimited flags, users, apps, and environments. Free for 3 months, then just $5/month.',
            'active' => true,
            'entitlements' => ['evaluations' => '10000', 'trial_length' => 'P3M'],
            'metadata' => ['icon' => 'flag', 'evaluation_tier_size' => '10000'],
            'order' => 0,
        ]);

        StripeProduct::create([
            'id' => '01k6d4h99d6peejemn66sfhsyr',
            'stripe_id' => 'prod_3',
            'stripe_base_price_id' => null,
            'stripe_metered_price_id' => null,
            'name' => 'Team Plan',
            'description' => 'Built for  teams with live apps. Includes 100,000 feature flag evaluations per month. Unlimited flags, users, apps, and environments — with usage-based billing and no seat-based pricing.',
            'active' => true,
            'entitlements' => ['evaluations' => '100000'],
            'metadata' => ['icon' => 'users', 'evaluation_tier_size' => '10000'],
            'order' => 1,
        ]);

        StripeProduct::create([
            'id' => '01k6d4h9338rx6qv0k8xbradgf',
            'stripe_id' => 'prod_4',
            'stripe_base_price_id' => null,
            'stripe_metered_price_id' => null,
            'name' => 'Scale Plan',
            'description' => 'Built for production apps at scale. Includes 2 million feature flag evaluations per month, advanced analytics, and engineering support. Unlimited flags, users, and environments — no seat limits.',
            'active' => true,
            'entitlements' => ['evaluations' => '2000000'],
            'metadata' => ['icon' => 'arrow-big-up-dash', 'evaluation_tier_size' => '100000'],
            'order' => 3,
        ]);

        StripeProduct::create([
            'id' => '01k6d4h965aafj1pbmdfkb4yjs',
            'stripe_id' => 'prod_5',
            'stripe_base_price_id' => null,
            'stripe_metered_price_id' => null,
            'name' => 'Growth Plan',
            'description' => 'For scaling products and teams. Includes 500,000 feature flag evaluations per month, plus priority support and usage analytics. Unlimited flags, environments, and users.',
            'active' => true,
            'entitlements' => ['evaluations' => '500000'],
            'metadata' => ['icon' => 'trending-up', 'evaluation_tier_size' => '100000'],
            'order' => 2,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9h9knqxpc5pz3qxxmek',
            'stripe_id' => 'price_1',
            'stripe_product_id' => '01k6d4h90346esqma5ek241czt',
            'name' => 'Team Plan Metered Price',
            'unit_amount' => 40,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hcggad2rkwcnevanrg',
            'stripe_id' => 'price_2',
            'stripe_product_id' => '01k6d4h90346esqma5ek241czt',
            'name' => 'Scale Plan Metered Price',
            'unit_amount' => 25,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9herym6sbc9yk1c9f6e',
            'stripe_id' => 'price_3',
            'stripe_product_id' => '01k6d4h90346esqma5ek241czt',
            'name' => 'Solo Plan Metered Price',
            'unit_amount' => 50,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hgm5p95fa06dwp5h62',
            'stripe_id' => 'price_4',
            'stripe_product_id' => '01k6d4h90346esqma5ek241czt',
            'name' => 'Growth Plan Metered Price',
            'unit_amount' => 35,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hkqk814nzd71x06za1',
            'stripe_id' => 'price_5',
            'stripe_product_id' => '01k6d4h9czydkgvgcwvf5czrv8',
            'name' => 'Solo Plan Base Rate',
            'unit_amount' => 500,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hrmq9cccjfk00m50vx',
            'stripe_id' => 'price_6',
            'stripe_product_id' => '01k6d4h9338rx6qv0k8xbradgf',
            'name' => 'Scale Plan Metered',
            'unit_amount' => 25,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hv55fh9ampyqwbkt6g',
            'stripe_id' => 'price_7',
            'stripe_product_id' => '01k6d4h9338rx6qv0k8xbradgf',
            'name' => 'Scale Plan Base Rate',
            'unit_amount' => 19900,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9hy5rjjpbas2zt5r8k2',
            'stripe_id' => 'price_8',
            'stripe_product_id' => '01k6d4h965aafj1pbmdfkb4yjs',
            'name' => 'Growth Plan Metered',
            'unit_amount' => 35,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9j22f5xdv94mxzhtxek',
            'stripe_id' => 'price_9',
            'stripe_product_id' => '01k6d4h965aafj1pbmdfkb4yjs',
            'name' => 'Grown Plan Base Rate',
            'unit_amount' => 7900,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9j5gkfwtdgbqn88ht0r',
            'stripe_id' => 'price_10',
            'stripe_product_id' => '01k6d4h99d6peejemn66sfhsyr',
            'name' => 'Team Plan Metered',
            'unit_amount' => 40,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9j9tybnrbwh8dbgxwgn',
            'stripe_id' => 'price_11',
            'stripe_product_id' => '01k6d4h99d6peejemn66sfhsyr',
            'name' => 'Team Plan Base Rate',
            'unit_amount' => 1900,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9jc42fdad5nbc1q6wws',
            'stripe_id' => 'price_12',
            'stripe_product_id' => '01k6d4h9czydkgvgcwvf5czrv8',
            'name' => 'Solo Plan Metered',
            'unit_amount' => 50,
            'active' => true,
        ]);

        StripePrice::create([
            'id' => '01k6d4h9jg687we29yw0n92z1m',
            'stripe_id' => 'price_13',
            'stripe_product_id' => '01k6d4h9czydkgvgcwvf5czrv8',
            'name' => 'Solo Plan Base Rate',
            'unit_amount' => 500,
            'active' => false,
        ]);

        StripePrice::create([
            'id' => '01k7b2gdrfarr1kwhvgjh5encs',
            'stripe_id' => 'price_14',
            'stripe_product_id' => '01k6d4h9czydkgvgcwvf5czrv8',
            'name' => 'Solo Plan Base Rate',
            'unit_amount' => 500,
            'active' => false,
        ]);



        StripeProduct::find('01k6d4h9czydkgvgcwvf5czrv8')
            ->update([
                'stripe_base_price_id' => '01k6d4h9hkqk814nzd71x06za1',
                'stripe_metered_price_id' => '01k6d4h9herym6sbc9yk1c9f6e'
            ]);

        StripeProduct::find('01k6d4h99d6peejemn66sfhsyr')
            ->update([
                'stripe_base_price_id' => '01k6d4h9j9tybnrbwh8dbgxwgn',
                'stripe_metered_price_id' => '01k6d4h9h9knqxpc5pz3qxxmek',
            ]);

        StripeProduct::find('01k6d4h9338rx6qv0k8xbradgf')
            ->update([
                'stripe_base_price_id' => '01k6d4h9hv55fh9ampyqwbkt6g',
                'stripe_metered_price_id' => '01k6d4h9hcggad2rkwcnevanrg',
            ]);

        StripeProduct::find('01k6d4h965aafj1pbmdfkb4yjs')
            ->update([
                'stripe_base_price_id' => '01k6d4h9j22f5xdv94mxzhtxek',
                'stripe_metered_price_id' => '01k6d4h9hgm5p95fa06dwp5h62',
            ]);
    }
}
