<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\StripeProduct;
use App\Values\Casts\MoneyFromMinor;
use App\Values\Collections\ProductCollection;
use App\Values\Factories\ProductFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Hidden;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Brick\Money\Money;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection as LaravelCollection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $id, string $name, string $description, bool $active, Collection $entitlements)
 * @method static ProductCollection<Product> collect(iterable $items)
 * @method static ProductFactory<Product> factory(Collection|array|int $data = [])
 */
#[Collection(ProductCollection::class)]
#[Factory(ProductFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Product extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('checkout')]
        public string $id,
        #[Hidden]
        public Optional|string $stripeId,
        public Optional|string $name,
        public Optional|string $description,
        public Optional|bool $active,
        public LaravelCollection|Optional $entitlements,
        public LaravelCollection|Optional $metadata,
        #[Cast(MoneyFromMinor::class, 'USD')]
        public ?Money $basePrice = null,
        #[Cast(MoneyFromMinor::class, 'USD')]
        public ?Money $meteredPrice = null,
    ) {
    }

    #[Transforms(StripeProduct::class)]
    public static function fromModel(StripeProduct $model): array
    {
        return [
            'id' => $model->id,
            'stripe_id' => $model->stripe_id,
            'name' => $model->name,
            'description' => $model->description,
            'active' => $model->active,
            'entitlements' => collect($model->entitlements)->map(function ($entitlement, $key) {
                if ($key === 'trial_length') {
                    return (string) new CarbonInterval($entitlement);
                }

                if ($key === 'evaluations') {
                    return (int) $entitlement;
                }

                return $entitlement;
            }),
            'metadata' => collect($model->metadata)->map(function ($value, $key) {
                if ($key === 'evaluation_tier_size') {
                    return (int) $value;
                }

                return $value;
            }),
            'base_price' => Money::ofMinor($model->basePrice->unit_amount, 'USD'),
            'metered_price' => Money::ofMinor($model->meteredPrice->unit_amount, 'USD')->multipliedBy($model->metadata['evaluation_tier_size'])->dividedBy(1000)
        ];
    }
}
