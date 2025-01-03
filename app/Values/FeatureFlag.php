<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlag as FeatureFlagModel;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\Factories\FeatureFlagFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;

/**
 * @method static static from(string $name, ?string $id = null, ?string $description = null, ?string $lastSeenAt = null, ?FeatureType $featureType = null)
 */
#[Collection(FeatureFlagCollection::class)]
#[Factory(FeatureFlagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class FeatureFlag extends Bag
{
    use HasFactory;

    public function __construct(
        public string $name,
        public ?string $id = null,
        public ?string $description = null,
        public ?string $lastSeenAt = null,
        public ?FeatureType $featureType = null,
    ) {
    }

    #[Transforms(FeatureFlagModel::class)]
    public static function fromModel(FeatureFlagModel $featureFlag): array
    {
        return [
            'name' => $featureFlag->name,
            'id' => $featureFlag->id,
            'description' => $featureFlag->description,
            'last_seen_at' => $featureFlag->last_seen_at,
            'feature_type' => FeatureType::from($featureFlag->featureType),
        ];
    }
}
